<?php
namespace WPPluginsDev\WooOrderWorkflow\Helpers;
use WPPluginsDev\Helpers\Helper_Html as PHelper_Html;
/**
 * Html Helper Class.
 * Modify custom data field in html elements.
 */
class Helper_Html extends PHelper_Html {

	/**
	 * Get field args.
	 * 
	 * @since 1.0.0 
	 * 
	 * @param string[] $field_args The html element args.
	 * @return string[] The field args with default values. 
	 */
	public static function get_field_args( $field_args ) {	
	    $data_vp = ! empty( $field_args['data_wo'] ) ? sprintf( 'data-wo="%s" ', esc_attr( json_encode( $field_args['data_wo'] ) ) ) : '';
	    $field_args = parent::get_field_args( $field_args );

	    $field_args['data_attr'] = $data_vp;
		
		return $field_args;
	}
}