<?php
namespace WPPluginsDev\Models;

/**
 * Cookie Model.
 *
 * @since 1.0.0
 */
class Model_Cookie extends Model {
	
	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 */
	public static $instance;

	protected $expire;
	
	protected $path;
	
	protected $domain;
	
	protected $secure;
	
	protected $http_only;
	
	/**
	 * Load Cookie Object.
	 *
	 * Load from cookie.
	 *
	 * @since 1.0.0
	 *
	 * @param in $model_id Not Used.
	 *
	 * @return Cookie The retrieved object.
	 */
	public static function load( $model_id = false ) {
		$model = new static();
		$class = get_class( $model );
		
		$model->before_load();
		
		if( isset( $_COOKIE[ $class ] ) ) {
			$settings = json_decode( str_replace( '\/', '/' , str_replace( '\"', '"', $_COOKIE[ $class ] ) ), true );
			$fields = $model->get_object_vars();
			
			foreach ( $fields as $field => $val ) {
				if ( in_array( $field, $model->ignore_fields ) ) {
					continue;
				}
				
				if ( isset( $settings[ $field ] ) ) {
					$model->set_field( $field, $settings[ $field ] );
				}
			}
			$class::$instance = $model;
		}
		
		$model->after_load();
		
		return apply_filters(
				'wppdev_model_cookie_load',
				$model,
				$class
				);
	}
	
	/**
	 * Set properties after load.
	 * {@inheritDoc}
	 * @see Model::after_load()
	 */
	public function after_load() {
 
		$this->expire = strtotime( '+1 year' );
		$this->path = '/';
		$this->domain = Util::get_current_domain();
	}
	
	/**
	 * Verify if cookie is present.
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public static function is_first_visit() {
		
		$first_visit = false;

		if( empty( $_COOKIE ) ) {
			$first_visit = true;
		}

		return $first_visit;
	}
	
	/**
	 * Save content in cookie.
	 *
	 * Update WP cache and instance singleton.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		$this->before_save();

		$class = get_class( $this );
		$settings = array();

		$fields = get_object_vars( $this );
		foreach ( $fields as $field => $val ) {
			if ( in_array( $field, $this->ignore_fields ) ) {
				continue;
			}
			$settings[ $field ] = $this->$field;
		}

		setcookie( $class, json_encode( $settings ), $this->expire, $this->path, $this->domain, $this->secure, $this->http_only );

		$this->instance = $this;
		
		$this->after_save();
	}

	/**
	 * Delete cookie.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		do_action( 'wppdev_model_cookie_delete_before', $this );

		$class = get_class( $this );
		setcookie( $class, '', time() - 3600 );

		do_action( 'wppdev_model_cookie_delete_after', $this );
	}
}