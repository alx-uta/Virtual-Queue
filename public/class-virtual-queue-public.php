<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://warpknot.com/
 * @since      1.0.0
 *
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/public
 * @author     Alex <alex@warpknot.com>
 */
class Virtual_Queue_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	public function virtual_queue_query_vars( $vars ) {
		$vars[] = '__virtual_queue';

		return $vars;
	}

	/**
	 * Maintenance Endpoint
	 * Used for the cron job
	 */
	public function virtual_queue_endpoint() {
		add_rewrite_rule( '^virtual-queue/maintenance/?', 'index.php?__virtual_queue=maintenance', 'top' );
	}


	/**
	 * Virtual Queue Maintenance
	 */
	public function virtual_queue_parse_request() {
		global $wp;
		$__virtual_queue = $wp->query_vars['__virtual_queue'];
		if ( $__virtual_queue == 'maintenance' ):
			self::maintenance();
		endif;
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/virtual-queue-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Add Custom Meta
	 *
	 * @since    1.0.0
	 */
	public function add_meta() {
		$request_url         = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$vq_landing_page_url = get_option( 'vq_landing_page_url' );
		$vq_refresh_seconds  = get_option( 'vq_refresh_seconds' );

		if ( $request_url === $vq_landing_page_url ):
			if ( intval( $vq_refresh_seconds ) > 0 ):?>
                <meta http-equiv="Refresh" content="<?= intval( $vq_refresh_seconds ) ?>">
			<?php
			endif;
		endif;
	}

	/**
	 * Validate the current queue
	 */
	public function virtual_queue_validation() {
		global $wpdb, $wp;

		$request_url              = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$vq_sessions_limit_number = get_option( 'vq_sessions_limit_number' );
		$vq_landing_page_url      = get_option( 'vq_landing_page_url' );
		$vq_cookie_expire_hours   = get_option( 'vq_cookie_expire_hours' );

		if ( ! is_admin()
		     && ! current_user_can( 'administrator' )
		     && strpos( $request_url, 'wp-admin' ) == false
		     && strpos( $request_url, 'wp-login' ) == false
		     && strpos( $request_url, 'virtual-queue/maintenance' ) == false
		):

			$table_name        = $wpdb->prefix . 'vq_sessions';
			$session_create_id = uniqid() . '-' . session_create_id();

			$current_cookie   = isset( $_COOKIE['vq_session_id'] ) ? $_COOKIE['vq_session_id'] : $session_create_id;
			$vq_status        = self::vq_status( $wpdb, $wpdb->prefix . 'vq_status' );
			$pending_sessions = is_object( $vq_status ) ? ( isset( $vq_status->pending ) ? $vq_status->pending : 0 ) : 0;
			$active_sessions  = is_object( $vq_status ) ? ( isset( $vq_status->active ) ? $vq_status->active : 0 ) : 0;

			/**
			 * Calculate the status based on:
			 * - current sessions
			 * - sessions limit
			 * - pending list
			 */
			$status = ( ( $pending_sessions + $active_sessions ) >= $vq_sessions_limit_number ) ? 0 : ( $pending_sessions > 0 ? 0 : 1 );

			if ( isset( $_COOKIE['vq_session_id'] ) ):

				/**
				 * Update the latest updated time
				 */
				self::update_timestamp( $wpdb, $table_name, $current_cookie );

				/**
				 * Validate this cookie
				 */
				$validate_cookie = self::validate_cookie( $wpdb, $table_name, $current_cookie );

				if ( empty( $validate_cookie ) ):
					/**
					 * Add a new cookie and send this one to the end of the queue
					 */
					setcookie( 'vq_session_id', false, time() + ( - 3600 * $vq_cookie_expire_hours ), '/' );
					wp_redirect( $vq_landing_page_url );
				else:
					/**
					 * Continue
					 */
					if ( $status ):
						/**
						 * Allow this user to access the website
						 */
						self::set_active_status( $wpdb, $table_name, $current_cookie );
					else:
						if ( $validate_cookie->status ):
							$status = 1;
						endif;
					endif;
				endif;
			else:
				/**
				 * Insert this row into the database
				 */
				$add_to_queue = self::add_to_queue( $wpdb, $table_name, $session_create_id, $status );

				/**
				 * If data saved, set the cookie
				 */
				if ( $add_to_queue ):
					setcookie( 'vq_session_id', $session_create_id, time() + ( 3600 * $vq_cookie_expire_hours ), '/' );
					/**
					 * Set the current position
					 */
					self::set_queue_position();
				endif;
			endif;

			if ( ! $status ):
				/**
				 * See if someone left the queue
				 */
				/**
				 * Redirect to the landing page
				 */
				if ( $request_url !== $vq_landing_page_url ):
					wp_redirect( $vq_landing_page_url );
					exit();
				endif;
			else:
				if ( $request_url === $vq_landing_page_url ):
					wp_redirect( "/" );
					exit();
				endif;
			endif;
		endif;
	}

	/**
	 * @param $wpdb
	 * @param $table_name
	 * @param $session_create_id
	 * @param $status
	 *
	 * @return mixed
	 */
	private static function add_to_queue( $wpdb, $table_name, $session_create_id, $status = 0 ) {
		$wpdb->insert(
			$table_name,
			array(
				'session_id'      => $session_create_id,
				'estimated_time'  => 0,
				'registered_date' => time(),
				'updated_date'    => time(),
				'status'          => $status ? 1 : 0
			),
			array(
				'%s',
				'%d',
				'%d',
				'%d',
				'%d'
			)
		);
		/**
		 * Keep the last ID
		 */
		$lastId = $wpdb->insert_id;

		/**
		 * Update the status
		 */

		self::update_statistics();

		return $lastId;
	}

	/**
	 * @param $wpdb
	 * @param $table_name
	 *
	 * @return int
	 */
	private static function vq_status( $wpdb, $table_name ) {
		$vq_status = $wpdb->get_row( "SELECT `active`, `pending` FROM $table_name where id='1'" );

		return $vq_status;
	}

	/**
	 * @param $wpdb
	 * @param $session_id
	 *
	 * @return mixed
	 */
	private static function validate_cookie( $wpdb, $table_name, $session_id ) {
		/**
		 * Return the current status
		 */
		return $wpdb->get_row( "SELECT status FROM $table_name where session_id='$session_id'" );
	}

	/**
	 * @param $wpdb
	 * @param $table_name
	 * @param $session_id
	 */
	private static function set_active_status( $wpdb, $table_name, $session_id ) {
		$wpdb->update( $table_name, array( 'status' => 1 ), array( 'session_id' => $session_id ) );

		self::update_statistics();
	}

	/**
	 * @param $wpdb
	 * @param $table_name
	 * @param $session_id
	 */
	private static function update_timestamp( $wpdb, $table_name, $session_id ) {
		$wpdb->update( $table_name, array( 'updated_date' => time() ), array( 'session_id' => $session_id ) );
	}


	/**
	 * Maintenance
	 */
	private static function maintenance() {
		global $wpdb;

		/**
		 * Remove all the inactive visitors from the queue
		 */
		$vq_sessions_limit_number = get_option( 'vq_sessions_limit_number' );
		$vq_inactive_minutes      = get_option( 'vq_inactive_minutes' );
		$time                     = time() - ( $vq_inactive_minutes * 60 );
		$table                    = $wpdb->prefix . 'vq_sessions';

		/**
		 * Delete Pending & Active Sessions
		 */
		$wpdb->query( "DELETE FROM $table WHERE updated_date < $time" );

		/**
		 * Let's allow someone else to navigate
		 */
		$allow_counter = $vq_sessions_limit_number - self::activeCounter();
		$wpdb->query( "update $table set status=1 ORDER BY id ASC LIMIT $allow_counter" );

		/**
		 * Update the statistics
		 */
		self::update_statistics();

		/**
		 * Update the queue position
		 */
		self::set_queue_position();
		exit( 0 );
	}

	/**
	 * Update the queue position
	 */
	private static function set_queue_position() {
		global $wpdb;
		$table = $wpdb->prefix . 'vq_sessions';

		/**
		 * Set the current position
		 */
		$wpdb->query( "SET @virtual_queue_position := 0;" );
		$wpdb->query( "UPDATE $table SET estimated_time = ( SELECT @virtual_queue_position := @virtual_queue_position + 1 ) where status=0 ORDER BY id ASC;" );

	}

	/**
	 * Update Statistics
	 */
	private static function update_statistics() {
		global $wpdb;

		$wpdb->update( $wpdb->prefix . 'vq_status',
			array(
				'pending' => self::pendingCounter(),
				'active'  => self::activeCounter()
			),
			array( 'id' => 1 )
		);
	}

	/**
	 * Active Counter
	 *
	 * @return mixed
	 */
	private static function activeCounter() {
		global $wpdb;
		$table = $wpdb->prefix . 'vq_sessions';

		/**
		 * Count the active sessions
		 */
		$sessions = $wpdb->get_row( "SELECT count(id) as counter FROM $table where status='1'" );

		return $sessions->counter;
	}

	/**
	 * Pending Counter
	 *
	 * @return mixed
	 */
	private static function pendingCounter() {
		global $wpdb;
		$table = $wpdb->prefix . 'vq_sessions';

		/**
		 * Count the pending sessions
		 * $pending_sessions->counter
		 */
		$sessions = $wpdb->get_row( "SELECT count(id) as counter FROM $table where status='0'" );

		return $sessions->counter;
	}
}
