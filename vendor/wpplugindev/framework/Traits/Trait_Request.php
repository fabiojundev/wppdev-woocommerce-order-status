<?php
namespace WPPluginsDev\Traits;
use WPPluginsDev\Helpers\Helper_Util;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\Models\Model_User;

/**
 * HTTP Request get methods.
 * Validate nonce methods. 
 *
 * @since 1.0.0
 */
trait Trait_Request {
    
    public $nonce_field = '_wdnonce';

	/**
	 * Get action from GET request.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_action() {
		return $this->get_request_param( 'action' );
	}
	
	/**
	 * Get action from POST request.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_post_action() {
		return $this->get_request_field( 'action' );
	}
	
	/**
	 * Verify nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action The action name to verify nonce.
	 * @param string $request_method POST or GET
	 * @param string $nonce_field The nonce field name
	 * @return boolean True if verified, false otherwise.
	 */
	public function verify_nonce( $action = null, $request_method = 'POST', $nonce_field = '' ) {
		$request_fields = ( 'POST' == $request_method ) ? $_POST : $_GET;
		$nonce_field = $nonce_field ? $nonce_field : $this->nonce_field;
		return Helper_Util::verify_nonce( $request_fields, $action, $request_method, $nonce_field );
	}
	
	/**
	 * Create nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action The action name to create nonce from.
	 * @return string The created nonce.
	 */
	public static function create_nonce( $action = 'wp_rest' ) {		
		return wp_create_nonce( $action );
	}
	
	/**
	 * Validate nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param string $request_fields The fields from request.
	 * @param string $action The action name to verify nonce.
	 * @param string $request_method POST or GET
	 * @param string $nonce_field The nonce field name
	 * @return boolean True if verified, false otherwise.
	 */
	public function validate_nonce( $request_fields, $action = '', $request_method = 'POST', $nonce_field = '' ) {
	    $nonce_field = $nonce_field ? $nonce_field : $this->nonce_field;
		return Helper_Util::verify_nonce( $request_fields, $action, $request_method, $nonce_field );
	}
	
	/**
	 * Verify nonce in GET params.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action The action name to verify nonce.
	 * @param string $nonce_field The nonce field name
	 * @return boolean True if verified, false otherwise.
	 */
	public function verify_nonce_get( $action = null, $nonce_field = '' ) {
	    $nonce_field = $nonce_field ? $nonce_field : $this->nonce_field;
		return $this->verify_nonce( $action, 'GET', $nonce_field );
	}
	
	/**
	 * Verify if current user can perform management actions.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if can, false otherwise.
	 */
	public function is_admin_user( $capability = null ) {
		if ( empty( $capability ) ) {
			$capability = $this->capability;
		}
		
		$is_admin_user = Model_User::is_admin_user( null, $capability );
		
		return $is_admin_user;
	}
	
	/**
	 * Verify required fields aren't empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $fields The array of fields to validate.
	 * @param string $request_method POST or GET
	 * @param bool $not_empty if true use empty method, else use isset method.
	 * @return bool True all fields are validated
	 */
	public function validate_required( $fields, $request_method = 'POST', $not_empty = true ) {
		$validated = Helper_Util::validate_required( $fields, $request_method, $not_empty );
		return apply_filters( 'wd_trait_request_validate_required', $validated, $fields, $this );
	}
	
	/**
	 * Verify required fields aren't empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $fields The array of fields to validate.
	 * @param string[] $request_fields The fields to validate against.
	 * @param bool $not_empty if true use empty method, else use isset method.
	 * @return bool True all fields are validated
	 */
	public function validate_req_fields( $required, $request_fields = array(), $not_empty = true ) {		
		$validated = Helper_Util::validate_req_fields( $required, $request_fields, $not_empty );
		return apply_filters( 'wd_trait_request_validate_req_fields', $validated, $fields, $this );
	}

	/**
	 *
	 * @param unknown $fields
	 * @return boolean
	 */
	public function validate_required_params( $fields, $not_empty = true ) {
		$validated = Helper_Util::validate_required( $fields, 'GET', $not_empty );
		return apply_filters( 'wd_trait_request_validate_required_params', $validated, $fields, $this );
	}
	
	/**
	 * Get field from request parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The field ID
	 * @param mixed $default The default value of the field.
	 * @param string $request_method POST or GET
	 * @return mixed The value of the request field.
	 */
	public function get_request_field( $id, $default = '', $request_method = 'POST' ) {
		return Helper_Util::get_request_field( $id, $default, $request_method );
	}
	
	/**
	 * Get field from GET parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The param ID
	 * @param mixed $default The default value of the field.
	 * @return mixed The value of the request field.
	 */
	public function get_request_param( $id, $default = '' ) {
		return $this->get_request_field( $id, $default, 'GET' );
	}
	
	/**
	 * Get Rest Auth Error.
	 *
	 * @since 1.0.0
	 *
	 * @param string $redir_url The url to redirect to.
	 * @return WP_Error The error.
	 */
	public static function get_auth_error( $redir_url = null ) {
	    return Helper_Util::get_auth_error( $redir_url );
	}
}
