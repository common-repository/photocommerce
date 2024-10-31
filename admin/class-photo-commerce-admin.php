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
class Photo_Commerce_Admin
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
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Photo_Commerce_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Photo_Commerce_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if (isset($_GET['page']) && $_GET['page'] === 'photo_commerce') {
            wp_enqueue_style($this->plugin_name . 'admin', plugin_dir_url(__FILE__) . 'css/photo-commerce-admin.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . 'bootstrap_button', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Photo_Commerce_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Photo_Commerce_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if (isset($_GET['page']) && $_GET['page'] === 'photo_commerce') {
            wp_enqueue_script($this->plugin_name . 'qrcode', plugin_dir_url(__FILE__) . 'js/qrcode.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name . 'bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, true);
            wp_enqueue_script($this->plugin_name . 'admin', plugin_dir_url(__FILE__) . 'js/photo-commerce-admin.js', array('jquery'), $this->version, false);
        }
    }

    function plugin_setup_menu()
    {
        add_menu_page(
            __('Photo Commerce', 'photo-commerce'),
            __('Photo Commerce', 'photo-commerce'),
            'manage_options',
            'photo_commerce',
            array($this, 'displayPluginAdminSettings'),
            'data:image/svg+xml;base64,' .
            base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path fill="currentColor" d="M56 0h80c13.3 0 24 10.7 24 24s-10.7 24-24 24H56c-4.4 0-8 3.6-8 8v80c0 13.3-10.7 24-24 24s-24-10.7-24-24V56C0 25.1 25.1 0 56 0zM376 0h80c30.9 0 56 25.1 56 56v80c0 13.3-10.7 24-24 24s-24-10.7-24-24V56c0-4.4-3.6-8-8-8H376c-13.3 0-24-10.7-24-24s10.7-24 24-24zM48 376v80c0 4.4 3.6 8 8 8h80c13.3 0 24 10.7 24 24s-10.7 24-24 24H56c-30.9 0-56-25.1-56-56V376c0-13.3 10.7-24 24-24s24 10.7 24 24zm464 0v80c0 30.9-25.1 56-56 56H376c-13.3 0-24-10.7-24-24s10.7-24 24-24h80c4.4 0 8-3.6 8-8V376c0-13.3 10.7-24 24-24s24 10.7 24 24zM180 128l6.2-16.4c3.5-9.4 12.5-15.6 22.5-15.6h94.7c10 0 19 6.2 22.5 15.6L332 128h36c26.5 0 48 21.5 48 48V336c0 26.5-21.5 48-48 48H144c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48h36zM320 256c0-35.3-28.7-64-64-64s-64 28.7-64 64s28.7 64 64 64s64-28.7 64-64z"/></svg>'
            )
        );
    }

    public function displayPluginAdminSettings()
    {
        require_once 'partials/' . $this->plugin_name . '-admin-display.php';
    }

    public function activation_redirect()
    {
        if (get_option('redirect_after_activation_option', false)) {
            delete_option('redirect_after_activation_option');
            exit(wp_redirect(admin_url('admin.php?page=photo_commerce')));
        }
    }
}
