<?php
namespace WPPluginsDev\WooOrderWorkflow\Models\Status;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;

/**
 * WooCommerce Food Delivery statuses definitions to import.
 */
class Model_Status_FoodDeliveryDef extends Model_Status {
	
	public static function get_statuses_definitions() {
		
		$statuses = static::get_core_statuses_definitions();

		$statuses = [
			'processing' => [
				'name' => _x( 'Processing', 'Order status', 'woocommerce' ),
				'next_statuses' => ['delivering'],
				'description' => __( 'Payment received (paid) and stock has been reduced; order is awaiting fulfillment.', 'wppdev-woocommerce-order-status' ),
				'color' => '#5b841b',
				'background' => '#c6e1c6',
				'icon' => 'FaEllipsisH',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'delivering' => [
				'name' =>  __( 'Delivering', 'wppdev-woocommerce-order-status' ),
				'next_statuses' => ['completed'],
				'description' => __( 'Shipped to address.', 'wppdev-woocommerce-order-status' ),
				'color' => '#f3f3f7',
				'background' => '#282cc0',
				'icon' => 'FaShippingFast',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CUSTOM,
			],
		] + $statuses;

		// Helper_Debug::debug($statuses);
		return $statuses;
	}
}