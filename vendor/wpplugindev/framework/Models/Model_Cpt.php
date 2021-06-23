<?php
namespace WPPluginsDev\Models;
use WPPluginsDev\Models\Model_Term;
use WPPluginsDev\Models\Model_User;
use WPPluginsDev\Helpers\Helper_Util;
use WPPluginsDev\Helpers\Helper_Debug;

/**
 * Custom Post Type Abstract Model.
 *
 * @since 1.0.0
 */
class Model_Cpt extends Model {
	const MAIN_SITE_ID = 1;
	
	/**
	 * Model custom post type.
	 *
	 * Both static and class property are used to handle php 5.2 limitations.
	 * Override this value in child object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $POST_TYPE;
	
	/**
	 * Post status constants.
	 *
	 * @since 1.0.0
	 *       
	 * @see $post_status property.
	 * @var string
	 */
	const POST_STATUS_PUBLISH = 'publish';
	const POST_STATUS_FUTURE = 'future';
	const POST_STATUS_DRAFT = 'draft';
	const POST_STATUS_PENDING = 'pending';
	const POST_STATUS_PRIVATE = 'private';
	const POST_STATUS_TRASH = 'trash';
	const POST_STATUS_AUTO_DRAFT = 'auto-draft';
	const POST_STATUS_INHERIT = 'inherit';
	
	/**
	 * Post comment and ping status constants.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	const STATUS_OPEN = 'open';
	const STATUS_CLOSED = 'closed';
	
	/**
	 * Flag to save in the main site of a multisite.
	 *
	 * Uses switch_to_blog to save in the main posts table.
	 *
	 * @since 1.0.0
	 *       
	 * @var int
	 */
	public static $SAVE_IN_MAIN_SITE = false;
	
	/**
	 * ID of the model object.
	 *
	 * Saved as WP post ID.
	 *
	 * @since 1.0.0
	 *       
	 * @var int
	 */
	protected $id;
	
	/**
	 * Model name.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $post_name;
	
	/**
	 * Model title.
	 *
	 * Saved in $post->post_title.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $post_title;
	
	/**
	 * Model description.
	 *
	 * Saved in $post->post_content and $post->excerpt.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $description;
	
	/**
	 * Model excerpt.
	 *
	 * Saved in $post->excerpt.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $excerpt;
	
	/**
	 * The user ID of the owner.
	 *
	 * Saved in $post->post_author
	 *
	 * @since 1.0.0
	 *       
	 * @var int
	 */
	protected $user_id;
	
	/**
	 * The parent ID of this model.
	 *
	 * Saved in $post->post_parent
	 *
	 * @since 1.0.0
	 *       
	 * @var int
	 */
	protected $parent_id;
	
	/**
	 * The post created date.
	 *
	 * Saved in $post->post_date
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $post_date;
	
	/**
	 * The last modified date.
	 *
	 * Saved in $post->post_modified
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $post_modified;
	
	/**
	 * The post status field.
	 *
	 * Saved in $post->post_status
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $post_status = 'private';
	
	/**
	 * The comment status field.
	 *
	 * Saved in $post->comment_status
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $comment_status = 'closed';
	protected $post_password = null;
	
	/**
	 * Not persisted fields.
	 *
	 * @since 1.0.0
	 *       
	 * @var string[]
	 */
	public $ignore_fields = array( 'actions', 'filters', 'ignore_fields', 'post_type', 'protected_fields' );

	public function before_save() {
		parent::before_save();
		
		self::switch_to_main_site();
	}

	/**
	 * Save content in wp tables (wp_post and wp_postmeta).
	 *
	 * Update WP cache.
	 *
	 * @since 1.0.0
	 *       
	 * @var string[]
	 */
	public function save() {
		$this->before_save();
		
		$this->post_modified = gmdate( 'Y-m-d H:i:s' );
		
		$class = get_class( $this );
		$post = array( 
				'comment_status' => $this->comment_status, 
				'ping_status' => 'closed', 
				'post_author' => $this->user_id, 
				'post_content' => $this->description ? $this->description : $this->description = ' ', 
				'post_excerpt' => !empty( $this->excerpt ) ? $this->excerpt : $this->description, 
				'post_name' => sanitize_title( $this->post_name ), 
				'post_status' => $this->post_status, 
				'post_title' => sanitize_text_field( !empty( $this->post_title ) ? $this->post_title : $this->post_name ), 
				'post_type' => $this->get_post_type(), 
				'post_parent' => $this->parent_id, 
				'post_date' => $this->post_date,
				'post_modified' => $this->post_modified,
				'post_password' => $this->post_password );
		
		if ( empty( $this->id ) ) {
			$this->id = wp_insert_post( $post );
		}
		else {
			$post[ 'ID' ] = $this->id;
			wp_update_post( $post );
		}
		
		// save attributes in postmeta table
		$post_meta = get_post_meta( $this->id );
		
		$fields = get_object_vars( $this );
		foreach ( $fields as $field => $val ) {
			if ( in_array( $field, $this->ignore_fields ) ) {
				continue;
			}
			if ( isset( $this->$field ) && ( !isset( $post_meta[ $field ][ 0 ] ) || $post_meta[ $field ][ 0 ] != $this->$field ) ) {
				update_post_meta( $this->id, $field, $this->$field );
			}
		}
		
		wp_cache_set( $this->id, $this, $class );
		
		$this->save_terms();
		
		$this->after_save();
	}

