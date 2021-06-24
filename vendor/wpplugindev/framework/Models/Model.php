<?php
namespace WPPluginsDev\Models;
use WPPluginsDev\Traits\Trait_Getset;
use DateTime;

define( 'WPPDEV_TXT_DM', 'wpplugins-dev' );

/**
 * Base Model Abstract Class.
 *
 * @since 1.0.0
 */
class Model {

	use Trait_Getset;

	/**
	 * ID of the model object.
	 *
	 * @since 1.0.0
	 *       
	 * @var int|string
	 */
	protected $id;
	
	/**
	 * Model name.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $name;
	
	/**
	 * Non persisting fields.
	 *
	 * @since 1.0.0
	 *       
	 * @var string[]
	 */
	public $ignore_fields = array( 'actions', 'filters', 'ignore_fields', 'protected_fields' );

	/**
	 * Model Contstuctor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		
		/**
		 * Actions to execute when constructing the parent Model.
		 *
		 * @since 1.0.0
		 * @param object $this The Model object.
		 */
		do_action( 'wppdev_model_construct', $this );
	}

	/**
	 * Set field value, bypassing the __set validation.
	 *
	 * Used for loading from db.
	 *
	 * @since 1.0.0
	 *       
	 * @param string $field
	 * @param mixed $value
	 */
	public function set_field( $field, $value ) {
		if ( property_exists( $this, $field ) ) {
			$this->$field = $value;
		}
	}

	/**
	 * Called before saving model.
	 *
	 * @since 1.0.0
	 */
	public function before_save() {
		do_action( 'wppdev_model_before_save', $this );
	}

	/**
	 * Abstract method to save model data.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		throw new Exception( 'Method to be implemented in child class' );
	}

	/**
	 * Called after saving model data.
	 *
	 * @since 1.0.0
	 */
	public function after_save() {
		do_action( 'wppdev_model_after_save', $this );
	}

	/**
	 * Called before loading the model.
	 *
	 * @since 1.0.0
	 */
	public function before_load() {
		do_action( 'wppdev_model_before_load', $this );
	}

	/**
	 * Load the model data.
	 *
	 * @since 1.0.0
	 */
	public static function load( $model_id = false ) {
		throw new Exception( "Method to be implemented in child class" );
	}

	/**
	 * Called after loading model data.
	 *
	 * @since 1.0.0
	 */
	public function after_load() {
		do_action( 'wppdev_model_after_load', $this );
	}

	/**
	 * Called before deleteing the model.
	 *
	 * @since 1.0.0
	 */
	public function before_delete() {
		do_action( 'wppdev_model_before_delete', $this );
	}

	/**
	 * Load the model data.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
	}

	/**
	 * Called after deleting model data.
	 *
	 * @since 1.0.0
	 */
	public function after_delete() {
		do_action( 'wppdev_model_after_delete', $this );
	}

	/**
	 * Get object properties.
	 *
	 * @since 1.0.0
	 *       
	 * @return array of fields.
	 */
	public function get_object_vars() {
		return get_object_vars( $this );
	}
	
	/**
	 * Return json of properties of this object.
	 * 
	 * @since 1.0.0
	 *       
	 * @return json string.
	 */
	public function to_json() {
		return json_encode( $this->to_array() );
	}

		/**
	 * Return array of properties of this object.
	 *
	 * @since 1.0.0
	 *
	 * @return array of fields.
	 */
	public function to_array() {
		$fields = $this->get_object_vars();

		foreach ( $fields as $field => $val ) {
			if ( in_array( $field, $this->ignore_fields ) || 'ignore_fields' == $field ) {
				unset( $fields[ $field ] );
			}
			if( $val instanceof self || $val instanceof static ) {
				$fields[ $field ] = $val->to_array();
			}
			if( is_array( $val ) && ! empty( $el = reset( $val ) ) && $el instanceof self ) {
				$fields[ $field ] = array_map( function( $v ) { 
					return $v->to_array(); 
					}, 
					$val 
				);
			}
		}
		return $fields;
	}
	
	/**
	 * Validate dates used within models.
	 *
	 * @since 1.0.0
	 *       
	 * @param string $date Date as a PHP date string
	 * @param string $format Date format.
	 */
	public function validate_date( $date, $format = 'Y-m-d' ) {
		$valid = null;
		
		$d = new DateTime( $date );
		if ( $d && $d->format( $format ) == $date ) {
			$valid = $date;
		}
		
		return apply_filters( 'wppdev_model_validate_date', $valid, $date, $format, $this );
	}

	/**
	 * Validate booleans.
	 *
	 * @since 1.0.0
	 *       
	 * @param bool $value The value to validate.
	 */
	public function validate_bool( $value ) {
		$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		
		return apply_filters( 'wppdev_model_validate_bool', $value, $this );
	}

	/**
	 * Validate minimum values.
	 *
	 * @since 1.0.0
	 *       
	 * @param int $value Value to validate
	 * @param int $min Minimum value
	 */
	public function validate_min( $value, $min ) {
		$valid = intval( ( $value > $min ) ? $value : $min );
		
		return apply_filters( 'wppdev_model_validate_min', $valid, $value, $min, $this );
	}

	/**
	 * Validate time periods array structure.
	 *
	 * @since 1.0.0
	 *       
	 * @param string $period The period to validate
	 * @param int $default_period_unit Number of periods (e.g. number of days)
	 * @param string $default_period_type (e.g. days, weeks, years)
	 */
	public function validate_period( $period, $default_period_unit = 0, $default_period_type = Helper_Period::PERIOD_TYPE_DAYS ) {
		$default = array( 'period_unit' => $default_period_unit, 'period_type' => $default_period_type );
		
		if ( !empty( $period[ 'period_unit' ] ) && !empty( $period[ 'period_type' ] ) ) {
			$period[ 'period_unit' ] = $this->validate_period_unit( $period[ 'period_unit' ] );
			$period[ 'period_type' ] = $this->validate_period_type( $period[ 'period_type' ] );
		}
		else {
			$period = $default;
		}
		
		return apply_filters( 'wppdev_model_validate_period', $period, $this );
	}

	/**
	 * Validate period unit.
	 *
	 * @since 1.0.0
	 *       
	 * @param string $period_unit The period quantity to validate.
	 * @param int $default The default value when not validated. Default to 1.
	 */
	public function validate_period_unit( $period_unit, $default = 1 ) {
		$period_unit = intval( $period_unit );
		
		if ( $period_unit <= 0 ) {
			$period_unit = $default;
		}
		
		return apply_filters( 'wppdev_model_validate_period_unit', $period_unit, $this );
	}

	/**
	 * Validate period type.
	 *
	 * @since 1.0.0
	 *       
	 * @param string $period_type The period type to validate.
	 * @param int $default The default value when not validated. Default to days.
	 */
	public function validate_period_type( $period_type, $default = Helper_Period::PERIOD_TYPE_DAYS ) {
		if ( !in_array( $period_type, Helper_Period::get_periods() ) ) {
			$period_type = $default;
		}
		
		return apply_filters( 'wppdev_model_validate_period_type', $period_type, $this );
	}

	public function validate_url( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED ) !== false;
	}
	
	public function get_formatted_price( $value ) {
		return Helper_Util::format_price( $value );
	}
}