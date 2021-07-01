<?php
/**
 * Plugin Name: WPPluginsDev WooCommerce Order Status
 * Plugin URI: https://wpplugins.dev
 * Description: Manage Woo Order Status and Workflow 
 * Version: 1.0.2
 * Author: WPPluginsDev
 * Author URI: https://wpplugins.dev
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /app/languages
 * Text Domain: wppdev-woocommerce-order-status
 */

 /**
 * Copyright notice
 *
 * @copyright WPPluginDev (https://wpplugins.dev/)
 *           
 *            Authors: Fabio Jun, Leandro Yukiu
 *           
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *         
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *         
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *          GNU General Public License for more details.
 *         
 *          You should have received a copy of the GNU General Public License
 *          along with this program; If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

/**
 * Plugin name dir constant.
 *
 */
define( 'WPPDEV_WO_PLUGIN_NAME', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Plugin version
 *
 */
define( 'WPPDEV_WO_PLUGIN_VERSION', '1.0.2' );

/**
 * Plugin base dir
 */
define( 'WPPDEV_WO_PLUGIN_BASE_DIR', dirname( __FILE__ ) );

include WPPDEV_WO_PLUGIN_BASE_DIR . '/vendor/autoload.php';

use WPPluginsDev\Traits\Trait_Hook;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\Helpers\Helper_Util;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Plugin;
use WPPluginsDev\WooOrderWorkflow\Controllers\Rest\Controller_Rest_Status;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Settings;


/**
 * Primary plugin class.
 * 
 * Initialize required plugin hooks.
 * Init CPT and Rest points registration.
 * 
 * Control of plugin is passed to the MVC implementation found
 * inside the /app/ folder.
 *
 * @return object Plugin instance.
 */
class WooOrderWorkflowPlugin {

	/**
	 * WP hooking wrapper.
	 */
	use Trait_Hook;
	
	/**
	 * Singleton instance of the plugin.
	 */
	private static $instance = null;

	/**
	 * The plugin name.
	 */
	private $name;

	/**
	 * The plugin version.
	 */
	private $version;

	/**
	 * The plugin file.
	 */
	private $file;

	/**
	 * The plugin path.
	 */
	private $dir;

	/**
	 * The plugin URL.
	 */
	private $url;

	/**
	 * The main controller of the plugin.
	 */
	private $controller;

	/**
	 * Plugin constructor.
	 *
	 * Set properties, registers hooks and loads the plugin.
	 */
	public function __construct() {


		/** Setup plugin properties */
		$this->name = WPPDEV_WO_PLUGIN_NAME;
		$this->version = WPPDEV_WO_PLUGIN_VERSION;
		$this->file = __FILE__;
		$this->dir = plugin_dir_path( __FILE__ );
		$this->url = plugin_dir_url( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );

		$this->add_filter( 'plugin_action_links_' . $this->basename, 'add_settings_link' );
		
		$this->add_action( 'init', 'register_custom_post_types', 1 );

		/**
		 * Hooks init to create the primary plugin controller.
		 */
		$this->add_action( 'init', 'plugin_constructing' );
		
		$this->add_action( 'rest_api_init', 'rest_api_init' );
		
		$this->add_action( 'plugins_loaded', 'plugin_localization' );

		register_activation_hook( __FILE__, [ $this, 'install' ] );
		
		// $this->uninstall();
		/** Grab instance of self. */
		self::$instance = $this;		
	}

	/**
	 * Add Settings Link in admin plugins list.
	 */
	public function add_settings_link( $links ) {

		$links[] = sprintf( 
			'<a href="%s">%s</a>', 
			esc_url( Controller_Plugin::get_admin_settings_url() ),
			__( 'Settings', 'wppdev-woocommerce-order-status' ),
		);
		return $links;
	}

	/**
	 * Loads primary plugin controllers.
	 */
	public function plugin_constructing() {

		$this->controller = new Controller_Plugin( $this );

	}

	/**
	 * Register REST API Routes.
	 * Create Controllers to handle REST calls.
	 */
	public function rest_api_init() {
		
		$controllers = array(
			'Controller_Rest_Status' => new Controller_Rest_Status(),
		);
		
		foreach( $controllers as $controller ) {
			$controller->register_routes();
		}		
	}

	/**
	 * Register plugin custom post types.
	 *
	 * @return void
	 */
	public function register_custom_post_types() {
	
		$cpts = array(
			Model_Status::$POST_TYPE => Model_Status::get_register_post_type_args(),
		);

		foreach ( $cpts as $cpt => $args ) {
		    Helper_Util::register_post_type( $cpt, $args );
		}
	}
	
	/**
	 * Flush rewrite rules.
	 * Flush and save flushed status.
	 */
	public function flush_rewrite_rules( $force = false ) {
		$flushed = Model_Settings::get_custom_setting( 'general', 'flushed_rewrite_rules' );
		if( $force || empty( $flushed ) ) {
			flush_rewrite_rules( true );
			Model_Settings::set_custom_setting( 'general', 'flushed_rewrite_rules', true );
		}
	}
	
	/**
	 * Load plugin localization files.
	 * Place files in plugin-dir/languages folder.
	 * ex: woocommerce-order-workflow-pt_BR.mo
	 */
	public function plugin_localization() {
	    load_plugin_textdomain(
	        'wppdev-woocommerce-order-status',
	        false,
	        WPPDEV_WO_PLUGIN_NAME . '/languages'
	    );
	}

	/**
	 * Load core order statuses.
	 * 
	 * Register uninstall hook.
	 */
	public function install() {
		register_uninstall_hook( __FILE__, [ $this, 'uninstall' ] );
		
		$core = Model_Status::get_core_statuses();
		if( empty( $core ) ) {
			Model_Status::load_core_statuses();
		}
	}

	/**
	 * Clear all order status custom post type.
	 */
	public function uninstall() {
		error_log( "UNINSTALL " . __FILE__ );

		$statuses = Model_Status::get_statuses();
		foreach( $statuses as $status ) {
			$status->delete();
			error_log( "Deleted order status id: $status->id, slug: $status->slug" );
		}
	}
	
	/**
	 * Get singleton instance of the plugin.
	 *
	 * @return plugin instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns property associated with the plugin.
	 *
	 * @access public
	 * @param string $property The name of a property.
	 * @return mixed Returns mixed value of a property or NULL if a property doesn't exist.
	 */
	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}
	}
}

/**
 * Create an instance of the plugin object.
 *
 * This is the primary entry point for the plugin.
 */
WooOrderWorkflowPlugin::instance();
