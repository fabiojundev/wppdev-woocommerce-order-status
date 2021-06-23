<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers;

use WPPluginsDev\WooOrderWorkflow\Controllers\Controller;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Cron;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
use WPPluginsDev\WooOrderWorkflow\Models\Status\Model_Status_Event;
use WPPluginsDev\Helpers\Helper_Period;

/**
 * Events controller.
 */
class Controller_Event extends Controller {

	public function __construct() {
		$this->add_action( 'woocommerce_order_status_changed', 'save_events', 10, 4 );

		$this->add_action( Controller_Cron::CRON_PROCESS_EVENTS, 'verify_events' );

		// $this->verify_events();
    }

	/**
	 * Save order status change events.
	 * 
	 * @param int $order_id The order ID.
	 * @param string $from_status The order status from which changed.
	 * @param string $to_status The order status that changed to.
	 * @param WC_Order $order The woocommerce order object.
	 */
	public function save_events( $order_id, $from_status, $to_status, $order ) {
		$from_status = Model_Status::load_by_field( 'slug', $from_status );
		$to_status = Model_Status::load_by_field( 'slug', $to_status );
		
		$event = Model_Status_Event::create_event( $order_id, $from_status, $to_status );
		// Helper_Debug::debug($event);
	}

	/**
	 * Verify events to trigger actions.
	 */
	public function verify_events() {
		$events = Model_Status_Event::get_events([
			'trigger_dt' => '',
		]);
		// Helper_Debug::debug( $events);
		foreach( $events as $event ) {
			if( ! $event->trigger_dt ) {
				$this->verify_triggers( $event );
			}
		}
	}

	/**
	 * Verify triggers to fire actions.
	 */
	public function verify_triggers( $event ) {

		// Helper_Debug::debug("trigger_actions");
		$statuses = Model_Status::get_statuses();
		foreach( $statuses as $status ) {
			foreach( $status->trigger_settings as $trigger ) {
				// Helper_Debug::debug("[$status->slug] Verify Trigger: $trigger->trigger_type ");
				if( $trigger->verify_event_condition( $event, $status ) ) {
					// Helper_Debug::debug("Event Triggered: $event->id, TRIGGER $status->slug, id: $status->id  type: $trigger->trigger_type -- FROM: {$event->get_from_status()->slug}, to: {$event->get_to_status()->slug}, order_id: $event->order_id");
					
					$trigger->trigger_actions( $event );

					$event->trigger_dt = Helper_Period::current_date();
					$event->save();	
					// Helper_Debug::debug($event);
				}
			}
		}
	}
}