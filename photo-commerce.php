<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://vitrion.nl
 * @since             1.0.0
 * @package           Photo_Commerce
 *
 * @wordpress-plugin
 * Plugin Name:       PhotoCommerce
 * Plugin URI:        https://photo-commerce.com
 * Description:       This is a helper plugin for the mobile app "PhotoCommerce" this plugin allows you to load products in the mobile app and edit images
 * Version:           1.0.6
 * Author:            Vitrion B.V.
 * Author URI:        https://vitrion.nl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       photo-commerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PHOTO_COMMERCE_VERSION', '1.0.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-photo-commerce-activator.php
 */
function activate_photo_commerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-photo-commerce-activator.php';
	Photo_Commerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-photo-commerce-deactivator.php
 */
function deactivate_photo_commerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-photo-commerce-deactivator.php';
	Photo_Commerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_photo_commerce' );
register_deactivation_hook( __FILE__, 'deactivate_photo_commerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-photo-commerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_photo_commerce() {

	$plugin = new Photo_Commerce();
	$plugin->run();

}
run_photo_commerce();
