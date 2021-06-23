<?php
namespace WPPluginsDev\Models;
use WPPluginsDev\Models\Model_Option;
use WPPluginsDev\Helpers\Helper_Util;
use WPPluginsDev\Helpers\Helper_Debug;

class Model_Settings extends Model_Option {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * 
	 * @staticvar JN_Model_Settings
	 */
	public static $instance;

	/**
	 * ID of the model object.
	 *
	 * @since 1.0.0
	 * 
	 * @var int
	 */
	protected $id = 'wd_plugin_settings';

	/**
	 * Model name.
	 *
	 * @since 1.0.0
	 * 
	 * @var string 
	 */
	protected $name = 'Plugin settings';

	/**
	 * Current db version.
	 *
	 * @since 1.0.0
	 * 
	 * @var string 
	 */
	protected $version;

	/**
	 * Plugin enabled status indicator.
	 *
	 * @since 1.0.0
	 * 
	 * @var boolean 
	 */
	protected $plugin_enabled = false;
	
	protected $custom = [
		'page' => [
			'register' => 0,
			''
		],
	];
	

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
		$settings = static::load();

		if ( property_exists( $settings, $field ) ) {
			$value = $settings->$field;
		}

		return $value;
	}

	/**
	 * Set custom setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group The custom setting group.
	 * @param string $field The custom setting field.
	 * @param mixed $value The custom setting value.
	 */
	public static function set_custom_setting( $group, $field, $value ) {
		
		$settings = static::load();
		$settings->custom[ $group ][ $field ] = $value;
		$settings->save();
	}

	/**
	 * Get custom setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group The custom setting group.
	 * @param string $field The custom setting field.
	 * @return mixed $value The custom setting value.
	 */
	public static function get_custom_setting( $group, $field, $default = '' ) {
		
		$value = $default;
		$settings = static::load();
		
		if ( isset( $settings->custom[ $group ][ $field ] ) ) {
			$value = stripslashes( $settings->custom[ $group ][ $field ] );
		}
		
		return $value;
	}

	/**
	 * Set specific property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'plugin_enabled':
					$this->$property = $this->validate_bool( $value );
					break;
				default:
					$this->$property = $value;
					break;
			}
		}
	}

	/**
	 * Returns a specific property.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $property The name of a property.
	 * @return mixed $value The value of a property.
	 */
	public function __get( $property ) {
		
		$value = null;
		
		if ( property_exists( $this, $property ) ) {
			$value = $this->$property;
		}
		
		return $value;
	}
}