	/**
	 * Method called after object save.
	 * 
	 * @since 1.0.0
	 * 
	 * {@inheritDoc}
	 * @see Model::after_save()
	 */
	public function after_save() {
		parent::after_save();
		
		self::restore_current_blog();
	}

	/**
	 * Method called before object load.
	 * 
	 * @since 1.0.0
	 * 
	 * {@inheritDoc}
	 * @see Model::before_load()
	 */
	public function before_load() {
		parent::before_load();
		
		self::switch_to_main_site();
	}
	
	/**
	 * Load Custom_Post_Type Objects.
	 *
	 * Load from post and postmeta.
	 *
	 * @since 1.0.0
	 *
	 * @param Custom_Post_Type $model The empty model instance.
	 * @param int $model_id The model id to retrieve.
	 *
	 * @return Custom_Post_Type The retrieved object.
	 */
	public static function load( $model_id = 0 ) {
		$model = new static();
		$class = get_class( $model );
		
		$model->before_load();
		
		if ( ! empty( $model_id ) ) {
			$cache = wp_cache_get( $model_id, $class );
			
			if ( $cache ) {
				$model = $cache;
//				Helper_Debug::debug("---------from cache, $class");
//				Helper_Debug::debug( $model);
			}
			else {
				$model->before_load();
//				Helper_Debug::debug("---------from DB, $class");
//				Helper_Debug::debug( $model);

				$post = get_post( $model_id );
				
				if ( ! empty( $post ) && $model->get_post_type() == $post->post_type ) {
					$post_meta = get_post_meta( $model_id );
					$fields = $model->get_object_vars();
					
					foreach ( $fields as $field => $val ) {
						if ( in_array( $field, $model->ignore_fields ) ) {
							continue;
						}
						
						if ( isset( $post_meta[ $field ][ 0 ] ) ) {
							$model->set_field(
									$field,
									maybe_unserialize( $post_meta[ $field ][ 0 ] )
									);
						}
					}
					
					$model->id = $post->ID;
					$model->description = $post->post_content;
					$model->user_id = $post->post_author;
					$model->post_title = $post->post_title;
					$model->post_status = $post->post_status;
					$model->post_name = $post->post_name;
					$model->excerpt = $post->post_excerpt;
					$model->post_date = $post->post_date;
					$model->post_modified = $post->post_modified;
					$model->parent_id = $post->post_parent;
				}
				
				$model->after_load();
			}
		}
		
		$model->after_load();
		
		return apply_filters(
				'wd_model_cpt_load',
				$model,
				$class,
				$model_id
				);
	}

	/**
	 * Method called after object load.
	 * 
	 * @since 1.0.0
	 * 
	 * {@inheritDoc}
	 * @see Model::after_load()
	 */
	public function after_load() {
		parent::after_load();
		
		self::restore_current_blog();
	}

	/**
	 * Load model by field.
	 */
	public static function load_by_field( $field, $value ) {
		$field = sanitize_text_field( $field );
		$value = sanitize_text_field( $value );
		$args = array(
				'post_type' => static::$POST_TYPE,
				'posts_per_page' => 1,
				'post_status' => 'any',
				'meta_query' => array(
						array(
								'key' => $field,
								'value' => $value,
						),
				)
		);
		
		$query = new \WP_Query( $args );
		
		$item = $query->get_posts();
		$model = null;
		
		if( ! empty( $item[0] ) ) {
			$model = static::load( $item[0]->ID );
		}
		
		return $model;
	}

	public static function get_count( $args = null ) {

		$defaults = array(
				'post_type' => static::$POST_TYPE,
				'post_status' => 'any',
		);
		$args = wp_parse_args( $args, $defaults );

		$query = new \WP_Query( $args );

		return $query->found_posts;
	}

