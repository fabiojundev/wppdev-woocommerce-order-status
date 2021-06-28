<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers;
use WPPluginsDev\WooOrderWorkflow\Controllers\Admin\Controller_Admin_Status;
use WPPluginsDev\WooOrderWorkflow\Controllers\Admin\Controller_Admin_Order;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Status;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Email;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Event;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Cron;

use WPPluginsDev\WooOrderWorkflow\Controllers\Controller;
use WPPluginsDev\Helpers\Helper_Debug;

/**
 * Plugin Controller.
 * Manage other controllers and menus.
 */
class Controller_Plugin extends Controller {

	/**
	 * Admin menu constants.
	 */
	// const MENU_SLUG = 'edit.php?post_type=wo_sale';
	const MENU_SLUG = 'woocommerce';
	const MENU_PREFIX = 'woocommerce_page_';
	const MENU_STATUS = 'wppdev_wo_status';
	
	protected $capability = 'manage_options';
	
	/**
	 * Pointer array for other controllers.
	 */
	protected $controllers = array();

	private $plugin;
	
	/**
	 * Create active controllers, enqueue scripts.
	 */
	public function __construct( $plugin ) {
		parent::__construct();

		$this->plugin = $plugin;
		
		// Register all available styles and scripts. Nothing is enqueued.
		$this->add_action( 'wp_loaded', 'wp_loaded' );

		// Setup plugin admin UI.
		$this->add_action( 'admin_menu', 'add_menu_pages' );

		// Initialize controllers that are available in public.
		$controllers = [
			'Controller_Status' => new Controller_Status(),
			'Controller_Email' => new Controller_Email(),
			'Controller_Event' => new Controller_Event(),
			// 'Controller_Cron' => new Controller_Cron(),
		];
		
		// Initialize controllers available in admin.
		if( is_admin() ) {
			$controllers['Controller_Admin_Status'] = new Controller_Admin_Status();
			$controllers['Controller_Admin_Order'] = new Controller_Admin_Order();
		}
		$this->controllers = $controllers;

		//Register scripts
		$this->add_action( 'wo_register_admin_scripts', 'register_admin_scripts' );
		$this->add_action( 'wo_register_admin_scripts', 'register_admin_styles' );
		$this->add_action( 'wo_register_public_scripts', 'register_public_scripts' );
		$this->add_action( 'wo_register_public_scripts', 'register_public_styles' );
		
		// Enqueue admin styles (CSS)
		$this->add_action( 'admin_enqueue_scripts', 'enqueue_plugin_admin_styles' );

		// Enqueue styles used in the front end (CSS)
		$this->add_action( 'wp_enqueue_scripts', 'enqueue_plugin_styles' );

		// Enqueue admin scripts (JS)
		$this->add_action( 'admin_enqueue_scripts', 'enqueue_plugin_admin_scripts' );

		// Register scripts used in the front end (JS)
		$this->add_action( 'wp_enqueue_scripts', 'enqueue_plugin_scripts' );
	}
	
	/**
	 * Register scripts and styles
	 *
	 */
	public function wp_loaded() {
		if ( is_admin() ) {
			do_action( 'wo_register_admin_scripts' );
		} 
		else {
			do_action( 'wo_register_public_scripts' );
		}
	}

	/**
	 * Add Dashboard navigation menus.
	 */
	public function add_menu_pages() {

		//Only add menu if woocommerce is active.
		if ( ! class_exists( 'Automattic\WooCommerce\Admin\Loader' ) ) {
    		return;
    	}

		$pages = array(
			'wppdev-wo-status' => array(
				'parent_slug' => self::MENU_SLUG,
				'page_title' => __( 'Order Status', WPPDEV_WO_TXT_DM ),
				'menu_title' => __( 'Order Status', WPPDEV_WO_TXT_DM ),
				'menu_slug' => self::MENU_STATUS,
				'capability' => $this->capability,
				'function' => array( $this->controllers['Controller_Admin_Status'], 'page_admin' ),
			),
        );
		
		if( ! empty( $pages ) ) {
			// Create submenus
			foreach ( $pages as $page ) {
				add_submenu_page(
					$page['parent_slug'],
					$page['page_title'],
					$page['menu_title'],
					$page['capability'],
					$page['menu_slug'],
					$page['function'],
					2
				);
			}
		}
		
		
	}

