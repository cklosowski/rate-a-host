<?php
/*
Plugin Name: Rate a Host
Plugin URI: https://filament-studios.com
Description: Allows the rating of a Co-Op Host
Author: Chris Klosowski
Version: 1.2
Author URI: http://kungfugrep.com
License: GPL V2
*/

define( 'RAH_PATH', plugin_dir_path( __FILE__ ) );
define( 'RAH_VERSION', '1.2' );
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
		include_once( $includes_path . 'open-graph-meta.php' );
		
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			include_once( $includes_path . 'wp-cli-integrations.php' );
		}
	}

	private function hooks() {
		add_action( 'init', array( $this, 'rewrites' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ), 10, 1 );

		// Social Constants
		add_action( 'init', 'rah_set_social_tokens', 10 );

		// Access restriction
		//add_action( 'login_head', 'rah_no_login_php', 1 );

		// Functionality
		add_action( 'init', 'rah_setup_post_types', 1 );
		add_action( 'wp_ajax_rah_group_listing', 'rah_get_groups_ajax' );
		add_action( 'wp_ajax_rah_secret_group_listing', 'rah_get_secret_groups_ajax' );
		add_action( 'wp_ajax_rah_verify_zip', 'rah_verify_zip' );
		add_action( 'wp_ajax_nopriv_rah_search_hosts_distance', 'rah_search_hosts_distance' );
		add_action( 'wp_ajax_rah_search_hosts_distance', 'rah_search_hosts_distance' );
		add_action( 'delete_post', 'rah_delete_host' );

		if( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['rah-action'] ) && ! empty( $_POST['rah-action'] ) ) {
			switch( $_POST['rah-action'] ) {
				case 'register-host':
					add_action( 'init', 'rah_insert_host' );
					break;

				case 'edit-host':
					add_action( 'init', 'rah_edit_host' );
					break;
			}
		}

		add_action( 'widgets_init', 'rah_host_widget' );
		add_action( 'transition_post_status', 'rah_recalculate_host_ratings', 10, 3 );
		add_action( 'transition_post_status', 'rah_send_host_email', 10, 3 );
		add_action( 'transition_post_status', 'rah_send_user_review_email', 10, 3 );
		add_action( 'admin_print_styles', 'rah_custom_right_now_icons' );
		add_action( 'admin_menu', array( $this, 'rah_setup_admin_menu' ) );
	}

	public function load_scripts() {

		wp_enqueue_style( 'dashicons' );		

		wp_register_style( 'rah-css', RAH_URL . 'assets/style.css', NULL, RAH_VERSION, 'all' );
		wp_enqueue_style( 'rah-css' );

		wp_register_script( 'rah-ajax', RAH_URL . 'assets/rah.js', array( 'jquery' ), RAH_VERSION, true );
		wp_enqueue_script( 'rah-ajax' );

		wp_register_script( 'rah-ratings', RAH_URL . 'assets/star-rating.js', array( 'jquery' ), RAH_VERSION );
		wp_enqueue_script( 'rah-ratings' );

	}

	public function load_admin_scripts( $hook ) {
		global $post;
		if ( 'edit.php' !== $hook && 'post.php' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'rah_admin_js', RAH_URL . 'assets/admin.js', array( 'jquery' ), RAH_VERSION, true );
	}

	public function rewrites() {
		add_rewrite_endpoint( 'new', EP_ALL );
		add_rewrite_endpoint( 'save', EP_ALL );
		add_rewrite_endpoint( 'edit', EP_ALL );
	}

	public function rah_setup_admin_menu() {
		add_options_page( __( 'Rate A Host', 'rah-txt' ), __( 'Rate A Host', 'rah-txt' ), 'administrator', 'rate-a-host', array( $this, 'show_settings_page' ) );
	}

	public function show_settings_page() {
		?>
		<div class="wrap">
			<?php
				if ( isset( $_GET['recount_host_ratings'] ) && $_GET['recount_host_ratings'] == 'true' ) {
					$host_args = array(
					'orderby'          => 'post_title',
					'order'            => 'DESC',
					'include'          => '',
					'exclude'          => '',
					'meta_key'         => '',
					'meta_value'       => '',
					'post_type'        => 'hosts',
					'post_mime_type'   => '',
					'post_status'      => 'publish',
					'posts_per_page'   => -1,
					'suppress_filters' => true );
					$hosts = get_posts( $host_args );

					foreach ( $hosts as $host ) {
						rah_run_recalculation( $host->ID );
					}
					?><div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Host Reviews Refreshed</strong></p></div><?php
				}
			?>
			<h2>Host Reviews Board - Settings & Utilities</h2>
			<a class="button-secondary" href="<?php echo admin_url('options-general.php?page=rate-a-host'); ?>&recount_host_ratings=true">Re-calculate Host Ratings</a>
		</div>
		<?php
	}
}

RateAHost::getInstance();