	public static function load_models( $args = null, $only_ids = false ) {
	    $defaults = array(
	        'post_type' => static::$POST_TYPE,
			'posts_per_page' => -1,
			'post_status' => 'any',
	    );
	    $args = wp_parse_args( $args, $defaults );
	    
	    if( $only_ids ) {
	        $args['fields'] = array( 'ids' );
	    }
	    
	    $query = new \WP_Query( $args );
	    
	    $items = $query->get_posts();
	    $models = array();
	    
	    if( ! empty( $items ) ) {
	        foreach( $items as $p ) {
	            if( $only_ids ) {
	                $models[ $p->ID ] = $p->ID;
	            }
	            else {
	                $models[ $p->ID ] = static::load( $p->ID );
	            }
	        }
	    }
	    
	    return $models;
	}

	/**
	 * Return array of taxonomies => args.
	 * 
	 * @since 1.0.0
	 */
	public function get_taxonomies() {
		return array( "Method not overriden." );
	}

	/**
	 * Get CPT associated terms.
	 * 
	 * @since 1.0.0
	 * @return Model_Term[]
	 */
	public function get_terms() {
		return Model_Term::get_cpt_terms( $this );
	}

	/**
	 * Save Taxonomy terms from fields.
	 * 
	 * @since 1.0.0
	 */
	public function save_terms() {
		Model_Term::save_cpt_terms( $this );
	}

	/**
	 * Switch to main site.
	 * 
	 * @since 1.0.0
	 */
	public static function switch_to_main_site() {
		$class = get_called_class();
		if ( is_multisite() && function_exists( 'switch_to_blog' ) && $class::$SAVE_IN_MAIN_SITE && self::MAIN_SITE_ID != get_current_blog_id() ) {
			switch_to_blog( self::MAIN_SITE_ID );
		}
	}

	/**
	 * Restore current blog/site.
	 * 
	 * @since 1.0.0
	 */
	public static function restore_current_blog() {
		$class = get_called_class();
		if ( is_multisite() && function_exists( 'restore_current_blog' ) && $class::$SAVE_IN_MAIN_SITE ) {
			restore_current_blog();
		}
	}

	/**
	 * Delete post from wp table
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		$this->before_delete();
		
		Model_Term::delete_cpt_terms( $this );
		if ( !empty( $this->id ) ) {
			wp_delete_post( $this->id );
		}
		
		$this->after_delete();
	}

	/**
	 * Method called before object delete.
	 * 
	 * @since 1.0.0
	 * 
	 * {@inheritDoc}
	 * @see Model::before_delete()
	 */
	public function before_delete() {
		parent::before_delete();
		
		$class = get_called_class();
		if ( is_multisite() && function_exists( 'switch_to_blog' ) && $class::$SAVE_IN_MAIN_SITE ) {
			switch_to_blog( 1 );
		}
	}

	/**
	 * Method called after object delete.
	 * 
	 * @since 1.0.0
	 * 
	 * {@inheritDoc}
	 * @see Model::after_delete()
	 */
	public function after_delete() {
		parent::after_delete();
		
		$class = get_called_class();
		if ( is_multisite() && function_exists( 'restore_current_blog' ) && $class::$SAVE_IN_MAIN_SITE ) {
			restore_current_blog();
		}
	}

	/**
	 * Trash post.
	 *
	 * @since 1.0.0
	 */
	public function trash() {
		if ( !empty( $this->id ) ) {
			wp_trash_post( $this->id );
			$this->post_status = self::POST_STATUS_TRASH;
			$this->save();
		}
	}
	
	/**
	 * Untrash post.
	 *
	 * @since 1.0.0
	 */
	public function untrash() {
		if ( !empty( $this->id ) ) {
			wp_untrash_post( $this->id );
			$this->post_status = self::POST_STATUS_PUBLISH;
			$this->save();
		}
	}
	
	/**
	 * Get custom register post type args for this model.
	 * Override in child class.
	 *
	 * @since 1.0.0
	 */
	public static function get_register_post_type_args() {
		return apply_filters( 'wd_model_cpt_register_post_type_args', array() );
	}

	/**
	 * Check to see if the post is currently being edited.
	 *
	 * @see wp_check_post_lock.
	 *
	 * @since 1.0.0
	 *       
	 * @return boolean True if locked.
	 */
	public function check_object_lock() {
		$locked = false;
		
		if ( $this->is_valid() && $lock = get_post_meta( $this->id, '_wd_edit_lock', true ) ) {
			
			$time = $lock;
			$time_window = apply_filters( 'wd_model_cpt_check_object_lock_window', 150 );
			if ( $time && $time > time() - $time_window ) {
				$locked = true;
			}
		}
		
		return apply_filters( 'wd_model_cpt_check_object_lock', $locked, $this );
	}

