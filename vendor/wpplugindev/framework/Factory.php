<?php
namespace WPPluginsDev;
use WPPluginsDev\Models\Model_Cookie;
use WPPluginsDev\Models\Model_Cpt;
use WPPluginsDev\Models\Model_Option;
use WPPluginsDev\Models\Model_Transient;
use WPPluginsDev\Models\Model_User;

/**
 * Factory Class.
 *
 * @since 1.0.0
 */
class Factory {

	/**
	 * Create an CA Object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The class to create object from.
	 *
	 * @return object The created object.
	 */
	public static function create( $class, $args = null ) {
		$class = trim( $class );

		if ( class_exists( $class ) ) {
			$obj = new $class( $args );
		}
		else {
			throw new \Exception( 'Class ' . $class . ' does not exist.' );
		}

		return apply_filters( 'wppdev_factory_create_'. $class, $obj );
	}

	/**
	 * Load a MS Object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The class to load object from.
	 * @param int $model_id Retrieve model object using ID.
	 *
	 * @return object The retrieved model.
	 */
	public static function load( $class, $model_id = 0 ) {
		$model = null;

		if ( class_exists( $class ) ) {
			$model = new $class();
			
			if ( $model instanceof Model_Option ) {
				$model = self::load_from_wp_option( $model );
			}
			elseif ( $model instanceof Model_Cpt ) {
				$model = self::load_from_wp_custom_post_type( $model, $model_id );
			}
			elseif ( $model instanceof Model_User ) {
				$args = func_get_args();

				$name = null;
				if ( ! empty( $args[2] ) ) {
					$name = $args[2];
				}
				$model = self::load_from_wp_user( $model, $model_id, $name );
			}
			elseif ( $model instanceof Model_Transient ) {
				$model = self::load_from_wp_transient( $model, $model_id );
			}
			elseif ( $model instanceof Model_Cookie ) {
				$model = self::load_from_cookie( $model, $model_id );
			}
		}

		return apply_filters(
			'wppdev_factory_load_' . $class,
			$model,
			$model_id
		);
	}

	/**
	 * Load Option object.
	 *
	 * Option objects are singletons.
	 *
	 * @since 1.0.0
	 *
	 * @param option $model The empty model instance.
	 *
	 * @return Option The retrieved object.
	 */
	protected static function load_from_wp_option( $model ) {
		$class = get_class( $model );

		$cache = wp_cache_get( $class, 'Model_Option' );

		if ( $cache ) {
			$model = $cache;
		}
		else {
 			$model->before_load();
			$settings = get_option( $class );

			$fields = $model->get_object_vars();
			foreach ( $fields as $field => $val ) {
				if ( in_array( $field, $model->ignore_fields ) ) {
					continue;
				}
				if ( isset( $settings[ $field ] ) ) {
					$model->set_field( $field, $settings[ $field ] );
				}
			}
		}

		$class::$instance = $model;
		
		$model->after_load();
		
		wp_cache_set( $class, $model, 'Model_Option' );

		return apply_filters(
			'wppdev_factory_load_from_wp_option',
			$model,
			$class
		);
	}

	/**
	 * Load Transient object.
	 *
	 * Transient objects are singletons.
	 *
	 * @since 1.0.0
	 *
	 * @param Transient $model The empty model instance.
	 *
	 * @return Transient The retrieved object.
	 */
	public static function load_from_wp_transient( $model ) {
		$class = get_class( $model );

		if ( empty( $class::$instance ) ) {
// 			$cache = wp_cache_get( $class, 'Transient' );

			if ( $cache ) {
				$model = $cache;
			}
			else {
				$model->before_load();
				$settings = get_transient( $class );
				$fields = $model->get_object_vars();

				foreach ( $fields as $field => $val ) {
					if ( in_array( $field, $model->ignore_fields ) ) {
						continue;
					}

					if ( isset( $settings[ $field ] ) ) {
						$model->set_field( $field, $settings[ $field ] );
					}
				}

				$class::$instance = $model;
				
				$model->after_load();

				wp_cache_set( $class, $model, 'Model_Transient' );
			}
		}
		else {
			$model = $class::$instance;
		}

		return apply_filters(
			'wppdev_factory_load_from_wp_transient',
			$model,
			$class
		);
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
	protected static function load_from_wp_custom_post_type( $model, $model_id = 0 ) {
		$class = get_class( $model );

		$model->before_load();
		
		if ( ! empty( $model_id ) ) {
			$cache = wp_cache_get( $model_id, $class );

			if ( $cache ) {
				$model = $cache;
			}
			else {
				$model->before_load();
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
			'wppdev_factory_load_from_cpt',
			$model,
			$class,
			$model_id
		);
	}

	/**
	 * Load User Object.
	 *
	 * Load from user and user meta.
	 *
	 * @since 1.0.0
	 *
	 * @param User $model The empty member instance.
	 * @param int $user_id The user ID.
	 *
	 * @return User The retrieved object.
	 */
	protected static function load_from_wp_user( $model, $user_id, $name = null ) {
		$class = get_class( $model );
		$cache = wp_cache_get( $user_id, $class );

		if ( $cache ) {
			$model = $cache;
		}
		else {
			$wp_user = new \WP_User( $user_id, $name );
			if ( ! empty( $wp_user->ID ) ) {
				
				$model->before_load();
				
				$member_details = get_user_meta( $user_id );
				$model->id = $wp_user->ID;
				$model->username = $wp_user->user_login;
				$model->email = $wp_user->user_email;
				$model->name = $wp_user->display_name;
				$model->first_name = $wp_user->first_name;
				$model->last_name = $wp_user->last_name;

				$model->is_admin = $model->is_admin_user( $user_id );

				$fields = $model->get_object_vars();

				foreach ( $fields as $field => $val ) {
					if ( in_array( $field, $model->ignore_fields ) ) {
						continue;
					}

					if ( isset( $member_details[ $model::WP_USER_META_PREFIX . $field ][0] ) ) {
						$model->set_field(
							$field,
							maybe_unserialize( $member_details[ $model::WP_USER_META_PREFIX . $field ][0] )
						);
					}
				}
				
				$model->after_load();
				
				wp_cache_set( $model->id, $model, $class );
			}
		}

		return apply_filters(
			'wppdev_factory_load_from_wp_user',
			$model,
			$class,
			$user_id
		);
	}
	
	/**
	 * Load Cookie Object.
	 *
	 * Load from cookie.
	 *
	 * @since 1.0.0
	 *
	 * @param Cookie $model The empty cookie instance.
	 * @param in $model_id Not Used.
	 *
	 * @return Cookie The retrieved object.
	 */
	public static function load_from_cookie( $model, $model_id = 0 ) {
		$class = get_class( $model );
		
		$model->before_load();

		if( isset( $_COOKIE[ $class ] ) ) {
			$settings = json_decode( str_replace( '\/', '/' , str_replace( '\"', '"', $_COOKIE[ $class ] ) ), true );
			$fields = $model->get_object_vars();
		
			foreach ( $fields as $field => $val ) {
				if ( in_array( $field, $model->ignore_fields ) ) {
					continue;
				}
		
				if ( isset( $settings[ $field ] ) ) {
					$model->set_field( $field, $settings[ $field ] );
				}
			}
			$class::$instance = $model;
		}
		
		$model->after_load();
		
		return apply_filters(
				'wppdev_factory_load_from_cookie',
				$model,
				$class
		);
	}
}