<?php
namespace WPPluginsDev\WooOrderWorkflow\Models;

use WPPluginsDev\Models\Model_Cpt;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\WooOrderWorkflow\Models\Model_EmailSettings;
use WPPluginsDev\WooOrderWorkflow\Models\Status\Model_Status_CoreDef;
use WPPluginsDev\WooOrderWorkflow\Models\Status\Model_Status_ManufactoryDef;
use WPPluginsDev\WooOrderWorkflow\Models\Status\Model_Status_FoodDeliveryDef;

/**
 * Order Status Model.
 */
class Model_Status extends Model_Cpt {
	
	const WC_ORDER_PREFIX = 'wc-';
	const TYPE_CORE = 'core';
	const TYPE_CUSTOM = 'custom';

	const IMPORT_CORE = 'core';
	const IMPORT_MANUFACTORY = 'manufactory';
	const IMPORT_FOOD_DELIVERY = 'food_delivery';
	const IMPORT_PACKAGE = 'package';
        
	public static $POST_TYPE = 'wd_wc_status';

	protected $id = 0;
	
	protected $enabled = false;
	
	protected $name;
	
	protected $slug;
	
	protected $description;
	
	protected $type = self::TYPE_CUSTOM;
	
	protected $days_estimation = 0;
	
	protected $order;
		
	protected $color = '#fff';

	protected $background = '#777';
	
	protected $icon = '';
	
	protected $next_statuses = [];
	
	protected $transitions;
	
	protected $enabled_in_bulk_actions;
	
	protected $enabled_in_reports;
	
	protected $is_paid;

	protected $email_settings;
	
	protected $trigger_settings = [];

	protected $orders_count = 0;

	protected $orders_link;
	
	/**
	 * Set properties after load from DB.
	 */
	public function after_load() {
		if( empty( $this->email_settings ) ) {
			$this->email_settings = new Model_EmailSettings();
		}
		if( ! $this->orders_link ) {
			$this->orders_link = admin_url( sprintf( 
				'edit.php?post_status=%s&post_type=shop_order', 
				$this->get_slug( true )
			) );
		}
	}

	/**
	 * Get order status types.
	 * 
	 * @return array The [slug => description] Order Statuses.
	 */
	public static function get_types() {
		return [
			self::TYPE_CORE => __( 'Core', 'wppdev-woocommerce-order-status' ),
			self::TYPE_CORE => __( 'Custom', 'wppdev-woocommerce-order-status' ),
		];
	}
	
	/**
	 * Verify if is a valid order status type.
	 * 
	 * @param string $type The order status to verify.
	 * @return bool If is a valid type.
	 */
	public static function is_valid_type( $type ) {
		$valid = false;
		if( array_key_exists( $type, self::get_types() ) ) {
			$valid = true;
		}
		return $valid;
	}
	
	/**
	 * Get order statuses.
	 * 
	 * @param array $args The args to filter.
	 * @param bool $to_array whether to return as array or object;
	 * @param bool $update_orders_count whether to update order status products count. 
	 */
	public static function get_statuses( $args = [], $to_array = false, $update_orders_count = false ) {
		
		$statuses =  self::get_core_statuses() + static::get_custom_statuses();
		
		if( $to_array ) {
			
			$array = [];
			foreach( $statuses as $status ) {
				if( $update_orders_count ) {
					$status->update_orders_count();
				}
				$array[] = $status->to_array();
			}
			$statuses = $array;
		}

		return $statuses;
	}
	
	/**
	 * Get custom order statuses.
	 * 
	 * @param array $args The filter args.
	 * @return Model_Status[] Order Status objects array.
	 */
	public static function get_custom_statuses( $args = [] ) {
		$args['meta_query'] = [
			[
				'key' => 'type',
				'value' => self::TYPE_CUSTOM,
			],
		];
		
		$statuses = static::load_models( $args );
		return $statuses;
	}
	
	/**
	 * Get core order statuses.
	 * 
	 * @param array $args The filter args.
	 * @return Model_Status[] Order Status objects array.
	 */
	public static function get_core_statuses( $args = [] ) {
		$args['meta_query'] = [
			[
				'key' => 'type',
				'value' => self::TYPE_CORE,
			],
		];
		
		$statuses = static::load_models( $args );
		return $statuses;
	}

	/**
	 * Get order statuses array.
	 * 
	 * @param array $args The filter args.
	 * @return array Slug => Order Status name array.
	 */
	public static function get_statuses_array( $args = [] ) {
		$statuses = [];
		$all_status = self::get_statuses( $args );
		
		foreach( $all_status as $status ) {
			$statuses[ $status->get_slug() ] = $status->name;
		}

		return $statuses;
	}
	
	/**
	 * Get core order status definitions.
	 */
	public static function get_core_statuses_definitions() {
		
		return Model_Status_CoreDef::get_statuses_definitions();
	}
	
