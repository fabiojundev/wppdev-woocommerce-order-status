<?php
namespace WPPluginsDev\WooOrderWorkflow\Models\Status;

use WPPluginsDev\Models\Model;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
use WPPluginsDev\Helpers\Helper_Debug;

/**
 * Model Status Product.
 * Products info in specific order status.
 */
class Model_Status_Product extends Model {
    
	protected $status_id;

	protected $product_id;

	protected $product_name;

	protected $product_edit_link;
	
	protected $quantity;

	protected $in_stock;

	protected $order_ids;
	
	protected $order_item_ids;
	
	public function __construct( $product_id, $status_id ) {
		$this->status_id = $status_id;
		$this->product_id = $product_id;
		$this->product_edit_link = get_edit_post_link( $product_id, 'edit' );
		$product = $this->get_product();
		if( $product ) {
			$this->product_name = $product->get_name();
			$this->in_stock = $product->get_stock_quantity();	
		}
		$this->find_orders_ids();
		$this->count_product_in_orders();
	}
	
	/**
	 * Get Order Status products.
	 * 
	 * @param int $status_id The order status Id to get products.
	 * @return Model_Status_Product[] The products found for the order status.
	 */
	public static function get_status_products( $status_id ) {
		global $wpdb;

		$products = [];
		$product_ids = [];

		$status = Model_Status::load( $status_id );
		if( $status->is_valid() ) {

			$product_ids = $wpdb->get_col( $wpdb->prepare( "
					SELECT DISTINCT order_item_meta.meta_value
					FROM {$wpdb->prefix}woocommerce_order_items as order_items
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
					LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
					WHERE posts.post_type = 'shop_order'
					AND posts.post_status IN ( %s )
					AND order_items.order_item_type = 'line_item'
					AND order_item_meta.meta_key = '_product_id'
				",
				$status->get_slug( true ),
				),
			);
		}

		foreach( $product_ids as $product_id ) {
			$product = new static( $product_id, $status_id );
			if( $product->quantity ) {
				$products[] = $product;
			}
		}
		return $products;
	}
	
	/**
	 * Find order ids with this order status.
	 */
	public function find_orders_ids() {
		global $wpdb;
		
		$status = $this->get_status()->get_slug( true );
		$orders = $wpdb->get_results( $wpdb->prepare( "
		        SELECT order_items.order_id, order_items.order_item_id
		        FROM {$wpdb->prefix}woocommerce_order_items as order_items
		        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
		        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
		        WHERE posts.post_type = 'shop_order'
		        AND posts.post_status IN ( %s )
		        AND order_items.order_item_type = 'line_item'
		        AND order_item_meta.meta_key = '_product_id'
		        AND order_item_meta.meta_value = %s
    		",
    		$status,
    		$this->product_id
		),
		);
		$this->order_ids = [];
		$this->order_item_ids = [];
		foreach ( $orders as $order ) {
			$order_id = intval( $order->order_id );
			$this->order_ids[] = [
				'order_id' => $order_id,
				'order_edit_link' => get_edit_post_link( $order_id, 'edit' ),
			];
			$this->order_item_ids[] = $order->order_item_id;
			// Helper_Debug::debug(get_post( $order_id));
		}
	}
	
	/**
	 * Count the products in orders.
	 */
	public function count_product_in_orders() {
		global $wpdb;
		
		$this->quantity = 0;
		$order_id_items = self::esc_implode_sql( $this->order_item_ids );
		if( ! empty( $order_id_items ) ) {

			$sql = $wpdb->prepare( "
				SELECT SUM( order_item_meta.meta_value )
				FROM {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta
				WHERE order_item_meta.order_item_id IN ( $order_id_items )
				AND order_item_meta.meta_key = '_qty'
				"
			);
			$this->quantity = $wpdb->get_var( $sql );
		}
	}

	/**
	 * Escape and implode array.
	 * 
	 * @param array The key => value to implode and scape.
	 */
	public static function esc_implode_sql( $arr ) {
		global $wpdb;
		$escaped = array();
		foreach ( $arr as $k => $v ) {
			if ( is_numeric( $v ) )
				$escaped[] = $wpdb->prepare( '%d', $v );
			else
				$escaped[] = $wpdb->prepare( '%s', $v );
		}
		return implode( ',', $escaped );
	}

	/**
	 * Get WooCommerce product.
	 * 
	 * @return WC_Product The WooCommerce Product object.
	 */
	public function get_product() {
		return wc_get_product( $this->product_id );
	}

	/**
	 * Get Order Status.
	 * 
	 * @return Model_Status The order status object.
	 */
	public function get_status() {
		return Model_Status::load( $this->status_id );		
	}
}