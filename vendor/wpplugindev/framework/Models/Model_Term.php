<?php
namespace WPPluginsDev\Models;

/**
 * Term Model Class.
 * Syncs CPT terms to WP terms.
 *
 * @since 1.0.0
 */
class Model_Term extends Model {

	/**
	 * Term ID.
	 * 
	 * @since 1.0.0
	 */
	protected $term_id;

	/**
	 * Term name.
	 *
	 * @since 1.0.0
	 */
	protected $name;
	
	/**
	 * Term Slug.
	 *
	 * @since 1.0.0
	 */
	protected $slug;

	/**
	 * Term Taxonomy.
	 *
	 * @since 1.0.0
	 */
	protected $taxonomy;

	/**
	 * Term object ID.
	 *
	 * @since 1.0.0
	 */
	protected $object_id;

	/**
	 * Term description.
	 *
	 * @since 1.0.0
	 */
	protected $description;
	
	/**
	 * Term Taxonomy ID.
	 *
	 * @since 1.0.0
	 */
	protected $term_taxonomy_id;
	
	/**
	 * Ignore fields during persitance.
	 *
	 * @since 1.0.0
	 */
	public $ignore_fields = array( 'actions', 'filters', 'ignore_fields', 'term_id', 'name', 'slug', 'taxonomy', 'object_id', 'description', 'term_taxonomy_id', 'protected_fields' );
	
	/**
	 * Verify if term has ID.
	 *
	 * @since 1.0.0
	 */
	public function is_valid() {
		return $this->term_id > 0;
	}
	
	/**
	 * Save the term.
	 *
	 * @since 1.0.0
	 * 
	 * @param array $args The WP term arguments options.
	 */
	public function save( $args = array() ) {
		
		$this->before_save();
		
		if( $this->taxonomy ) {
			if( $this->is_valid() ) {
				$t = wp_update_term( $this->term_id, $this->taxonomy, $args );
				$this->term_taxonomy_id = $t['term_taxonomy_id'];
			}
			elseif( $this->name ) {
				$t = term_exists( (string)$this->name, $this->taxonomy );
				if( $t instanceof WP_Error ) {
					throw new Exception( $t->get_error_message() );
				}
				elseif( empty( $t ) ) {
					$t = wp_insert_term( $this->name, $this->taxonomy, $args );
					if( $t instanceof  WP_Error ) {
						throw new Exception( "Can not insert term:tax [$this->name]:[$this->taxonomy]");
					}
					else {
						$this->term_id = $t['term_id'];
						$this->term_taxonomy_id = $t['term_taxonomy_id'];
					}
				}
			}
			// save attributes in postmeta table
			$term_meta = get_term_meta( $this->term_id );
			
			$fields = get_object_vars( $this );
			foreach ( $fields as $field => $val ) {
				if ( in_array( $field, $this->ignore_fields ) ) {
					continue;
				}
				if ( isset( $this->$field )
						&& ( ! isset( $term_meta[ $field ][0] ) || $term_meta[ $field ][0] != $this->$field ) ) {
							update_term_meta( $this->term_id, $field, $this->$field );
						}
			}
			
			wp_cache_set( $this->term_id, $this, $class );
		}
		else {
			Debug::log($this);
			throw new Exception( "Cant save term without taxonomy");
		}
		
		$this->after_save();
	}
	
	/**
	 * Get custom post type terms.
	 *
	 * @since 1.0.0
	 * 
	 * @param CA_Model_Cpt The custom post type instance to get terms from.
	 * @return CA_Model_Term[] The found terms.
	 */
	public static function get_cpt_terms( $cpt ) {
		$terms = array();
		$args = array();

		if( $cpt->is_valid() ) {
			$taxonomies = array_keys( $cpt->get_taxonomies() );

			$wp_terms = wp_get_object_terms( $cpt->id, $taxonomies, $args );
			if( ! empty( $wp_terms ) && ! is_wp_error( $wp_terms ) ) {
				foreach ( $wp_terms as $wp_term ) {
					$term = new self();
					$term->term_id = $wp_term->term_id;
					$term->object_id = $cpt->id;
					$term->name = $wp_term->name;
					$term->slug = $wp_term->slug;
					$term->taxonomy = $wp_term->taxonomy;
					$term->term_taxonomy_id = $wp_term->term_taxonomy_id;
					$term->description = $wp_term->description;
					$terms[] = $term;
				}
			}
		}
		
		return $terms;
	}
	
	/**
	 * Save custom post type instance terms into WP DB.
	 *
	 * @since 1.0.0
	 * 
	 * @param CA_Model_Cpt The custom post type instance to save terms from.
	 */
	public static function save_cpt_terms( $cpt ) {
		$taxonomies = $cpt->get_taxonomies();
		$terms = array();
		foreach ( $taxonomies as $taxonomy => $args ) {
			
			if( $cpt->$taxonomy ) {
				$term_names = $cpt->$taxonomy;
				
				if( ! is_array( $term_names ) ) {
					$term_names = array( $term_names );
				}
				foreach( $term_names as $term_name ) { 
					//Create term if it does not exist in the db
					$exist = term_exists( $term_name, $taxonomy );
					if( $exist ) {
						$term = self::load( $exist['term_id'] );
					}
					else {
						$term = CA_Factory::create( 'CA_Model_Term' );
						$term->name = $term_name;
						$term->taxonomy = $taxonomy;
						$term->save();
					}
					$terms[] = $term->term_id;
				}				
				wp_set_object_terms( $cpt->id, $term_name, $taxonomy );
			}
		}
	}
	
