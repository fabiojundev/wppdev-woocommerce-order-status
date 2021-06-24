<?php
namespace WPPluginsDev\Models;

/**
 * Option Model Abstract Class.
 *
 * @since 1.0.0
 */
class Model_Option extends Model {
	
	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 *       
	 * @staticvar Model_Option
	 */
	public static $instance;
	
	/**
	 * Object lock timestamp.
	 * 
	 * @since 1.0.0
	 */
	protected $lock;

	/**
	 * Load Option object.
	 *
	 * Option objects are singletons.
	 *
	 * @since 1.0.0
	 *
	 * @param in $model_id Not Used.
	 * @return Option The retrieved object.
	 */
	public static function load( $model_id = false ) {
		$model = new static();
		$class = get_class( $model );
		
		$cache = wp_cache_get( $class, 'Model_Option' );
		
		if ( $cache ) {
			$model = $cache;
		}
		else {
			$model->before_load();
			$settings = get_option( $class );
			
			$fields = $model->get_object_vars();
			foreach ( $fields as $field => $val ) {
				if ( in_array( $field, $model->ignore_fields ) ) {
					continue;
				}
				if ( isset( $settings[ $field ] ) ) {
					$model->set_field( $field, $settings[ $field ] );
				}
			}
		}
		
		$class::$instance = $model;
		
		$model->after_load();
		
		wp_cache_set( $class, $model, 'Model_Option' );
		
		return apply_filters(
				'wppdev_model_option_load',
				$model,
				$class
				);
	}
	
	/**
	 * Get setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field The setting to retrieve.
	 * @return mixed The setting value.
	 */
	public static function get_setting( $field ) {
		$value = null;
		$model = static::load();
		if ( property_exists( $model, $field ) ) {
			$value = $model->$field;
		}
		
		return $value;
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
		
		update_option( $class, $settings );
		
		// $this->instance = $this;
		
		$this->after_save();
		
		wp_cache_set( $class, $this, 'Model_Option' );
	}

	/**
	 * Delete from wp option table
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		do_action( 'wppdev_model_option_delete_before', $this );
		
		$class = get_class( $this );
		delete_option( $class );
		
		do_action( 'wppdev_model_option_delete_after', $this );
	}

	/**
	 * Check to see if the object is currently being edited.
	 *
	 * @since 1.0.0
	 * @see wp_check_post_lock.
	 *
	 * @return boolean True if locked.
	 */
	public function check_object_lock() {
		$locked = false;
		$time = $this->lock;
		$time_window = apply_filters( 'wppdev_model_option_check_object_lock_window', 150 );
		
		if ( $time && $time > time() - $time_window ) {
			$locked = true;
		}
		
		return $locked;
	}

	/**
	 * Mark the object as currently being edited.
	 *
	 * @since 1.0.0
	 * Based in the wp_set_post_lock
	 *
	 * @return bool|int
	 */
	public function set_object_lock() {
		$this->lock = time();
		$this->save();
		
		return $this->lock;
	}

	/**
	 * Delete object lock.
	 * 
	 * @since 1.0.0
	 */
	public function delete_object_lock() {
		$this->lock = null;
		$this->save();
	}
}