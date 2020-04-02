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
 * Plugin URI:        https://warpknot.com/
 * Description:       Keep a virtual queue in case you have more traffic than you should.
 * Version:           1.0.0
 * Author:            Alex Uta
 * Author URI:        https://warpknot.com/
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