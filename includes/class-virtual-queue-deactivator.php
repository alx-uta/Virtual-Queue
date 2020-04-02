<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://warpknot.com/
 * @since      1.0.0
 *
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/includes
 * @author     Alex <alex@warpknot.com>
 */
class Virtual_Queue_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'vq_sessions' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'vq_status' );
		delete_option( "vq_sessions_limit_number" );
		delete_option( "vq_landing_page_url" );
		delete_option( "vq_sessions_limit_number" );
		delete_option( "vq_cookie_expire_hours" );
		delete_option( "vq_refresh_seconds" );
	}

}
