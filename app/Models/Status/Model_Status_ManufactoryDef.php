<?php
namespace WPPluginsDev\WooOrderWorkflow\Models\Status;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;

/**
 * WooCommerce Manufatory statuses definitions to import.
 */
class Model_Status_ManufactoryDef extends Model_Status {
	
	public static function get_statuses_definitions() {
		
		$statuses = static::get_core_statuses_definitions();

		$statuses = [
			'processing' => [
				'name' => _x( 'Processing', 'Order status', 'woocommerce' ),
				'next_statuses' => ['manufactoring'],
				'description' => __( 'Payment received (paid) and stock has been reduced; order is awaiting fulfillment.', 'wppdev-woocommerce-order-status' ),
				'color' => '#5b841b',
				'background' => '#c6e1c6',
				'icon' => 'FaEllipsisH',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'manufactoring' => [
				'name' =>  __( 'Manufactoring', 'wppdev-woocommerce-order-status' ),
				'next_statuses' => ['shipped'],
				'description' => __( 'Manufactoring the product.', 'wppdev-woocommerce-order-status' ),
				'color' => '#976565',
				'background' => '#e8e3e3',
				'icon' => 'FaTools',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CUSTOM,
			],
			'shipped' => [
				'name' =>  __( 'Shipped', 'wppdev-woocommerce-order-status' ),
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