	/**
	 * Delete custom post type instance terms from WP DB.
	 *
	 * @since 1.0.0
	 *
	 * @param CA_Model_Cpt The custom post type instance to delete terms from.
	 */
	public static function delete_cpt_terms( $cpt ) {
		$taxonomies = $cpt->get_taxonomies();
		$terms = array();
		foreach ( $taxonomies as $taxonomy => $args ) {
			if( $cpt->$taxonomy ) {
				$term_names = $cpt->$taxonomy;
				wp_remove_object_terms( $cpt->id, $term_names, $taxonomy );
			}
		}
	}
	
	/**
	 * Get query args form terms.
	 *
	 * @since 1.0.0
	 */
	public static function get_query_args() {
		return array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'slug' => $slug,
				'fields' => 'all'
		);
	}
	
	/**
	 * Load terms from WP DB.
	 *
	 * @since 1.0.0
	 *
	 * @param CA_Model_Cpt The custom post type instance to get terms from.
	 */
	public static function load( $term_id = 0 ) {
		
		$term = null;
		if( ! empty( $term_id ) ) {
			$wp_term = get_term( $term_id );
// 			Debug::log("load term_id: $term_id");
// 			Debug::log($wp_term);
			
			$term = self::load_from_wp( $wp_term );
		}
		
		return $term;
	}
	
	/**
	 * Load term using slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The term slug to load.
	 * @param string $taxonomy the term taxonomy to load.
	 */
	public static function load_by_slug( $slug, $taxonomy ) {
		$args = array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'slug' => $slug,
				'fields' => 'all'
		);
		
		$term = CA_Factory::create( self::class );
		$wp_term = get_terms( $args );
		
		if( ! empty( $wp_term[0] ) ) {
			$term = self::load_from_wp( $wp_term[0] );
		}
		
		return $term;
	}
	
	/**
	 * Load term using term name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The term name to load.
	 * @param string $taxonomy the term taxonomy to load.
	 */
	public static function load_by_term_name( $name, $taxonomy ) {
	
		$args = array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'name' => $name,
				'fields' => 'all'
		);
		
		$term = CA_Factory::create( self::class );
		
		$wp_term = get_terms( $args );
		
		if( $wp_term instanceof WP_Error ) {
			throw new Exception( $wp_term->get_error_message() );
		}
		elseif( ! empty( $wp_term[0] ) ) {
			$term = self::load_from_wp( $wp_term[0] );
		}
		
		return $term;
	}
	
	/**
	 * Delete this term.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		wp_delete_term( $this->term_id, $this->taxonomy );
	}
	
	/**
	 * Get term id.
	 *
	 * @since 1.0.0
	 *
	 * @param string $term The term to find id.
	 * @param string $taxonomy the term taxonomy to find id.
	 */
	 public static function get_term_id( $term, $taxonomy ) {
		$args = array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'slug' => $term,
				'fields' => 'ids'
		);
		$term_id = 0;
		$term = get_terms( $args );

		if( ! empty( $term[0] ) ) {
			$term_id = $term[0];
		}
		
		return $term_id;
	}
	
	/**
	 * Get taxonomy terms.
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy the term taxonomy to load.
	 * @param bool $only_names The flag to only return term names.
	 * @param array $args The arguments to config query.
	 * @return string[]|CA_Model_Term[] The found terms.
	 */
	public static function get_taxonomy_terms( $taxonomy, $only_names = true, $args = null ) {
		$terms = array();
		if( $only_names ) {
			$terms = array( '' => '' );
		}
		$defaults = array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
				'order' => 'ASC',
				'orderby' => 'ID',
		);
	
		$args = wp_parse_args( $args, $defaults );
		$wp_terms = get_terms(  $args );

		if( ! empty( $wp_terms ) && ! is_wp_error( $wp_terms ) ) {
			foreach ( $wp_terms as $wp_term ) {
				if( $only_names ) {
					$terms[ $wp_term->name ] = $wp_term->name;
				}
				else {
					$term = self::load_from_wp( $wp_term );
					$terms[] = $term;
				}
			}
		}
		
		return $terms;
	}

	/**
	 * Load terms from WP.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Term $wp_term The WP term to load from.
	 * @return CA_Model_Term The loaded term.
	 */
	public static function load_from_wp( $wp_term ) {
		
		$term = new self();
		$term->term_id = $wp_term->term_id;
		$term->object_id = $cpt->id;
		$term->name = $wp_term->name;
		$term->slug = $wp_term->slug;
		$term->taxonomy = $wp_term->taxonomy;
		$term->term_taxonomy_id = $wp_term->term_taxonomy_id;
		$term->description = $wp_term->description;
		
		return $term;
	}
}