	/**
	 * Get admin url.
	 */
	public static function get_admin_url() {
		return apply_filters(
			'wo_controller_plugin_get_admin_url',
			admin_url( 'admin.php?page=' . self::MENU_SLUG )
		);
	}

	/**
	 * Get admin settings url.
	 */
	public static function get_admin_settings_url() {
		$url = esc_url( add_query_arg(
			'page',
			self::MENU_STATUS,
			admin_url( 'admin.php' )
		) );
		return apply_filters(
			'wo_controller_plugin_get_admin_url',
			$url
		);
	}

	/**
	 * Register admin scripts.
	 */
	public function register_admin_scripts() {
	    $this->register_public_scripts();
	    
	    $script_path = sprintf( '%sbuild/order-status-admin.js', $this->plugin->dir );
	    $script_asset_path = sprintf( '%sbuild/order-status-admin.asset.php', $this->plugin->dir );
	    $script_asset = file_exists( $script_asset_path )
		    ? require( $script_asset_path )
		    : array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
		$script_url = sprintf( '%sbuild/order-status-admin.js', $this->plugin->url );
	    
	    wp_register_script(
    		'wo-order-status-admin',
    		$script_url,
    		$script_asset['dependencies'],
    		$script_asset['version'],
    		true,
	    );
	    
		$script_path = sprintf( '%sbuild/order-admin.js', $this->plugin->dir );
	    $script_asset_path = sprintf( '%sbuild/order-admin.asset.php', $this->plugin->dir );
	    $script_asset = file_exists( $script_asset_path )
		    ? require( $script_asset_path )
		    : array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
		$script_url = sprintf( '%sbuild/order-admin.js', $this->plugin->url );
	    
	    wp_register_script(
    		'wo-order-admin',
    		$script_url,
    		$script_asset['dependencies'],
    		$script_asset['version'],
    		true,
	    );
	}

	/**
	 * Register admin styles.
	 */
	public function register_admin_styles() {
	    wp_register_style(
	        'wo-admin',
	        sprintf( '%s%s', $this->plugin->url, 'app/assets/css/wo-admin.css' ),
	        null,
	        $this->plugin->version
	   );
	    
	    wp_register_style(
    		'wo-order-status-admin',
	    	sprintf( '%sbuild/order-status-admin.css', $this->plugin->url ),
	    	array( 'wp-components' ),
	    	filemtime( sprintf( '%sbuild/order-status-admin.css', $this->plugin->dir ) )
    	);

	    wp_register_style(
    		'wo-order-admin',
	    	sprintf( '%sbuild/order-admin.css', $this->plugin->url ),
	    	array(),
	    	filemtime( sprintf( '%sbuild/order-admin.css', $this->plugin->dir ) )
    	);
	}

	/**
	 * Register front-end scripts.
	 */
	public function register_public_scripts() {
		
	}

	/**
	 * Register front end styles.
	 */
	public function register_public_styles() {

	}

	/**
	 * Enqueue admin CSS.
	 */
	public function enqueue_plugin_admin_styles() {

	}

	/**
	 * Enqueue front end CSS.
	 */
	public function enqueue_plugin_styles() {
		
	}

	/**
	 * Enqueue admin js.
	 */
	public function enqueue_plugin_admin_scripts() {

	}

	/**
	 * Enqueue Res api nonce.
	 * Pass params to js using localize script function.
	 */
	public function enqueue_plugin_scripts() {
		
		wp_localize_script( 'wp-api', 'wpApiSettings', array(
				'root' => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' )
		) );
	}	
}