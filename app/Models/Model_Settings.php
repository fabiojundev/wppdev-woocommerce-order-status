<?php
namespace WPPluginsDev\WooOrderWorkflow\Models;
use WPPluginsDev\Models\Model_Settings as ModelSettings;
use WPPluginsDev\Helpers\Helper_Debug;

class Model_Settings extends ModelSettings {

	/**
	 * ID of the model object.
	 */
	protected $id = 'wo_plugin_settings';

	/**
	 * Current db version.
	 */
	protected $version;

	/**
	 * Plugin enabled status indicator.
	 */
	protected $plugin_enabled = false;
	
	protected $custom = [
		'page' => [
			'register' => 0,
			''
		],
	];

	/**
	 * Set specific property.
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