<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers\Rest;

use WPPluginsDev\Controllers\Rest\Rest;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\Models\Model_User;
use WPPluginsDev\WooOrderWorkflow\Models\Model_TriggerSettings;
use WPPluginsDev\WooOrderWorkflow\Models\Model_EmailSettings;
use WPPluginsDev\WooOrderWorkflow\Models\Status\Model_Status_Product;

/**
 * Order Status Rest Controller.
 *
 * @since 1.0.0
 */
class Controller_Rest_Status extends Rest {
	const REST_NAMESPACE = 'wo/v1';
	const REST_BASE = 'order-status';

	const ACTION_CREATE = 'create-status';
	const ACTION_UPDATE = 'update-status';
	const ACTION_DELETE = 'delete-status';
	const ACTION_TOGGLE_ENABLE = 'toggle-enable';
	
	/**
	 * Get REST statuses url.
	 */
	public static function get_statuses_url() {
		$url = sprintf( "%s%s/%s/", rest_url(), self::REST_NAMESPACE, self::REST_BASE );
		return $url;
	}
	
	/**
	 * Get save/update url.
	 */
	public static function get_save_url( $status_id ) {
		return sprintf( "%s%s/%s/%s/", rest_url(), self::REST_NAMESPACE, self::REST_BASE, $status_id );
	}
	
	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		
		register_rest_route( self::REST_NAMESPACE, '/' . self::REST_BASE, array(
			array(
				'methods'         => \WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_statuses' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => array(),
			),
			array(
				'methods'         => \WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_status' ),
				'permission_callback' => array( $this, 'get_update_item_permissions_check' ),
				'args'            => array(),
			),
			array(
				'methods'         => \WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'import_statuses' ),
				'permission_callback' => array( $this, 'get_update_item_permissions_check' ),
				'args'            => array(),
			),
		) );

		register_rest_route( self::REST_NAMESPACE, '/' . self::REST_BASE . '/import', array(
			array(
				'methods'         => \WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'import_statuses' ),
				'permission_callback' => array( $this, 'get_update_item_permissions_check' ),
				'args'            => array(),
			),
		) );

		register_rest_route( self::REST_NAMESPACE, '/' . self::REST_BASE . '-products/(?P<id>[\d]+)', array(
			array(
				'methods'         => \WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_status_products' ),
				'permission_callback' => array( $this, 'get_update_item_permissions_check' ),
				'args'            => array(),
			),
		) );

		register_rest_route( self::REST_NAMESPACE, '/' . self::REST_BASE . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => \WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_status' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => array(),
			),
			array(
				'methods'         => \WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_status' ),
				'permission_callback' => array( $this, 'get_update_item_permissions_check' ),
				'args'            => array(),
			),
			array(
				'methods'  => \WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_status' ),
				'permission_callback' => array( $this, 'get_delete_item_permissions_check' ),
				'args'            => array(),
			),
		) );
	}

	/**
	 * Get all available statuses.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_statuses( $request ) {
		$statuses = Model_Status::get_statuses( null, true , true);

		return new \WP_REST_Response( $statuses, 200 );
	}
	
	/**
	 * Get a order status by id.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_status( $request ) {
		$status = Model_Status::load( $request->get_param( 'id' ) );
		
		if( $status->is_valid() ) {
			
			return new \WP_REST_Response( $status->to_array(), 200 );
		}
		else{
			return new \WP_Error( 404, __( 'Order Status not found', WPPDEV_WO_TXT_DM ) );
		}
	}
	
	/**
	 * Create a new order status.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_status( $request ) {
		$required = array( 'name', 'slug' );
		$fields = $request->get_params();
		Helper_Debug::log($fields);
		if( $this->validate_req_fields( $required, $fields ) ) {
			$status = new Model_Status( );
			
			$status = $this->save_status( $fields, $status );
			return new \WP_REST_Response( [
				'status' => $status->to_array(),
				'message' => __( 'Order Status Created.', WPPDEV_WO_TXT_DM ),
			], 200 );
		}
		else{
			return new \WP_Error( 404, __( 'Required fields are empty.', WPPDEV_WO_TXT_DM ) );
		}
	}
	
	/**
	 * Update order status by id.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_status( $request ) {
		$status = Model_Status::load( $request->get_param( 'id' ) );
		$required = array( 'id', 'name', 'slug' );
		$fields = $request->get_params();
		// Helper_Debug::debug($fields);
		
		if( $this->validate_req_fields( $required, $fields ) && $status->is_valid() ) {
			
			$status = $this->save_status( $fields, $status );
// 			Helper_Debug::debug($status);
			return new \WP_REST_Response( [
				'status' => $status->to_array(),
				'message' => __( 'Order Status Updated.', WPPDEV_WO_TXT_DM ),
			], 200 );

		}
		else{
			return new \WP_Error( 404, __( 'Order Status not found', WPPDEV_WO_TXT_DM ) );
		}
	}

	/**
	 * Save order status.
	 * @param array $fields The field values to save.
	 * @param Model_Status $status The order status to save.
	 * @return Model_Status $status The saved order status.
	 */
	private function save_status( $fields, $status ) {
		foreach( $fields as $field => $value ) {
			$status->$field = $value;
		}
		
		if( ! empty( $fields['email_settings'] ) && is_array( $fields['email_settings'] ) ) {
			$email_settings = new Model_EmailSettings();
			foreach( $fields['email_settings'] as $field => $value ) {
				$email_settings->$field = $value;
			}
		}

		if( ! empty( $fields['trigger_settings'] ) && is_array( $fields['trigger_settings'] ) ) {
			$trigger_settings = [];
			foreach( $fields['trigger_settings'] as $trigger ) {
				$settings = new Model_TriggerSettings();
				foreach( $trigger as $field => $value ) {
					$settings->$field = $value;
				}
				$trigger_settings[] = $settings;
			}
		}

		$status->email_settings = $email_settings;
		$status->trigger_settings = $trigger_settings;
		$status->update_orders_count();
		$status->save();
		// Helper_Debug::debug($status);

		return $status;
	}

	/**
	 * Delete order status by Id.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_status( $request ) {
		$status = Model_Status::load( $request->get_param( 'id' ) );
		$fields = $request->get_params();
		Helper_Debug::debug($fields);
		
		if( $status->is_valid() && $status->type != Model_Status::TYPE_CORE ) {
			Helper_Debug::debug("orders count: $status->orders_count");
			if( $status->orders_count ) {
				if( ! empty($fields['reassign'] ) && $reassign = Model_Status::load( $fields['reassign'] ) ) {
					
					Helper_Debug::debug("reassign to {$reassign->get_slug( true )}, $reassign->id");
					$status->reassign( $reassign );
					$status->delete();
				}
				else {
					return new \WP_Error( 404, __( 'Reassign status failed', WPPDEV_WO_TXT_DM ) );
				}
			}
			else {
				$status->delete();
			}
			return new \WP_REST_Response( __( 'Order Status Deleted', WPPDEV_WO_TXT_DM ), 200 );
		}
		else{
			return new \WP_Error( 404, __( 'Order Status not found', WPPDEV_WO_TXT_DM ) );
		}
	}
	
	/**
	 * Import pre defined order statuses.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function import_statuses( $request ) {
		$required = array( 'action', 'import_id' );
		$fields = $request->get_params();
		// Helper_Debug::debug($fields);

		if( $this->validate_req_fields( $required, $fields ) && 'import-statuses' == $fields['action'] ) {
			Model_Status::import_statuses( $fields['import_id'] );
			$statuses = Model_Status::get_statuses( null, true, true );
			return new \WP_REST_Response( [
				'statuses' => $statuses,
				'message' => __( 'Order Statuses Imported.', WPPDEV_WO_TXT_DM ),
			], 200 );
		}
		else{
			return new \WP_Error( 404, __( 'Required fields are empty.', WPPDEV_WO_TXT_DM ) );
		}
	}

	/**
	 * Load order status products info.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_status_products( $request ) {
		$required = array( 'id' );
		$fields = $request->get_params();

		if( $this->validate_req_fields( $required, $fields ) ) {
			$products = Model_Status_Product::get_status_products( $fields['id'] );
			$products = array_map( function( $product ) {
				return $product->to_array();
			}, $products );

			return new \WP_REST_Response( [
				'products' => $products,
				'message' => __( 'Products loaded.', WPPDEV_WO_TXT_DM ),
			], 200 );
		}
		else{
			return new \WP_Error( 404, __( 'Required fields are empty.', WPPDEV_WO_TXT_DM ) );
		}
	}

	/**
	 * Check if a given request has access to get order status
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return Model_User::is_admin_user();
	}
	
	/**
	 * Check if a given request has access to update a order status
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_update_item_permissions_check( $request ) {
		return Model_User::is_admin_user();
	}

	/**
	 * Check if a given request has access to delete a order status
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_delete_item_permissions_check( $request ) {
		return Model_User::is_admin_user();
	}
	
}