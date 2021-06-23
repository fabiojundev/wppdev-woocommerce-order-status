<?php
namespace WPPluginsDev\Traits;
/**
 * Get and Set magic methods.
 * Set fields method.
 * 
 * @since 1.0.0
 */
trait Trait_Getset {
	/**
	 * Fields protected from set_fields method.
	 * @var array
	 */
	protected $protected_fields = array( 'id' );
	
	public function set_fields( $fields = array(), $allowed_fields = array(), $protected_fields = array() ) {
		
		if( ! empty( $allowed_fields ) && is_array( $allowed_fields ) ) {
			foreach( $fields as $field => $value ) {
				if( in_array( $field, $allowed_fields ) ) {
					$this->__set( $field, $value );
				}
			}
		}
		else {
			if( ! empty( $protected_fields ) && is_array( $protected_fields ) ) {
				$protected_fields = array_merge( $protected_fields, $this->protected_fields );
			}
			else {
				$protected_fields = $this->protected_fields;
			}
			foreach( $fields as $field => $value ) {
				if( ! in_array( $field, $protected_fields ) ) {
					$this->__set( $field, $value );
				}
			}
		}
	}
	
	/**
	 * Returns property associated with the render.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property The name of a property.
	 * @return mixed Returns mixed value of a property or NULL if a property doesn't exist.
	 */
	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}
	}
	
	/**
	 * Associates the render with specific property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'notes':
					if ( is_array( $value ) ) {
						$this->$property = array_map( 'sanitize_text_field', $value );
					}
					else {
						$this->$property = array( sanitize_text_field( $value ) );
					}
					break;
					
				default:
					$this->$property = $value;
					break;
			}
		}
	}
}
