<?php

/**
 * Fired during plugin activation
 *
 * @link       https://warpknot.com/
 * @since      1.0.0
 *
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/includes
 * @author     Alex <alex@warpknot.com>
 */
class Virtual_Queue_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$vq_sessions     = $wpdb->prefix . 'vq_sessions';
		$wp_vq_status    = $wpdb->prefix . 'vq_status';

		$sql = "CREATE TABLE $vq_sessions (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `session_id` varchar(256) NOT NULL,
			  `estimated_time` int(11) DEFAULT NULL,
			  `registered_date` int(11) NOT NULL,
			  `updated_date` int(11) NOT NULL,
			  `status` TINYINT(2) NOT NULL DEFAULT '0',
				PRIMARY KEY id (id)
			) $charset_collate;
			
			CREATE TABLE $wp_vq_status (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `active` bigint(11) NOT NULL,
			  `pending` bigint(11) NOT NULL,
				PRIMARY KEY id (id)
			) $charset_collate;
			
			INSERT INTO $wp_vq_status (`id`, `active`, `pending`) VALUES (1, 0, 0);
			";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
