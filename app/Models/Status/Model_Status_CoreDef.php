<?php
namespace WPPluginsDev\WooOrderWorkflow\Models\Status;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;

/**
 * WooCommerce core statuses definitions to import.
 */
class Model_Status_CoreDef extends Model_Status {
	
	public static function get_statuses_definitions() {
		
		return array(
			'pending'    => [
				'name' =>	_x( 'Pending payment', 'Order status', 'woocommerce' ),
				'next_statuses' => [ 'on-hold', 'failed', 'processing' ],
				'description' => __( 'Order received, no payment initiated. Awaiting payment (unpaid).', 'wppdev-woocommerce-order-status' ),
				'color' => '#777777',
				'background' => '#e5e5e5',
				'icon' => 'FaCreativeCommonsNc',
				'enabled_in_bulk_actions' => false,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'processing' => [
				'name' => _x( 'Processing', 'Order status', 'woocommerce' ),
				'next_statuses' => [ 'completed' ],
				'description' => __( 'Payment received (paid) and stock has been reduced; order is awaiting fulfillment.', 'wppdev-woocommerce-order-status' ),
				'color' => '#5b841b',
				'background' => '#c6e1c6',
				'icon' => 'FaEllipsisH',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'on-hold' => [
				'name' => _x( 'On hold', 'Order status', 'woocommerce' ),
				'next_statuses' => [ 'processing', 'failed' ],
				'description' => __( 'Awaiting payment – stock is reduced, but you need to confirm payment.', 'wppdev-woocommerce-order-status' ),
				'color' => '#94660c',
				'background' => '#f8dda7',
				'icon' => 'FaRegClock',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'completed' => [
				'name' => _x( 'Completed', 'Order status', 'woocommerce' ),
				'next_statuses' => [ 'refunded' ],
				'description' => __( 'Order fulfilled and complete – requires no further action.', 'wppdev-woocommerce-order-status' ),
				'color' => '#2e4453',
				'background' => '#c8d7e1',
				'icon' => 'FaCheck',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'cancelled' => [
				'name' =>  _x( 'Cancelled', 'Order status', 'woocommerce' ),
				'next_statuses' => [],
				'description' => __( 'Canceled by an admin or the customer – stock is increased, no further action required.', 'wppdev-woocommerce-order-status' ),
				'color' => '#777777',
				'background' => '#e5e5e5',
				'icon' => 'FaRegTimesCircle',
				'enabled_in_bulk_actions' => false,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'refunded' => [
				'name' =>  _x( 'Refunded', 'Order status', 'woocommerce' ),
				'next_statuses' => [],
				'description' => __( 'Refunded by an admin – no further action required.', 'wppdev-woocommerce-order-status' ),
				'color' => '#777777',
				'background' => '#e5e5e5',
				'icon' => 'FaShare',
				'enabled_in_bulk_actions' => false,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'failed' => [
				'name' =>  _x( 'Failed', 'Order status', 'woocommerce' ),
				'next_statuses' => [ 'cancelled' ],
				'description' => __( 'Payment failed or was declined (unpaid) or requires authentication (SCA). Note that this status may not show immediately and instead show as Pending until verified.', 'wppdev-woocommerce-order-status' ),
				'color' => '#761919',
				'background' => '#eba3a3',
				'icon' => 'FaExclamationTriangle',
				'enabled_in_bulk_actions' => false,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
		);
	}
}