	/**
	 * Get core order status definitions.
	 * 
	 * @return Model_Status[] The slug => order status object array.
	 */
	public static function load_core_statuses() {
		
		$statuses = self::get_core_statuses();
		if( empty( $statuses ) ) {
			$statuses = self::import_statuses( self::IMPORT_CORE );
		}		
		return $statuses;
	}

	/**
	 * Import statuses definitions.
	 * 
	 * @param string $import_id The import definition ID.
	 * @return Model_Status[] The imported slug => order status object array.
	 */
	public static function import_statuses( $import_id ) {
		$statuses = [];
		$loaded_statuses = self::get_statuses();
		$statuses_definitions = [];
		switch( $import_id ) {
			case self::IMPORT_MANUFACTORY:
				$statuses_definitions = Model_Status_ManufactoryDef::get_statuses_definitions();
				break;
			case self::IMPORT_CORE:
				$statuses_definitions = self::get_core_statuses_definitions();
				break;
			case self::IMPORT_FOOD_DELIVERY:
				$statuses_definitions = Model_Status_FoodDeliveryDef::get_statuses_definitions();
				break;
			
		}
		if( ! empty( $statuses_definitions ) ) {
			foreach( $statuses_definitions as $slug => $status ) {
				foreach( $loaded_statuses as $loaded_status ) {
					if( $loaded_status->get_slug() == $slug ) {
						$status['id'] = $loaded_status->id;
						break;
					}
				}
				$statuses[ $slug ] = self::import_definition( $slug, $status );
				$statuses[ $slug ]->save();
			}
	
			foreach( $statuses as $status ) {
				$status->load_next_statuses_ids();
				$status->register_status();
			}

			self::remove_unused_statuses( $statuses );
		}

		// Helper_Debug::debug($statuses);
		return $statuses;
	}

	/**
	 * Remove empty order statuses.
	 */
	public static function remove_unused_statuses( $current_statuses ) {

		$statuses = self::get_custom_statuses();

		foreach( $statuses as $status ) {
			if( ! array_key_exists( $status->slug, $current_statuses ) && 
				! array_key_exists( $status->id, $current_statuses ) ) {
				
				if( ! $status->update_orders_count() ) {
					$status->delete();
				}
			}
		}

	}

	/**
	 * Reset core statuses definitions.
	 * 
	 * @return Model_Status[] The imported slug => order status object array.
	 */
	public static function reset_core_statuses() {
		
		$statuses = [];
		$loaded_core_statuses = self::get_core_statuses();
		$core_statuses = self::get_core_statuses_definitions();
		
		foreach( $core_statuses as $slug => $status ) {
			foreach( $loaded_core_statuses as $loaded_status ) {
				if( $loaded_status->get_slug() == $slug ) {
					$status['id'] = $loaded_status->id;
					break;
				}
			}
			$statuses[ $slug ] = self::import_definition( $slug, $status );
		}

		return $statuses;
	}
	
	/**
	 * Import order status definition.
	 * 
	 * @param string $slug The order status slug.
	 * @param Model_Status The order status to copy from.
	 */
	public static function import_definition( $slug, $status ) {
		
		$copied_status = new static();
		foreach( $status as $field => $val ) {
			$copied_status->$field = $val;
		}
		$copied_status->slug = $slug;
		$copied_status->save();
		//Helper_Debug::debug($copied_status);
		
		return $copied_status;
	}
	
	/**
	 * Verify if is a core order status.
	 * 
	 * @return bool True if is a core order status.
	 */
	public function is_core_status() {
		$is_core = false;
		
		$core_statuses = self::get_core_statuses_definitions();
		if( isset( $core_statuses[ $this->slug ] ) ) {
			$is_core = true;
		}
		
		return $is_core;
	}

	/**
	 * Load next statuses Ids from slug.
	 */
	public function load_next_statuses_ids() {
		$next_statuses = [];
		$save = false;

		foreach( $this->next_statuses as $next_status ) {
			if( ! is_int( $next_status ) ) {
				$save = true;
				$status = self::get_by_slug( $next_status );
				if( $status && $status->id ) {
					$next_statuses[] = $status->id;
				}
			}
		}
		$this->next_statuses = $next_statuses;

		if( $save ) {
			$this->save();
		}
	}

	/**
	 * Get order status by slug.
	 * 
	 * @param string $slug The order status slug to find.
	 * @return null|Model_Status The found order status object.
	 */
	public static function get_by_slug( $slug ) {
		$found = null;
		$statuses = self::get_statuses();
		foreach( $statuses as $status ) {
			if( $status->slug == $slug ) {
				$found = $status;
				break;
			}
		}

		return $found;
	}
	
