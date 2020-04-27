<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://warpknot.com/
 * @since             1.0.0
 * @package           Virtual_Queue
 *
 * @wordpress-plugin
 * Plugin Name:       Virtual Queue
 * Plugin URI:        https://github.com/alx-uta/Virtual-Queue
 * Description:       Keep a virtual queue in case you have more traffic than you should.
 * Version:           1.0.0
 * Author:            Alex Uta
 * Author URI:        https://warpknot.com/
 * Donate link:       https://www.paypal.me/wknt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       virtual-queue
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
define( 'VIRTUAL_QUEUE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-virtual-queue-activator.php
 */
function activate_virtual_queue() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-virtual-queue-activator.php';
	Virtual_Queue_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-virtual-queue-deactivator.php
 */
function deactivate_virtual_queue() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-virtual-queue-deactivator.php';
	Virtual_Queue_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_virtual_queue' );
register_deactivation_hook( __FILE__, 'deactivate_virtual_queue' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-virtual-queue.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_virtual_queue() {

	$plugin = new Virtual_Queue();
	$plugin->run();

}

run_virtual_queue();

/**
 * Shortcode
 */
function vq_shortcode() {
	global $wpdb;
	$table          = $wpdb->prefix . 'vq_sessions';
	$current_cookie = isset( $_COOKIE['vq_session_id'] ) ? $_COOKIE['vq_session_id'] : false;
	$undefined      = '#pending';

	if ( $current_cookie ):
		$position = $wpdb->get_row( "SELECT estimated_time FROM $table where session_id='$current_cookie'" );
		if ( $position ):
			return $position->estimated_time;
		else:
			return $undefined;
		endif;
	else:
		return $undefined;
	endif;
}

add_shortcode( 'virtual-queue-position', 'vq_shortcode' );

/**
 * Total
 *
 * @return string|null
 */
function vq_shortcode_total() {
	global $wpdb;
	$table    = $wpdb->prefix . 'vq_sessions';
	$sessions = $wpdb->get_row( "SELECT count(id) as counter FROM $table where status='0'" );

	return $sessions->counter;
}

add_shortcode( 'virtual-queue-total', 'vq_shortcode_total' );