<?php
/*
Plugin Name: Rate a Host
Plugin URI: https://filament-studios.com
Description: Allows the rating of a Co-Op Host
Author: Chris Klosowski
Version: 1.0
Author URI: http://kungfugrep.com
License: GPL V2
*/

define( 'RAH_PATH', plugin_dir_path( __FILE__ ) );
define( 'RAH_VERSION', '1.0' );
define( 'RAH_FILE', plugin_basename( __FILE__ ) );
define( 'RAH_URL', plugins_url( '/', RAH_FILE ) );

class RateAHost {
	private static $rah_instance;

	private function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Get the singleton instance of our plugin
	 * @return class The Instance
	 * @access public
	 */
	public static function getInstance() {
		if ( !self::$rah_instance ) {
			self::$rah_instance = new RateAHost();
		}

		return self::$rah_instance;
	}

	private function includes() {
		$includes_path = RAH_PATH . '/includes/';

		include_once( $includes_path . 'general-functions.php' );
		include_once( $includes_path . 'post-types.php' );
		include_once( $includes_path . 'shortcodes.php' );
		include_once( $includes_path . 'widgets.php' );
	}

	private function hooks() {
		add_action( 'init', array( $this, 'rewrites' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

		// Social Constants
		add_action( 'init', 'rah_set_social_tokens', 10 );

		// Access restriction
		add_action( 'login_head', 'rah_no_login_php', 1 );

		// Functionality
		add_action( 'init', 'rah_setup_post_types', 1 );
		add_action( 'wp_ajax_rah_group_listing', 'rah_get_groups_ajax' );
		add_action( 'delete_post', 'rah_delete_host' );
		if( 'POST' == $_SERVER['REQUEST_METHOD']
			&& ( ! empty( $_POST['action'] ) && $_POST['action'] === 'register-host' ) ) {
			add_action( 'init', 'rah_insert_host' );
		}
		add_action( 'widgets_init', 'rah_host_widget' );
		add_action( 'transition_post_status', 'rah_recalculate_host_ratings', 10, 3 );
	}

	public function load_scripts() {
		wp_enqueue_style( 'rah-css', RAH_URL . 'assets/style.css', NULL, RAH_VERSION, 'all' );
		wp_enqueue_script( 'rah-ajax', RAH_URL . 'assets/rah.js', array('jquery'), RAH_VERSION, true );
		wp_enqueue_script( 'rah-ratings', RAH_URL . 'assets/star-rating.js', array('jquery'), RAH_VERSION );
	}

	public function rewrites() {
		add_rewrite_endpoint( 'new', EP_PERMALINK );
		add_rewrite_endpoint( 'submit', EP_PERMALINK );
		add_rewrite_endpoint( 'edit', EP_PERMALINK );
	}
}

RateAHost::getInstance();
