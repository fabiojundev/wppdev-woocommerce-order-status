<?php
namespace WPPluginsDev\WooOrderWorkflow\Models\Status;

use WPPluginsDev\Models\Model_Cpt;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\Helpers\Helper_Period;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
/**
 * Order Status Event Model.
 * Status change events info. 
 */
class Model_Status_Event extends Model_Cpt {
	
	const TYPE_STATUS_CHANGE = 'order-status-change';
        
	public static $POST_TYPE = 'wo_status_event';

	protected $id = 0;
	
	protected $order_id;
	
	protected $name;
	
	protected $from_status_id;
	
	protected $to_status_id;
	
	protected $type = self::TYPE_STATUS_CHANGE;
	
	protected $event_dt;
	
	protected $sent_dt;

	protected $trigger_dt;
	

	/**
	 * Create status change event.
	 * 
	 * @param int $order_id The order ID.
	 * @param Model_Status $from_status The from status object.
	 * @param Model_Status $from_status The to status object.
	 * @param bool $overwrite The flag to overrite existing event.
	 */
	public static function create_event( $order_id, $from_status, $to_status, $overwrite = true ) {
		$event = null;

		$events = static::get_events( [
			'order_id' => $order_id, 
			'from_status_id' => $from_status->id, 
			'to_status_id' => $to_status->id,
			'trigger_dt' => '', //avoid duplication
		] );

		if( ! empty( $events ) && ! $overwrite ) {
			$event = reset( $events );
		}

		if( empty( $event ) ) {
			$event = new static();
			$event->name = sprintf( '[%s] %s %s > %s', 
				$order_id, 
				__( 'Order Status Change from', WO_TEXT_DOMAIN ), 
				$from_status->slug, 
				$to_status->slug 
			);
			$event->order_id = $order_id;
			$event->from_status_id = $from_status->id;
			$event->to_status_id = $to_status->id;
			if( ! $event->event_dt ) {
				$event->event_dt = Helper_Period::current_date();
			}
			// $event->event_dt = '2021-05-20';
			$event->trigger_dt = '';
			$event->sent_dt = '';
			$event->save();
			// Helper_Debug::debug("wo_model_status_event_create_event - do_action");	

			do_action( 'wo_model_status_event_create_event', $event );
		}
		// Helper_Debug::debug($event);

		return $event;
	}

	/**
	 * The order status event types.
	 */
	public static function get_types() {
		return [
			self::TYPE_STATUS_CHANGE => __( 'Order Status Change', WO_TEXT_DOMAIN ),
		];
	}
	
	/**
	 * Verify if is a valid event type.
	 * 
	 * @param string $type The event type to verify.
	 */
	public static function is_valid_type( $type ) {
		$valid = false;
		if( array_key_exists( $type, self::get_types() ) ) {
			$valid = true;
		}
		return $valid;
	}
	
	/**
	 * Get filtered events.
	 * 
	 * @param array $filters The filters definitions. See $defaults.
	 * @return Model_Event[] The events found.
	 */
	public static function get_events( $filters = [] ) {
		$defaults = [
			'sent_dt' => '',
			'trigger_dt' => '',
			'order_id' => 0, 
			'from_status_id' => '', 
			'to_status_id' => '', 
			'days_after' => 0,
		];
		$filters = wp_parse_args( $filters, $defaults );

		$args['meta_query'] = [
			[
				'key' => 'type',
				'value' => self::TYPE_STATUS_CHANGE,
			],
		];

		foreach( $filters as $field => $val ) {
			if( ! empty( $val ) ) {
				$args['meta_query'][ $field ] = [
					'key' => $field,
					'value' => $val,
				];
			}	
		}

		if( empty( $filters['sent_dt'] ) ) {
			$args['meta_query']['sent_dt'] = [
				'key' => 'sent_dt',
				'value' => '',
			];
		}

		if( empty( $filters['trigger_dt'] ) ) {
			$args['meta_query']['trigger_dt'] = [
				'key' => 'trigger_dt',
				'value' => '',
			];
		}

		if( $filters['days_after'] ) {
			try {
				$date = Helper_Period::subtract_interval( 
					$filters['days_after'], 
					Helper_Period::PERIOD_TYPE_DAYS, 
					date('Y-m-d')
				);
				$args['date_query']['before'] = $date;
	
			} catch (\Exception $e) {
			}
		}

		// Helper_Debug::debug($args);
		$events = static::load_models( $args );
		return $events;
	}
	
	/**
	 * Get from order status object.
	 * 
	 * @return Model_Status The from status object.
	 */
	public function get_from_status() {
		$status = null;
		if( $this->from_status_id ) {
			$status = Model_Status::load( $this->from_status_id );
		}
		return $status;
	}

	/**
	 * Get to order status object.
	 * 
	 * @return Model_Status The to status object.
	 */
	public function get_to_status() {
		$status = null;
		if( $this->to_status_id ) {
			$status = Model_Status::load( $this->to_status_id );
		}
		return $status;
	}
	
	/**
	 * Get WooCommerce order object.
	 * 
	 * @return WC_Order The order object.
	 */
	public function get_order() {
		$order = null;
		if( $this->order_id ) {
			$order = wc_get_order( $this->order_id );
		}
		return $order;
	}

	/**
	 * Set specific property.
	 *
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch( $property ) {
			    case 'type':
			    	if( self::is_valid_type( $value ) ) {
			    		$this->$property = $value;
			    	}
			    	break;
			    case 'from_status_id':
			    case 'to_status_id':
			    	$this->$property = intval( $value );
			    	break;
				case 'event_dt':
				case 'trigger_dt':
				case 'sent_dt':
					$this->$property = sanitize_text_field( $value );
					break;

				case 'name':
				default:
					parent::__set( $property, $value );
					break;
			}
		}
		else {
			switch( $property ) {
				default:
					parent::__set( $property, $value );
					break;
			}
		}
	}

	/**
	 * Returns register custom post type args.
	 *
	 */
	public static function get_register_post_type_args() {
	    return [
	        'description' => 'WC Status Events',
	        'public' => false,
	        'show_ui' => false,
	        'show_in_menu' => false,
	        'has_archive' => false,
	        'publicly_queryable' => false,
	        'hierarchical' => false,
	    ];
	}
}