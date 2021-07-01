<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers\Admin;

use WPPluginsDev\WooOrderWorkflow\Controllers\Controller;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Plugin;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
use WPPluginsDev\WooOrderWorkflow\Views\Admin\View_Admin_Status;
use WPPluginsDev\WooOrderWorkflow\Controllers\Rest\Controller_Rest_Status;
/**
 * Admin Status Controller.
 */
class Controller_Admin_Status extends Controller {
	const WO_ORDER_STATUS_ADMIN = 'wo-order-status-admin';
	const ACTION_EDIT = 'edit';
	const ACTION_DELETE = 'delete';

	const TAB_NAME = 'wo_order_status';

	public function __construct() {
    	$hook = Controller_Plugin::MENU_PREFIX . Controller_Plugin::MENU_STATUS;
    	
    	$this->add_action( 'admin_print_scripts-' . $hook, 'enqueue_scripts' );
    	$this->add_action( 'admin_print_styles-' . $hook, 'enqueue_styles' );
		$this->add_filter( 'load_script_translation_file', 'load_script_translation_file', 10, 3 );
    }

	/**
	 * Status admin page manager.
	 * Show status view.
	 */
    public function page_admin() {
    	
    	/**
    	 * Action view page request
    	 */
    	$isset = array( 'action', 'status_id' );
		$status = null;
    	if( $this->validate_required( $isset, 'GET', false ) && self::ACTION_EDIT == $this->get_request_param( 'action' ) ) {
    		$status_id = $this->get_request_param( 'status_id' );
    		$status = Model_Status::load( $status_id );    		
    	}

		$view = new View_Admin_Status();
		$view->data = [
			'action-edit' => self::ACTION_EDIT,
			'action-delete' => self::ACTION_DELETE,
			'data_wo' => [
				'config' => [
					'api_nonce' => wp_create_nonce( 'wp_rest' ),
					'api_url' => Controller_Rest_Status::get_rest_url(),
				],
				'order_statuses' => Model_Status::get_statuses( null, true ),
				'edit' => $status ? $status->to_array() : [],
			]
		];
		$view->render();
    }
    
	/**
	 * Enqueue styles for admin status page.
	 */
    public function enqueue_styles() {
		wp_enqueue_style( self::WO_ORDER_STATUS_ADMIN );
    }
    
	/**
	 * Enqueue scripts for admin status page.
	 * Set translations folder for js files.
	 */
    public function enqueue_scripts() {
		wp_enqueue_script( self::WO_ORDER_STATUS_ADMIN );
		
		wp_set_script_translations( 
			self::WO_ORDER_STATUS_ADMIN, 
			'wppdev-woocommerce-order-status', 
			\WooOrderWorkflowPlugin::instance()->dir . 'languages/'
		);
    }

	/**
	 * Load js translation files.
	 */
	public function load_script_translation_file($file, $handle, $domain) {
        $locale = determine_locale();
        if( $handle === self::WO_ORDER_STATUS_ADMIN && !is_readable($file) ) {
            $folder = dirname( $file );
			$file = path_join( $folder, $domain . '-js-'. $locale . '.json');
        }
        return $file;
    }
}