	/**
	 * Mark the object as currently being edited.
	 *
	 * Based in the wp_set_post_lock
	 *
	 * @since 1.0.0
	 *       
	 * @return bool|int
	 */
	public function set_object_lock() {
		$lock = false;
		
		if ( $this->is_valid() ) {
			$lock = apply_filters( 'wd_model_cpt_set_object_lock', time() );
			update_post_meta( $this->id, '_wd_edit_lock', $lock );
		}
		
		return apply_filters( 'wd_model_cpt_set_object_lock', $lock, $this );
	}

	/**
	 * Delete object lock.
	 *
	 * @since 1.0.0
	 */
	public function delete_object_lock() {
		if ( $this->is_valid() ) {
			update_post_meta( $this->id, '_wd_edit_lock', '' );
		}
		
		do_action( 'wd_model_cpt_delete_object_lock', $this );
	}

	/**
	 * Check if the current post type has a valid ID.
	 *
	 * @since 1.0.0
	 *       
	 * @return boolean True if valid.
	 */
	public function is_valid() {
		$valid = false;
		
		if ( $this->id > 0 ) {
			$valid = true;
		}
		
		return apply_filters( 'wd_model_cpt_is_valid', $valid, $this );
	}

	/**
	 * Get comment status types.
	 * 
	 * @since 1.0.0
	 * 
	 * @return array String The comment status types available.
	 */
	public static function get_comment_status_types() {
		return apply_filters( 'wd_model_cpt_get_comment_status_types', array( 
				self::STATUS_OPEN => __( 'Open', 'WD_TEXT_DOMAIN' ), 
				self::STATUS_CLOSED => __( 'Closed', 'WD_TEXT_DOMAIN' ) ) );
	}

	/**
	 * Get model custom post type.
	 * 
	 * @since 1.0.0
	 * @return string The custom post type identifier.
	 */
	public function get_post_type() {
		return static::$POST_TYPE;
	}
	
	/**
	 * Get post url / permalink.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $force_ssl
	 * @return string
	 */
	public function get_url( $force_ssl = true ) {
		$url = null;
		
		$url = get_permalink( $this->id );
		if ( $force_ssl && ! Helper_Util::is_localhost() ) {
			$url = Helper_Util::get_ssl_url( $url );
		}

		return apply_filters( 'wd_model_cpt_get_url', $url );
	}

	public function get_user() {
		return Model_User::load( $this->user_id );
	}

	/**
	 * Returns property associated with the render.
	 *
	 * @since 1.0.0
	 *       
	 * @access public
	 * @param string $property The name of a property.
	 * @return mixed Returns mixed value of a property or NULL if a property doesn't exist.
	 */
	public function __get( $property ) {
		$value = null;
		
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				default :
					$value = $this->$property;
					break;
			}
		}
		else {
			switch ( $property ) {
				case 'name' :
					$value = $this->post_name;
					break;
				case 'title' :
					$value = $this->post_title;
					break;
				default :
					break;
			}
		}
		return $value;
	}

	/**
	 * Set specific property.
	 *
	 * @since 1.0.0
	 *       
	 * @access public
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'name' :
					$this->name = sanitize_text_field( $value );
				case 'post_name' :
					$this->post_name = sanitize_text_field( $value );
					break;
				case 'title' :
				case 'post_title' :
					$this->post_title = sanitize_text_field( $value );
					break;
				case 'post_status' :
					if ( array_key_exists( $value, get_post_stati() ) ) {
						$this->$property = $value;
					}
					break;
				case 'comment_status' :
				case 'ping_status' :
					if ( array_key_exists( $value, self::get_comment_status_types() ) ) {
						$this->$property = $value;
					}
					break;
				case 'post_author' :
				case 'post_parent' :
				case 'parent_id' :
					$this->$property = intval( $value );
					break;
				case 'post_modified' :
					$this->$property = $this->validate_date( $value );
					break;
				case 'excerpt' :
				case 'description' :
					$this->$property = wp_kses_post( $value );
					break;
				default :
					$this->$property = $value;
					break;
			}
		}
		else {
			switch ( $property ) {
				case 'name' :
					$this->post_name = sanitize_text_field( $value );
					break;
				case 'title' :
					$this->post_title = sanitize_text_field( $value );
					break;
			}
		}
		
		do_action( 'wd_model_cpt__set_after', $property, $value, $this );
	}
}