<?php
namespace WPPluginsDev\Models;
use WPPluginsDev\Models\Model_Cpt;
use WPPluginsDev\Factory;
use WPPluginsDev\Helpers\Helper_Debug;

class Model_Woo extends Model_Cpt {

	public static $POST_TYPE = 'product';
	public $post_type = 'product';
	
	public static $SAVE_IN_MAIN_SITE = false;

	protected $_sku = null;
	
	protected $_downloadable  = 'no';

	protected $_virtual = 'no';
	
	protected $_price;
	
	protected $_visibility = 'visible';
	
	protected $_stock;
	
	protected $_stock_status = 'instock';
	
	protected $_backorders = 'no';
	
	protected $_manage_stock = 'no';
	
	protected $_sale_price;
	
	protected $_regular_price;
	
	protected $_weight;
	
	protected $_length;
	
	protected $_width;
	
	protected $_height;
	
	protected $_tax_status = 'taxable';
	
	protected $_tax_class;
	
	protected $_upsell_ids = array();
	
	protected $_crosssell_ids = array();
	
	protected $_sale_price_dates_from;
	
	protected $_sale_price_dates_to;
	
	protected $_min_variation_price;
	
	protected $_max_variation_price;
	
	protected $_min_variation_regular_price;
	
	protected $_max_variation_regular_price;
	
	protected $_min_variation_sale_price;
	
	protected $_max_variation_sale_price;
	
	protected $_featured  = 'no';
	
	protected $_file_path;
	
	protected $_download_limit;
	
	protected $_download_expiry;
	
	protected $_product_url;
	
	protected $_button_text;
	
	protected $_thumbnail_id;
	
	protected $_product_image_gallery;
	
	protected $_downloadable_files;
	
	protected $_download_type;
	
	public static function load_products( $args = null, $only_ids = false ) {
	    $defaults = array(
	        'post_type' => static::$POST_TYPE,
	        'posts_per_page' => -1,
	        'post_status' => 'any',
	        'order' => 'DESC',
	        'orderby' => 'ID', 
	    );
	    
	    $args = wp_parse_args( $args, $defaults ) ;
	    
	    if( $only_ids ) {
	        $args['fields'] = array( 'ids' );
	    }
	    
	    $query = new \WP_Query( $args );
	    $query->set( 'lang', null );
	    
// 	    Helper_Debug::log($args);
	    
	    $items = $query->get_posts();
	    $products = array();
	    
	    if( ! empty( $items ) ) {
	        foreach( $items as $p ) {
	            if( $only_ids ) {
	                $products[] = $p->ID;
	            }
	            else {
	                $products[] = static::load( $p->ID );
	            }
	        }
	    }

	    return apply_filters( 'model_woo_load_producs', $products );
	}
	
	public static function load_products_names() {
	    $names = [];
	    $products = static::load_products();
	    foreach( $products as $product ) {
	        $names[ $product->id ] = $product->post_title;
	    }
	    	    
	    return $names;
	}

	public static function load_by_sku( $sku ) {
		$args = array(
				'post_type' => static::$POST_TYPE,
				'posts_per_page' => 1,
				'meta_query' => array(
						array(
								'key'     => '_sku',
								'value'   => $sku,
						),
				)
		);
		
		$query = new WP_Query( $args );
		
		$item = $query->get_posts();
		$product = null;
		
		if( ! empty( $item[0] ) ) {
			$product = Factory::load( 'Model_Woo', $item[0]->ID );
		}
		
		return apply_filters( 'model_woo_load_by_sku', $product , $sku );
	}
	
	/**
	 * Set specific property.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch( $property ) {
				case '_sku':
				case '_downloadable':
				case '_virtual':
				case '_stock':
				case '_backorders':
				case '_manage_stock':
				case '_featured':
					if( in_array( $value, array( 'yes', 'no' ) ) ) {
						$this->$property = $value;
					}
					break;
				case '_price':
				case '_sale_price':
				case '_regular_price':
				case '_min_variation_price':
				case '_max_variation_price':
				case '_min_variation_regular_price':
				case '_max_variation_regular_price':
				case '_min_variation_sale_price':
				case '_max_variation_sale_price':
					$this->$property = floatval( $value );
					break;
				case '_visibility':
				case '_stock_status':
				case '_tax_status':
				case '_tax_class':
					$this->$property = sanitize_text_field( $value );
					break;
				case '_weight':
				case '_length':
				case '_width':
				case '_height':
					$this->$property = floatval( $value );
					break;
				case '_upsell_ids': //array
				case '_crosssell_ids':
					if( is_array( $value ) ) {
						$this->property = $value;
					}
					break;
				case '_download_limit':
				case '_thumbnail_id':
					$this->$property = intval( $value );
					break;
				case '_sale_price_dates_from':
				case '_sale_price_dates_to':
				case '_file_path':
				case '_download_expiry':
				case '_product_url':
				case '_button_text':
					$this->$property = sanitize_text_field( $value );
					break;
				case '_product_image_gallery':
					$this->$property = sanitize_text_field( $value );
					break;
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

		do_action( 'model_woo_set_after', $property, $value, $this );
	}

	/**
	 * Returns register custom post type args.
	 *
	 * @since 1.0.0
	 */
	public static function get_register_post_type_args() {
		return array();
	}
}