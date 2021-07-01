<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers;

use WPPluginsDev\WooOrderWorkflow\Controllers\Controller;
use WPPluginsDev\Helpers\Helper_Debug;

/**
 * Cron Controller.
 */
class Controller_Cron extends Controller {

	const CRON_PROCESS_EVENTS = 'wo_cron_process_events';
	/**
	 * Cron Schedule events.
	 *
	 * @since 1.0.0
	 *
	 */
	protected $events;
	
	public function __construct() {
		parent::__construct();
		
		$this->setup_cron_services();
		$this->add_filter( 'cron_schedules', 'cron_time_period' );
		register_deactivation_hook( __FILE__, 'deactivate_cron' );
	}

	/**
	 * Setup cron plugin services.
	 *
	 * Setup cron to call actions.
	 *
	 * @since 1.0.0
	 */
	public function setup_cron_services() {

		$this->events = array(
				self::CRON_PROCESS_EVENTS => '1day',
		);

		foreach ( $this->events as $event => $interval ) {
			if ( ! wp_next_scheduled( $event ) ) {
				wp_schedule_event( time(), $interval, $event );
			}
		}
	}
	
	/**
	 * Clear cron schedules.
	 *
	 * @since 1.0.0
	 */
	public function deactivate_cron() {
		
		foreach ( $this->events as $event => $interval ) {
			wp_clear_scheduled_hook( $event );
		}
	}

	/**
	* Config cron time period.
	*
	* Related Action Hooks:
	* - cron_schedules
	*
	* @since 1.0.0
	*/
	public function cron_time_period( $periods ) {
		if ( ! is_array( $periods ) ) {
			$periods = array();
		}
		
		$periods['1day'] = array(
				'interval' => DAY_IN_SECONDS,
				'display' => __( 'Every Day', 'wppdev-woocommerce-order-status' )
		);
		$periods['12hours'] = array(
				'interval' => 12 * HOUR_IN_SECONDS,
				'display' => __( 'Every 12 Hours', 'wppdev-woocommerce-order-status' )
		);
		$periods['6hours'] = array(
				'interval' => 6 * HOUR_IN_SECONDS,
				'display' => __( 'Every 6 Hours', 'wppdev-woocommerce-order-status' )
		);
		$periods['60mins'] = array(
				'interval' => 60 * MINUTE_IN_SECONDS,
				'display' => __( 'Every 60 Mins', 'wppdev-woocommerce-order-status' )
		);
		$periods['30mins'] = array(
				'interval' => 30 * MINUTE_IN_SECONDS,
				'display' => __( 'Every 30 Mins', 'wppdev-woocommerce-order-status' )
		);
		$periods['15mins'] = array(
				'interval' => 15 * MINUTE_IN_SECONDS,
				'display' => __( 'Every 15 Mins', 'wppdev-woocommerce-order-status' )
		);
		$periods['10mins'] = array(
				'interval' => 10 * MINUTE_IN_SECONDS,
				'display' => __( 'Every 10 Mins', 'wppdev-woocommerce-order-status' )
		);
		$periods['5mins'] = array(
				'interval' => 5 * MINUTE_IN_SECONDS,
				'display' => __( 'Every 5 Mins', 'wppdev-woocommerce-order-status' )
		);
		$periods['1min'] = array(
				'interval' => MINUTE_IN_SECONDS,
				'display' => __( 'Every Minute', 'wppdev-woocommerce-order-status' )
		);
		
		return apply_filters( 'wo_controller_cron_time_period', $periods );
	}
}
