<?php

/**
 * Fired during plugin activation
 *
 * @link       https://vitrion.nl
 * @since      1.0.0
 *
 * @package    Photo_Commerce
 * @subpackage Photo_Commerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Photo_Commerce
 * @subpackage Photo_Commerce/includes
 * @author     Vitrion B.V. <support@vitrion.nl>
 */
class Photo_Commerce_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        if ( ! current_user_can( 'activate_plugins' ) ) {
            // Deactivate the plugin.
            deactivate_plugins( plugin_basename( __FILE__ ) );

            $error_message = __( 'You do not have proper authorization to activate a plugin!', 'photo-commerce' );
            die( esc_html( $error_message ) );
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            // Deactivate the plugin.
            deactivate_plugins( plugin_basename( __FILE__ ) );
            // Throw an error in the WordPress admin console.
            $error_message = __( 'This plugin requires WooCommerce plugin to be active!', 'photo-commerce' );
            die( wp_kses_post( $error_message ) );
        }
        add_option('redirect_after_activation_option', true);


    }


}
