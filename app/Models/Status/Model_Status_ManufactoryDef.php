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
				'description' => __( 'Payment received (paid) and stock has been reduced; order is awaiting fulfillment.', WPPDEV_WO_TXT_DM ),
				'color' => '#5b841b',
				'background' => '#c6e1c6',
				'icon' => 'FaEllipsisH',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CORE,
			],
			'manufactoring' => [
				'name' =>  __( 'Manufactoring', WPPDEV_WO_TXT_DM ),
				'next_statuses' => ['shipped'],
				'description' => __( 'Manufactoring the product.', WPPDEV_WO_TXT_DM ),
				'color' => '#976565',
				'background' => '#e8e3e3',
				'icon' => 'FaTools',
				'enabled_in_bulk_actions' => true,
				'enabled_in_reports' => true,
				'type' => self::TYPE_CUSTOM,
			],
			'shipped' => [
				'name' =>  __( 'Shipped', WPPDEV_WO_TXT_DM ),
				'next_statuses' => ['completed'],
				'description' => __( 'Shipped to address.', WPPDEV_WO_TXT_DM ),
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