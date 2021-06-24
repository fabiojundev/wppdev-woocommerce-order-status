<?php
namespace WPPluginsDev\WooOrderWorkflow\Traits;

use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\Helpers\Helper_Period;

/**
 * Conditions Trait.
 * 
 * @since 1.0.0
 */
trait Trait_Conditions {

    protected $conditions = [];

    /**
     * Verify if a conditions is met in event.
     * 
     * @param Model_Status_Event The event to verify.
     * @param Model_Status The order status conditions.
     */
    public function verify_event_condition( $event, $status  ) {
        $to_status = $event->get_to_status();
        $from_status  = $event->get_from_status();
        $verified = true;
        $conditions = $this->conditions;

        if( ! empty( $conditions['enabled'] ) ) {
            $verified = false;
                // if( $status && $status->id && $to_status && $to_status->id && $from_status && $from_status->id ) {
            if( $status->id && $to_status->id && $from_status->id ) {

                if( empty( $conditions['if_overdue'] ) && empty( $conditions['from_statuses'] ) ) {
                    Helper_Debug::debug( "if overdue is true and from statuses is empty");
                    $verified = true;
                }

                if( ! empty( $from_statuses = $conditions['from_statuses'] ) ) {
                    foreach( $from_statuses as $from_id ) {
                        if( $from_id == $from_status->id && $status->id == $to_status->id ) {
                            Helper_Debug::debug( "from_statueses ok: from_id: $from_id, to_id: $status->id, slug: $status->slug");
                            $verified = true;
                            break;
                        }
                    }
                }

                if( ! empty( $conditions['if_overdue'] ) && $status->days_estimation ) {
                    $days = Helper_Period::subtract_dates( $event->event_dt, date('Y-m-d') );
                    Helper_Debug::debug( "days: $days, estimative: $status->days_estimation");
                    if( $days >= $status->days_estimation ) {
                        $verified  = true;
                    }
                    else {
                        $verified  = false;
                    }
                }
            }
        }
        // Helper_Debug::debug( "Status: [$status->slug] verified: [$verified], order_id: $event->order_id, from: $from_status->slug, to: $to_status->slug");
        return $verified;
    }

    /**
     * Set conditions.
     * 
     * @param array $conditions The conditions to set.
     */
    public function set_conditions( $conditions ) {
        if ( is_array( $conditions ) ) {
            foreach( $conditions as $field => $val ) {
                switch( $field ) {
                    case 'enabled':
                    case 'if_overdue':
                        $conditions[ $field ] = boolval( $val );
                        break;
                    case 'from_statuses':
                        $conditions[ $field ] = array_map( 'intval', $val );
                        break;
                    default:
                    case 'desc':    
                        $conditions[ $field ] = sanitize_text_field( $val );
                    break;
                }
            }
            $this->conditions = $conditions;
        }
        else {
            $this->conditions = array( sanitize_text_field( $conditions ) );
        }
    }

	/**
	 * Get specific property value.
	 *
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
            Helper_Debug::debug("set trait propertu, $property: $value");
			switch ( $property ) {
				case 'conditions':
                    $this->set_conditions( $value );
					break;
					
				default:
					$this->$property = $value;
					break;
			}
		}
	}
}
