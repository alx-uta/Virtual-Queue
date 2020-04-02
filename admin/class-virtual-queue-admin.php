<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://warpknot.com/
 * @since      1.0.0
 *
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Virtual_Queue
 * @subpackage Virtual_Queue/admin
 * @author     Alex <alex@warpknot.com>
 */
class Virtual_Queue_Admin {

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
	 *
	 * @since    1.0.0
	 */

	static $strings
		= array(
			'name'    => 'Virtual Queue',
			'options' => 'Options'
		);

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}


	/**
	 * Add the menu
	 */
	function add_new_menu_items() {
		add_menu_page(
			self::$strings['name'] . " - " . self::$strings['options'],
			self::$strings['name'],
			"manage_options",
			"virtual-queue-options",
			"Virtual_Queue_Admin::theme_options_page",
			"",
			100
		);
	}


	function theme_options_page() {
		?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h1>Virtual Queue - Options</h1>

			<?php
			$active_tab = isset( $_GET["tab"] ) ? ( $_GET["tab"] == 'statistics' ? 'statistics' : 'general-settings' ) : 'general-settings';
			?>

            <!-- wordpress provides the styling for tabs. -->
            <h2 class="nav-tab-wrapper">
                <!-- when tab buttons are clicked we jump back to the same page but with a new parameter that represents the clicked tab. accordingly we make it active -->
                <a href="?page=virtual-queue-options&tab=general-settings"
                   class="nav-tab <?php if ( $active_tab == 'general-settings' ) {
					   echo 'nav-tab-active';
				   } ?> "><?php _e( 'Header Options', 'virtual-queue' ); ?></a>
                <a href="?page=virtual-queue-options&tab=statistics"
                   class="nav-tab <?php if ( $active_tab == 'statistics' ) {
					   echo 'nav-tab-active';
				   } ?>"><?php _e( 'Statistics', 'virtual-queue' ); ?></a>
            </h2>

            <form method="post" action="options.php">
				<?php

				settings_fields( "header_section" );

				do_settings_sections( "virtual-queue-options" );
				if ( $active_tab !== 'statistics' ):
					submit_button();
				endif;
				?>
            </form>
        </div>
		<?php
	}


	function display_options() {
		add_settings_section( "header_section", "", "Virtual_Queue_Admin::display_header_options_content", "virtual-queue-options" );
		$tab = isset( $_GET["tab"] ) ? $_GET["tab"] : '';

		//here we display the sections and options in the settings page based on the active tab

		if ( $tab == "statistics" ):?>

			<?php
			/**
			 * Sessions Limit
			 */
			add_settings_field( "vq_sessions_limit_number", "Sessions", "Virtual_Queue_Admin::active_sessions", "virtual-queue-options", "header_section" );
			register_setting( "header_section", "vq_active_sessions" );

		else:
			/**
			 * Sessions Limit
			 */
			add_settings_field( "vq_sessions_limit_number", "Visitors allowed to navigate the website at a time", "Virtual_Queue_Admin::display_queue_limit_form_element", "virtual-queue-options", "header_section" );
			register_setting( "header_section", "vq_sessions_limit_number" );

			/**
			 * Cookie Expire Hours
			 */
			add_settings_field( "vq_cookie_expire_hours", "Queue cookie expire after", "Virtual_Queue_Admin::display_cookie_expire_hours_form_element", "virtual-queue-options", "header_section" );
			register_setting( "header_section", "vq_cookie_expire_hours" );

			/**
			 * Automatically refresh page every X seconds
			 */
			add_settings_field( "vq_refresh_seconds", "Automatically reload the landing page after", "Virtual_Queue_Admin::display_refresh_seconds_form_element", "virtual-queue-options", "header_section" );
			register_setting( "header_section", "vq_refresh_seconds" );

			/**
			 * Delete inactive users after
			 */
			add_settings_field( "vq_inactive_minutes", "Remove inactive users from queue after", "Virtual_Queue_Admin::display_inactive_minutes_form_element", "virtual-queue-options", "header_section" );
			register_setting( "header_section", "vq_inactive_minutes" );

			/**
			 * Landing Page
			 */
			add_settings_field( "vq_landing_page_url", "Landing Page Url", "Virtual_Queue_Admin::display_landing_page_url_form_element", "virtual-queue-options", "header_section" );
			register_setting( "header_section", "vq_landing_page_url" );

			?>

		<?php
		endif;


	}

	function display_header_options_content() {
	}

	function display_queue_limit_form_element() {
		?>
        <input type="text" name="vq_sessions_limit_number" id="vq_sessions_limit_number" class="regular-text code"
               value="<?php echo get_option( 'vq_sessions_limit_number' ); ?>"/>
		<?php
	}

	function display_cookie_expire_hours_form_element() {
		?>
        <input type="text" name="vq_cookie_expire_hours" id="vq_cookie_expire_hours" class="regular-text code"
               value="<?php echo get_option( 'vq_cookie_expire_hours' ); ?>"/> hours
		<?php
	}

	function display_refresh_seconds_form_element() {
		?>
        <input type="text" name="vq_refresh_seconds" id="vq_refresh_seconds" class="regular-text code"
               value="<?php echo get_option( 'vq_refresh_seconds' ); ?>"/> seconds
		<?php
	}

	function display_inactive_minutes_form_element() {
		?>
        <input type="text" name="vq_inactive_minutes" id="vq_inactive_minutes" class="regular-text code"
               value="<?php echo get_option( 'vq_inactive_minutes' ); ?>"/> minutes
		<?php
	}

	function display_landing_page_url_form_element() {
		?>
        <input type="text" name="vq_landing_page_url" id="vq_landing_page_url" class="regular-text code"
               value="<?php echo get_option( 'vq_landing_page_url' ); ?>"/>
		<?php
	}

	function active_sessions() {
		global $wpdb;
		$vq_status = $wpdb->get_row( "SELECT `active`, `pending` FROM " . $wpdb->prefix . "vq_status where id='1'" );
		if ( ! empty( $vq_status ) ):
			?>
            <p>Active: <?= $vq_status->active ?></p>
            <p>Pending: <?= $vq_status->pending ?></p>
		<?php
		endif;

	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/virtual-queue-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/virtual-queue-admin.js', array( 'jquery' ), $this->version, false );

	}

}