	/**
	 * Update orders count in current order status.
	 */
	public function update_orders_count() {
		$count = $this->orders_count;
		$this->orders_count = wc_orders_count( $this->get_slug() );

		if( $count != $this->orders_count ) {
			// Helper_Debug::debug( "SAVE STATUS COUNT: $this->orders_count, old: $count, $this->id, $this->slug" );
			$this->save();
		}

		return $this->orders_count;
	}

	/**
	 * Reassing all orders to other order status.
	 * 
	 * @param Model_Status $to_status The order status object to reassign to.
	 */
	public function reassign( $to_status ) {
		global $wpdb;

		$reassigned = false;
		if( ! empty( $to_status ) && $to_status->is_valid() ) {
			$update = $wpdb->get_results( $wpdb->prepare( "
					Update {$wpdb->posts}
					SET {$wpdb->posts}.post_status = %s
					WHERE post_type = 'shop_order'
					AND post_status IN ( %s )
					",
					$to_status->get_slug( true ),
					$this->get_slug( true )
				),
			);
			$reassigned = true;
		}

		return $reassigned;
	}

	/**
	 * Register custom order status in WP post status.
	 */
	public function register_status() {

    	$config = [
    		'public' => true,
    		'exclude_from_search' => false,
    		'show_in_admin_all_list' => true,
    		'show_in_admin_status_list' => true,
			'label' => $this->name,
			'label_count' => _n_noop( 
				$this->name . ' <span class="count">(%s)</span>', 
				$this->name . ' <span class="count">(%s)</span>', 
				'wppdev-woocommerce-order-status'
			),
    	];
		register_post_status( $this->get_slug( true ), $config );
    }
	
	/**
	 * Get order status slug.
	 * 
	 * @param bool $with_prefix Flag to add wc- prefix.
	 * @return string The order status slug.
	 */
	public function get_slug( $with_prefix = false ) {
		$slug = $this->slug;
		if( $with_prefix ) {
			$slug = self::WC_ORDER_PREFIX . $slug;
		}
		
		return $slug;
	}

	/**
	 * Set order status slug.
	 * 
	 * @param string $slug The slug to set.
	 */
	public function set_slug( $slug ) {
		$max = 20;
		$slug = sanitize_title( $slug );
		if( substr( $slug, 0, 3 ) == self::WC_ORDER_PREFIX ) {
			$slug = str_replace( self::WC_ORDER_PREFIX, '', $slug );
		}
		if( strlen( $slug ) > $max ) {
			$slug = substr( $slug, 0, $max );
		}
		$this->slug = $slug;
	}
	
	/**
	 * Get specific property.
	 * 
	 * @param string $property the property to get value.
	 * @return mixed The property's value.
	 */
	public function __get( $property ) {
	    
	    $value = null;
	    
	    if ( property_exists( $this, $property ) ) {
	        switch( $property ) {
	            case 'slug':
	                $value = $this->get_slug();
	                break;
	            case 'enabled':
	            	$value = $this->enabled;
	            	if( $this->is_core_status() ) {
	            		$value = true;
	            	}
	            	break;
	            case 'type':
	            	$value = self::TYPE_CUSTOM;
	            	if( $this->is_core_status() ) {
	            		$value = self::TYPE_CORE;
	            	}
	            	break;
	            default:
	                $value = parent::__get( $property );
	                break;
	        }
	    }
	    
	    return $value;
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
			    case 'enabled':
			    case 'enabled_in_bulk_action':
			    case 'enabled_in_reports':
			    case 'is_paid':
			        $this->$property = boolval( $value );
			        break;
			    case 'slug':
			    	$this->set_slug( $value );
			    	break;
			    case 'type':
			    	if( self::is_valid_type( $value ) ) {
			    		$this->$property = $value;
			    	}
			    	break;
			    case 'days_estimation':
			    case 'order':
			    	$this->$property = intval( $value );
			    	break;
				case 'color':
				case 'icon':
					$this->$property = sanitize_text_field( $value );
					break;
				case 'transitions':
					$set = false;
					if( is_array( $value ) ) {
						$set = true;
						foreach( $value as $v ) {
							if( ! $v instanceof static ) {
								$set = false;
							}
						}
					}
					if( $set ) {
						$this->$property = $value;
					}
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
	}

	/**
	 * Returns register custom post type args.
	 *
	 */
	public static function get_register_post_type_args() {
	    return [
	        'description' => 'WC Status',
	        'public' => false,
	        'show_ui' => true,
	        'show_in_menu' => false,
	        'has_archive' => false,
	        'publicly_queryable' => false,
	        'supports' => array( 'title', 'editor', 'author' ),
	        'hierarchical' => false,
	        'rewrite' => array( 'slug' => 'wd_wc_status' ),
	        'labels' => array( 'name' => 'WC Status'),
	    ];
	}
}