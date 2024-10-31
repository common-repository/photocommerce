<?php

class Photo_Commerce_Rest_Products extends WP_REST_Controller
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;


    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name)
    {
        $this->plugin_name = $plugin_name;
        $this->namespace = sprintf('%s/v1', $plugin_name);
        $this->resource_name = 'products';
    }

    // Register our routes.
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->resource_name, array(
            // Here we register the readable endpoint for collections.
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_items'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
            ),
            // Register our schema callback.
            'schema' => array($this, 'get_item_schema'),
        ));
        register_rest_route($this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the resource.', 'woocommerce'),
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => array($this, 'get_item_permissions_check'),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'update_item_permissions_check'),
            ),
            // Register our schema callback.
            'schema' => array($this, 'get_item_schema'),
        ));
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_permissions_check($request)
    {
        if (!current_user_can('read')) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot view WooCommerce products.'), array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Grabs the products based on some custom filters
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items($request)
    {
        $args = array();
        $args['posts_per_page'] = $request['per_page'] ?: 25;
        $args['paged'] = $request['paged'] ?: 1;
        if ($request['parent']) {
            $args['post_parent'] = (int)$request['parent'];
        }

        $args['post_type'] = $request['variations'] ? 'product_variation' : 'product';
        $args['post_status'] =array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private');
        $args['orderby'] = $request['orderby'] ?: 'count';
        $args['order'] = $request['order'] ?: 'ASC';
        if ($request['search']) {
            $args['s'] = $request['search'];
        }
        if($request['category']){
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $request['category'],
                    'operator' => 'IN'
                )
            );
        }
        if ($request['sku']) {
            $args['meta_query'] = array(
                array(
                    'key' => '_sku',
                    'value' => $request['sku'],
                    'compare' => 'LIKE'
                ),
            );
        }
        if ($request['ean']) {
            if ($request['variations'] !== 'product_variation') {
                $args['post_type'] = array('product', 'product_variation');
            }
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => '_alg_ean',
                    'value' => $request['ean'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'hwp_product_gtin',
                    'value' => $request['ean'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_wpm_gtin_code',
                    'value' => $request['ean'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 's2_woocommerce_ean_field',
                    'value' => $request['ean'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_wpm_gtin_code_label',
                    'value' => $request['ean'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_wpmr_ean',
                    'value' => $request['ean'],
                    'compare' => 'LIKE'
                ),
            );
        }
        $posts_query = new WP_Query();
        $query_result = $posts_query->query($args);
        $page = (int)$args['paged'];
        $total_posts = $posts_query->found_posts;

        $max_pages = ceil($total_posts / (int)$args['posts_per_page']);
        $data = array();


        foreach ($query_result as $post) {
            $response = $this->prepare_item_for_response($post, $request);
            $data[] = $this->prepare_response_for_collection($response);
        }

        $response = rest_ensure_response(['items' => $data]);
        if ($page > 1) {
            $prev_page = $page - 1;
            if ($prev_page > $max_pages) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg(array(
                'orderby' => $args['orderby'],
                'order' => $args['order'],
                'per_page' => $args['posts_per_page'],
                'paged' => $prev_page
            ), rest_url('photo-commerce/v1/products'));
            $response->add_link('prev', $prev_link);

        }
        if ($max_pages > $page) {
            $next_page = $page + 1;
            $next_link = add_query_arg(array(
                'orderby' => $args['orderby'],
                'order' => $args['order'],
                'per_page' => $args['posts_per_page'],
                'paged' => $next_page
            ), rest_url('photo-commerce/v1/products'));
            $response->add_link('next', $next_link);
        }

        return $response;
    }

    function get_previous_posts_page_link()
    {
        global $paged;

        if (!is_single()) {
            $nextpage = intval($paged) - 1;
            if ($nextpage < 1)
                $nextpage = 1;
            return get_pagenum_link($nextpage);
        }
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_permissions_check($request)
    {
        if (!current_user_can('read')) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot view the product resource.'), array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function update_item_permissions_check($request)
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot edit the product resource.'), array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item($request)
    {

        $id = (int)$request['id'];
        $post = get_post($id);

        if (empty($post)) {
            return rest_ensure_response(array());
        }

        $response = $this->prepare_item_for_response($post, $request);

        // Return all of our post response data.
        return $response;
    }

    /**
     * Update product
     *
     * @param WP_REST_Request $request Current request.
     */
    public function update_item($request)
    {
        $post_id = (int)$request['id'];
        if (empty($post_id) || !in_array(get_post_type($post_id), array('product', 'product_variation'))) {
            return new WP_Error("404", __('Product not found', 'woocommerce'));
        };

        $product = wc_get_product($post_id);
        if ($request['name']) {
            $product->set_name(wp_filter_post_kses($request['name']));
        }
        if ($request['sale_price']) {
            $product->set_sale_price($request['sale_price']);
        }
        if ($request['price']) {
            $product->set_price($request['price']);
        }
        if ($request['regular_price']) {
            $product->set_regular_price($request['regular_price']);
        }
        if ($request['featured_image']) {
            $product->set_image_id($request['featured_image']);
        }
        if ($request['gallery']) {
            $product->set_gallery_image_ids($request['gallery']);
        }
        $product->save();
        $response = $this->prepare_item_for_response($post_id, $request);
        return rest_ensure_response($response);
    }

    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Post $post The comment object whose response is being prepared.
     */
    public function prepare_item_for_response($post, $request)
    {
        $product = wc_get_product($post);
        if (!$product) {
            return new WP_Error('404', esc_html__('Product not found'));
        }
        $data = $this->get_product_data($product);
        $context = !empty($request['context']) ? $request['context'] : 'view';
        $data = $this->filter_response_by_context($data, $context);

        return rest_ensure_response($data);
    }


    /**
     * Get our sample schema for a post.
     *
     * @return array The sample schema for a post
     */
    public function get_item_schema()
    {
        if ($this->schema) {
            // Since WordPress 5.3, the schema can be cached in the $schema property.
            return $this->schema;
        }

        $this->schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            // The name property marks the identity of the resource.
            'title' => 'product',
            'type' => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties' => array(
                'id' => array(
                    'description' => esc_html__('Unique identifier for the object.', $this->plugin_name),
                    'type' => 'integer',
                    'context' => array('view', 'edit'),
                    'readonly' => true,
                ),
                'name' => array(
                    'description' => esc_html__('The name for the object.', $this->plugin_name),
                    'type' => 'string',
                ),
                'price' => array(
                    'description' => esc_html__('The price for the object.', $this->plugin_name),
                    'type' => 'string',
                ),
            ),
        );

        return $this->schema;
    }

    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code()
    {

        $status = 401;

        if (is_user_logged_in()) {
            $status = 403;
        }

        return $status;
    }

    /**
     * Get product data.
     *
     * @param WC_Product $product Product instance.
     * @return array
     */
    protected function get_product_data($product)
    {
        $data = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'price' => $product->get_price(),
            'type' => $product->get_type(),
            'parent' => $product->get_parent_id(),
            'featured_image' => $this->get_featured_image($product),
            'gallery' => $this->get_gallery($product),
        );
        return $data;
    }

    /**
     * Get the images for a product or product variation.
     *
     * @param WC_Product|WC_Product_Variation $product Product instance.
     * @return array
     */
    protected function get_featured_image($product)
    {
        $featured_image_id = $product->get_image_id();
        $attachment_post = get_post($featured_image_id);
        $dummy = array(
            'id' => 0,
            'src' => wc_placeholder_img_src(),
            'name' => __('Placeholder', $this->plugin_name),
        );

        if (is_null($attachment_post)) {
            return $dummy;
        }

        $attachment = wp_get_attachment_image_src($featured_image_id, 'full');
        if (!is_array($attachment)) {
            return $dummy;
        }

        return array(
            'id' => (int)$featured_image_id,
            'src' => current($attachment),
            'name' => get_the_title($featured_image_id),
        );

    }

    protected function get_gallery($product)
    {
        $images = array();
        $attachment_ids = $product->get_gallery_image_ids();
        // Build image data.
        foreach ($attachment_ids as $position => $attachment_id) {
            $attachment_post = get_post($attachment_id);
            if (is_null($attachment_post)) {
                continue;
            }

            $attachment = wp_get_attachment_image_src($attachment_id, 'full');
            if (!is_array($attachment)) {
                continue;
            }

            $images[] = array(
                'id' => (int)$attachment_id,
                'src' => current($attachment),
                'name' => get_the_title($attachment_id),
            );
        }

        return $images;
    }


}
