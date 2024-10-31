<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://vitrion.nl
 * @since      1.0.0
 *
 * @package    Photo_Commerce
 * @subpackage Photo_Commerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Photo_Commerce
 * @subpackage Photo_Commerce/admin
 * @author     Vitrion B.V. <support@vitrion.nl>
 */
class Photo_Commerce_Rest {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->load_dependencies();
	}


    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'rest/class-photo-commerce-rest-products.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'rest/class-photo-commerce-rest-categories.php';

    }

    function register_rest() {
        $controller = new Photo_Commerce_Rest_Products($this->plugin_name);
        $controller->register_routes();
        $controller = new Photo_Commerce_Rest_Categories($this->plugin_name);
        $controller->register_routes();
    }

}
