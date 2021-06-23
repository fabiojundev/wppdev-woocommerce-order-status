<?php
namespace WPPluginsDev\Models;
/**
 * Transient Abstract Model Class.
 *
 * @since 1.0.0
 */
class Model_Transient extends Model {
	
	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 *       
	 * @staticvar Transient
	 */
	public static $instance;
	
	/**
	 * Load Transient object.
	 *
	 * CA_Transient objects are singletons.
	 *
	 * @since 1.0.0
	 *
	 * @param Transient $model The empty model instance.
	 *
	 * @return Transient The retrieved object.
	 */
	public static function load( $model_id = false ) {
		$model = new static();
		$class = get_class( $model );
		
		if ( empty( $class::$instance ) ) {
			// 			$cache = wp_cache_get( $class, 'Transient' );
			
			if ( $cache ) {
				$model = $cache;
				// 				CA_Helper_Debug::log("---------from cache, $class");
			}
			else {
				$model->before_load();
				// 				CA_Helper_Debug::log("---------from DB, $class");
				$settings = get_transient( $class );
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
				
				$model->after_load();
				
				wp_cache_set( $class, $model, 'Model_Transient' );
			}
		}
		else {
			$model = $class::$instance;
			// 			CA_Helper_Debug::log("---------from singleton, $class");
		}
		
		return apply_filters(
				'wd_model_transient_load',
				$model,
				$class
				);
	}

	/**
	 * Save content in wp_option table.
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
		
		set_transient( $class, $settings );
		
		$this->instance = $this;
		$this->after_save();
		
		wp_cache_set( $class, $this, 'Transient' );
	}

	/**
	 * Delete transient.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		do_action( 'wd_model_transient_delete_before', $this );
		
		$class = get_class( $this );
		delete_transient( $class );
		
		do_action( 'wd_model_transient_delete_after', $this );
	}
}