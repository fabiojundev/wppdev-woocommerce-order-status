<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers;

use WPPluginsDev\WooOrderWorkflow\Controllers\Controller;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
/**
 * Order Status controller in front end.
 */
class Controller_Status extends Controller {
	const ACTION_EDIT = 'edit';
	const ACTION_DELETE = 'delete';

	public function __construct() {
    	$this->register_woocommerce_statuses();
    	$this->add_filter( 'wc_order_statuses', 'add_woocommerce_order_statuses' );
    }

    /**
     * Register custom post status for woocommerce orders.
     */
    public function register_woocommerce_statuses() {
    	
    	
    	$statuses = Model_Status::get_custom_statuses();
    	foreach ( $statuses as $status ) {
			$status->register_status();
    	}
    }
    
	/**
	 * Add WooCommerce custom order status.
	 */
    public function add_woocommerce_order_statuses( $order_statuses ) {
    	
    	$statuses = Model_Status::get_statuses( true );
    	foreach ( $statuses as $status ) {
    		$order_statuses[ $status->get_slug( true ) ] = $status->name;
    	}
		// Helper_Debug::debug( $order_statuses );

    	return $order_statuses;
    }

}