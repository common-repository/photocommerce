<?php

class Photo_Commerce_Rest_Categories extends WP_REST_Controller
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
        $this->resource_name = 'categories';
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
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_permissions_check($request)
    {
        if (!current_user_can('read')) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot view WooCommerce categories.'), array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items($request)
    {
        //get default args
        $terms_per_page = $request['per_page'] ?: 25;
        $current_page =$request['paged'] ?: 1;

        //get terms
        $args = array();
        $args['paged'] = $current_page;
        $args['taxonomy'] = 'product_cat';
        $total_terms = wp_count_terms( 'product_cat', $args );
        if ($request['search']) {
            $args['name__like'] = $request['search'];
        }
        $args['orderby']  = $request['orderby'] ?: 'count';
        $args['order']  = $request['order'] ?: 'ASC';
        $args['number'] = $terms_per_page;
        $offset = 0; // initialize offset

        // if page is greater than 1, calculate offset
        if( ! 0 == $current_page) {
            $offset = ( $terms_per_page * $current_page ) - $terms_per_page;
        }
        $args['offset'] = $offset;

        $query_result = get_terms($args);


        $max_pages = ceil($total_terms / (int) $terms_per_page);
        $data = array();


        foreach ($query_result as $post) {
            $response = $this->prepare_item_for_response($post, $request);
            $data[] = $this->prepare_response_for_collection($response);
        }

//        $data['next'] =    next_posts_link( 'Older Entries', $posts->max_num_pages );
        // Return all of our comment response data.
        $response = rest_ensure_response(['items' => $data]);
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            if ($prev_page > $max_pages) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg(array(
                'orderby' => $args['orderby'],
                'order' => $args['order'],
                'per_page' => $args['posts_per_page'],
                'paged' => $prev_page
            ), rest_url('photo-commerce/v1/categories'));
            $response->add_link('prev', $prev_link);
        }
        if ($max_pages > $current_page) {
            $next_page = $current_page + 1;
            $next_link = add_query_arg(array(
                'orderby' => $args['orderby'],
                'order' => $args['order'],
                'per_page' => $args['posts_per_page'],
                'paged' => $next_page
            ), rest_url('photo-commerce/v1/categories'));
            $response->add_link('next', $next_link);
        }

        return $response;
    }


    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Term $term The comment object whose response is being prepared.
     */
    public function prepare_item_for_response($term, $request)
    {
        $category = get_term($term,'product_cat');
        if(!$category){
            return new WP_Error('404', esc_html__('Category not found'));
        }
        $data = $this->get_category_data($category);
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
            'title' => 'categorie',
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
     * @param WP_Term $category Category instance.
     * @return array
     */
    protected function get_category_data($category)
    {
        $data = array(
            'id' => $category->term_id,
            'name' => $category->name,
            'count' => $category->count,
        );
        return $data;
    }



}
