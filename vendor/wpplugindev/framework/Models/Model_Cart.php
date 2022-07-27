<?php
namespace WPPluginsDev\Models;
use WPPluginsDev\Models\Model;

class Model_Cart extends Model {

	public static function get_woo_cart() {
	    return WC()->cart;
	}
	
	public static function get_cart_contents() {
	    return WC()->cart->get_cart();
	}
	
	public static function add_to_cart( $product, $qty, $desc = [] ) {
	    $cart = static::get_woo_cart();
	    $cart_item_key = static::find_in_cart( $product );
	    $product_id = $product->get_id();
	    $product_variation_id = 0;

	    if( $product->get_parent_id() ) {
	        $product_id = $product->get_parent_id();
	        $product_variation_id = $product->get_id();
	    }
	    
	    $product_variation_id = $product->get_id();

	    if ( empty( $cart_item_key ) ) {
	        
	        $cart->add_to_cart(
	            $product_id,
	            $qty,
	            $product_variation_id,
	            $desc
	        );
	    }
	}

	public static function remove_from_cart( $product ) {
	    $cart_item_key = static::find_in_cart( $product );
	    if( $cart_item_key ) {
    	    $cart = static::get_woo_cart();
    	    $cart->remove_cart_item( $cart_item_key );
	    }
	}
	
	public static function find_in_cart( $product ) {
	    $cart_item_key = null;
	    $cart = static::get_woo_cart();
	    
	    foreach ( $cart->get_cart() as $key => $item ) {
	        $prod = $item['data'];
	        if( $prod->get_id() == $product->get_id() ) {
	            $cart_item_key = $key;
	            break;
	        }
	    }
	    
	    return $cart_item_key;
	}
}