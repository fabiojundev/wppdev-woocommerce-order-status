<?php
namespace WPPluginsDev\Models;
use WPPluginsDev\Factory;
use WPPluginsDev\Helpers\Helper_Debug;
/**
 * User Model Class.
 *
 * @since 1.0.0
 */
class Model_User extends Model {
	/**
	 * Prefix used to store fields in WP usermeta table.
	 * 
	 * @since 1.0.0
	 */
	const WP_USER_META_PREFIX = 'wd_';
	
	/**
	 * The user ID.
	 * 
	 * @since 1.0.0
	 */
	protected $id;
	
	/**
	 * Admin user indicator.
	 *
	 * @since 1.0.0
	 *       
	 * @var boolean
	 */
	protected $is_admin = false;
	
	/**
	 * User's username.
	 *
	 * Mapped from wordpress $wp_user object.
	 *
	 * @see \WP_User $user_login.
	 *     
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $username;
	
	/**
	 * User's email.
	 *
	 * Mapped from wordpress $wp_user object.
	 *
	 * @see \WP_User $user_email.
	 *     
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $email;
	
	/**
	 * User's name.
	 *
	 * Mapped from wordpress $wp_user object.
	 *
	 * @see \WP_User $display_name.
	 *     
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $name;
	
	/**
	 * User's first name.
	 *
	 * Mapped from wordpress $wp_user object.
	 *
	 * @see \WP_User $first_name
	 *     
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $first_name;
	
	/**
	 * User's last name.
	 *
	 * Mapped from wordpress $wp_user object.
	 *
	 * @see \WP_User $last_name.
	 *     
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $last_name;
	
	/**
	 * User's Birth Date.
	 *
	 * @since 1.0.0
	 */
	protected $birth_dt;
	
	/**
	 * User's password.
	 *
	 * Used when registering.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $password;
	
	/**
	 * User's password confirmation.
	 *
	 * Used when registering.
	 *
	 * @since 1.0.0
	 *       
	 * @var string
	 */
	protected $password2;
	
	/**
	 * User phone number.
	 * 
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $phone_number;
	
	/**
	 * User phone area code.
	 * 
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $phone_area;
	
	/**
	 * User phone without area code.
	 * 
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $phone_number_part;
	
	/**
	 * User billing address.
	 * 
	 * @since 1.0.0
	 *
	 * @var Address
	 */
	protected $billing_address;
	
	/**
	 * Don't persist this fields.
	 *
	 * @since 1.0.0
	 *       
	 * @var string[] The fields to ignore when persisting.
	 */
	public $ignore_fields = array( 
			'name', 
			'username', 
			'email', 
			'name', 
			'first_name', 
			'last_name', 
			'password', 
			'password2', 
			'actions', 
			'filters', 
			'ignore_fields' );

	/**
	 * Get users.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $args The arguments to select data.
	 * @return User[] The found users.
	 */
	public static function get_users( $args = null ) {
		$users = array();
		
		$args = self::get_query_args( $args );
		$wp_user_search = new \WP_User_Query( $args );
		$wp_users = $wp_user_search->get_results();
		
		foreach ( $wp_users as $user_id ) {
			$users[] = Factory::load( static::class, $user_id );
		}
		
		return apply_filters( 'wd_model_user_get_users', $users );
	}

	/**
	 * Get Default \WP_Query args.
	 * 
	 * @since 1.0.0
	 * 
	 * @param mixed $args The overriding query args.
	 * @return mixed The resulting query args.
	 */
	public static function get_query_args( $args = null ) {
		$defaults = apply_filters( 
				'wd_model_user_get_query_args_defaults', 
				array( 'order' => 'DESC', 'orderby' => 'ID', 'number' => 10, 'offset' => 0, 'fields' => 'ID' ) );
		
		$args = wp_parse_args( $args, $defaults );
		
		return apply_filters( 'wd_model_user_get_query_args', $args, $defaults );
	}

	/**
	 * Get current user.
	 *
	 * @since 1.0.0
	 *       
	 * @return User The current user.
	 */
	public static function get_current_user() {
		return Factory::load( static::class, get_current_user_id() );
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
	public static function load( $user_id = false ) {
		$model = new static();
		$class = get_class( $model );
		$cache = wp_cache_get( $user_id, $class );
		
		if ( $cache ) {
			$model = $cache;
			// 			CA_Helper_Debug::log("---------from cache, $class");
		}
		else {
			$wp_user = new \WP_User( $user_id );
			// 			CA_Helper_Debug::log("---------from DB, $class");
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
				'wd_model_user_load',
				$model,
				$class,
				$user_id
				);
	}
	
	/**
	 * Save user.
	 *
	 * Create a new user is id is empty.
	 * Save user fields to wp_user and wp_usermeta tables.
	 * Set cache for further use in Factory::load.
	 * The usermeta are prefixed with 'WP_USER_META_PREFIX'.
	 *
	 * @since 1.0.0
	 *       
	 * @return User The saved user object.
	 */
	public function save() {
		Helper_Debug::log( "saving-----------" );
		
		$this->before_save();
		
		if ( empty( $this->id ) ) {
			$this->create_new_user();
		}
		
		$user_details = get_user_meta( $this->id );
		$fields = get_object_vars( $this );
		
		foreach ( $fields as $field => $val ) {
			if ( in_array( $field, $this->ignore_fields ) ) {
				continue;
			}
			$wp_field = static::WP_USER_META_PREFIX . $field;
			if ( isset( $this->$field ) && ( ! isset( $user_details[ $wp_field ][ 0 ] ) || $user_details[ $wp_field ][ 0 ] != $this->$field ) ) {
				update_user_meta( $this->id, $wp_field, $this->$field );
			}
		}
		
		if ( isset( $this->username ) ) {
			//verify username update
			$old_user_data = get_userdata( $this->id );
			if ( username_exists( $this->username ) && ! empty( $old_user_data->user_login ) && $this->username != $old_user_data->user_login ) {
				throw new \Exception( __( 'Nome de usuário indisponível.', WD_TEXT_DOMAIN ) );
			}
			//verify email update
			if ( email_exists( $this->email ) && ! empty( $old_user_data->user_email ) && $this->email != $old_user_data->user_email ) {
				throw new \Exception( __( 'Email já está sendo utilizado.', WD_TEXT_DOMAIN ) );
			}
			
			$wp_user = new \stdClass();
			$wp_user->ID = $this->id;
			$wp_user->nickname = $this->username;
			$wp_user->user_email = $this->email;
			$wp_user->user_nicename = $this->name;
			$wp_user->first_name = $this->first_name;
			$wp_user->last_name = $this->last_name;
			$wp_user->display_name = $this->name;
			
			if ( ! empty( $this->password ) && $this->password == $this->password2 ) {
				$wp_user->user_pass = $this->password;
			}
			wp_update_user( get_object_vars( $wp_user ) );
		}
		
		$class = get_class( $this );
		wp_cache_set( $this->id, $this, $class );
		
		$this->after_save();
		
		return apply_filters( 'wd_model_user_save', $this );
	}

	/**
	 * Create new WP user.
	 * Validate user data before create.
	 * 
	 * @since 1.0.0
	 *       
	 * @throws Exception
	 */
	protected function create_new_user() {
		$errors = new \WP_Error();
		
		$this->create_username();

		$required = apply_filters( 
				'wd_model_user_create_new_user_required', 
				array( 
						'name' => __( 'Nome Completo', WD_TEXT_DOMAIN ), 
						'username' => __( 'Nome de Usuário', WD_TEXT_DOMAIN ), 
						'email' => __( 'Email', WD_TEXT_DOMAIN ), 
						'phone_number' => __( 'Número Telefone', WD_TEXT_DOMAIN ), 
						'password' => __( 'Senha', WD_TEXT_DOMAIN ) // 'password2' => __( 'Confirmação de Senha', WD_TEXT_DOMAIN ),
		) );
		
		foreach ( $required as $field => $message ) {
			if ( empty( $this->$field ) ) {
				$errors->add( $field, sprintf( __( 'O campo <strong>%s</strong> é obrigatório.', WD_TEXT_DOMAIN ), $message ) );
			}
		}
		
		Helper_Debug::log( $this->name );
		if( strpos( $this->name, ' '  ) !== false ) {
			$name = explode( ' ', $this->name );
			$this->first_name = $name[0];
			unset( $name[0] );
			$this->last_name = implode( ' ', $name );
		}
		
		if ( ! validate_username( $this->username ) ) {
			$errors->add( 'usernamenotvalid', __( 'Nome de <strong>Usuário</strong> inválido.', WD_TEXT_DOMAIN ) );
		}
		
		if ( username_exists( $this->username ) ) {
			$errors->add( 'usernameexists', __( 'Nome de <strong>Usuário</strong> indisponível.', WD_TEXT_DOMAIN ) );
		}
		
		if ( ! is_email( $this->email ) ) {
			$errors->add( 'emailnotvalid', __( '<strong>Email</strong> inválido.', WD_TEXT_DOMAIN ) );
		}
		
		if ( email_exists( $this->email ) ) {
			$errors->add( 'emailexists', __( '<strong>Email</strong> já está sendo utilizado.', WD_TEXT_DOMAIN ) );
		}
		
		$errors = apply_filters( 'wd_model_user_create_new_user_validation_errors', $errors );
		
		$result = apply_filters( 
				'wpmu_validate_user_signup', 
				array( 
						'user_name' => $this->username, 
						'orig_username' => $this->username, 
						'user_email' => $this->email, 
						'errors' => $errors ) );
		
		$errors = $result[ 'errors' ];
		$error_msgs = $errors->get_error_messages();
		
		if ( ! empty( $error_msgs ) ) {
			throw new \Exception( implode( '<br/>', $error_msgs ) );
		}
		else {
			$user_id = wp_create_user( $this->username, $this->password, $this->email );
			
			if ( is_wp_error( $user_id ) ) {
				$errors->add( 'userid', $user_id->get_error_message() );
				
				throw new \Exception( implode( '<br/>', $errors->get_error_messages() ) );
			}
			$this->id = $user_id;
		}
		
		do_action( 'wd_model_user_create_new_user', $this );
	}

	/**
	 * Create new username from email.
	 *
	 * @since 1.0.0
	 * 
	 * @return string The created username.
	 */
	 public function create_username() {
		if ( ! empty( $this->email ) ) {
			$this->username = substr( $this->email, 0, strpos( $this->email, '@' ) );
			$this->username = strtolower( $this->username );
			$this->username = preg_replace( "$[^a-zA-Z0-9\s]$", '', $this->username );
			$i = 0;
			$username = $this->username;
			while ( username_exists( $username ) ) {
				$username = $this->username . $i++;
			}
			$this->username = $username;
			return $username;
		}
	}

	/**
	 * Signon User.
	 *
	 * @since 1.0.0
	 */
	public static function login_user( $creds ) {
		$user = null;
		$wp_user = wp_signon( $creds, is_ssl() );
		
		if ( is_wp_error( $wp_user ) ) {
			throw new \Exception( $wp_user->get_error_message() );
		}
		else {
			$user = Factory::load( static::class, $wp_user->ID );
			$user->signon();
		}
		
		return $user;
	}
	
	/**
	 * Sign on user.
	 *
	 * @since 1.0.0
	 */
	public function signon() {
		if ( $this->is_valid() ) {
			wp_set_current_user( $this->id, $this->username );
			wp_set_auth_cookie( $this->id, true, is_ssl() );
		}
	}

	/**
	 * Logout user.
	 *
	 * @since 1.0.0
	 */
	 public function logout() {
		wp_logout();
		wp_set_current_user( 0 );
	}

	/**
	 * Get User ID by username.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $username The username to load user from.
	 * @return int The user ID.
	 */
	 public static function get_user_id_by_username( $username ) {
	 	$user_id = 0;
	 	$wp_user = get_user_by( 'login', $username );
	 	if( $wp_user && $wp_user->ID ) {
	 		$user_id = $wp_user->ID;
	 	}
	 	return $user_id;
	}

	/**
	 * Get User by email.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email The email to load user from.
	 * @return User The loaded user.
	 */
	public static function get_user_by_email( $email ) {
		$user = null;
		$wp_user = get_user_by( 'email', $email );
		if( $wp_user && $wp_user->ID ) {
			$user = Factory::load( static::class, $wp_user->ID );
		}
		return $user;
	}
	
	/**
	 * Verify is user is logged in.
	 *
	 * @since 1.0.0
	 *       
	 * @return boolean True if user is logged in.
	 */
	public static function is_logged_user() {
		$logged = is_user_logged_in();
		
		return apply_filters( 'wd_model_user_is_logged_user', $logged );
	}

	/**
	 * Verify if is Admin user.
	 *
	 * @since 1.0.0
	 *       
	 * @todo modify this when implementing network/multisites handling.
	 *      
	 * @param int|bool $user_id Optional. The user ID. Default to current user.
	 * @param string $capability The capability to check for admin users.
	 * @return boolean True if user is admin.
	 */
	public static function is_admin_user( $user_id = false, $capability = 'manage_options' ) {
		$is_admin = false;
				
		$capability = apply_filters( 'wd_model_user_is_admin_user_capability', $capability );
		
		if ( ! empty( $capability ) ) {
			$wp_user = null;
			
			if ( empty( $user_id ) ) {
				$wp_user = wp_get_current_user();
			}
			else {
				$wp_user = new \WP_User( $user_id );
			}
			
			if ( self::is_super_admin( $user_id ) ) {
				$is_admin = true;
			}
			else {
				$is_admin = $wp_user->has_cap( $capability );
			}
		}
		
		return apply_filters( 'wd_model_user_is_admin_user', $is_admin, $user_id );
	}

	/**
	 * Verify if is Super Admin user.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if user is super admin.
	 */
	
	public static function is_super_admin( $user_id = 0 ) {
		$is_super_admin = false;
		
		if ( is_multisite() && is_super_admin( $user_id ) ) {
			$is_super_admin = true;
		}
		
		return $is_super_admin;
	}

	/**
	 * Get Admin users emails.
	 *
	 * @since 1.0.0
	 *       
	 * @return string[] The admin emails.
	 */
	public static function get_admin_user_emails() {
		$admins = array();
		
		$args = array( 'role' => 'administrator', 'fields' => array( 'ID', 'user_email' ) );
		
		$wp_user_search = new \WP_User_Query( $args );
		$users = $wp_user_search->get_results();
		
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$admins[ $user->ID ] = $user->user_email;
			}
		}
		return apply_filters( 'wd_model_user_get_admin_user_emails', $admins );
	}

	/**
	 * Get Admin users IDs.
	 *
	 * @since 1.0.0
	 *
	 * @return int[] The admin IDs.
	 */
	 public static function get_admin_user_id( $return_all = false ) {
		$admins = null;
		
		$args = array( 'role' => 'administrator', 'fields' => 'ids' );
		
		$wp_user_search = new \WP_User_Query( $args );
		$users = $wp_user_search->get_results();

// 		Helper_Debug::log($users);
		if ( ! empty( $users ) ) {
			if ( $return_all ) {
				$admins = array();
				foreach ( $users as $user_id ) {
					$admins[] = $user_id;
				}
			}
			else {
				$admins = $users[0];
			}
		}
		
		return $admins;
	}

	/**
	 * Get user's username.
	 *
	 * @since 1.0.0
	 *       
	 * @param int $user_id The user ID to get username.
	 * @return string The username.
	 */
	public static function get_username( $user_id ) {
		$user = Factory::load( static::class, $user_id );
		
		return apply_filters( 'wd_model_user_get_username', $user->username, $user_id );
	}

	/**
	 * Get user's usernames.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $args The args to restrict user query.
	 * @return string[] | User[] The usernames.
	 */
	 public static function get_usernames( $args = null ) {
		$users = array();
		
		if ( $return_array ) {
			$users[ 0 ] = __( 'Selecione', WD_TEXT_DOMAIN );
		}
		
		$args[ 'blog_id' ] = 0;
		$args[ 'number' ] = - 1;
		$args[ 'fields' ] = array( 'ID', 'user_login' );
		$args = self::get_query_args( $args );
		$wp_user_search = new \WP_User_Query( $args );
		$wp_users = $wp_user_search->get_results();
		
		foreach ( $wp_users as $user ) {
			$users[ $user->ID ] = $user->user_login;
		}
		
		return apply_filters( 'wd_model_user_get_users_usernames', $users );
	}

	/**
	 * Get user's page URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string The author's page URL.
	 */
	public function get_url() {
		return get_author_posts_url( $this->id );
	}

	/**
	 * Add user capability.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $cap The capability to add.
	 */
	public function add_cap( $cap ) {
		$user = new \WP_User( $this->id );
		$user->add_cap( $cap );
	}

	/**
	 * Remove user capability.
	 *
	 * @since 1.0.0
	 *
	 * @param string $cap The capability to add.
	 */
	public function remove_cap( $cap ) {
		$user = new \WP_User( $this->id );
		$user->remove_cap( $cap );
	}

	/**
	 * Add user role.
	 *
	 * @since 1.0.0
	 *
	 * @param string $role The role to add.
	 */
	public function add_role( $role ) {
		$user = new \WP_User( $this->id );
		$user->add_role( $role );
	}

	/**
	 * Remove user role.
	 *
	 * @since 1.0.0
	 *
	 * @param string $role The role to remove.
	 */
	public function remove_role( $role ) {
		$user = new \WP_User( $this->id );
		$user->remove_role( $role );
	}

	/**
	 * Get user capabilities.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] The user's capabilities.
	 */
	public function get_capabilities() {
		$cap = array();
		$data = get_userdata( $this->id );
		
		if ( is_array( $data->allcaps ) ) {
			foreach ( $data->allcaps as $key => $val ) {
				$cap[ $key ] = $key;
			}
		}
		
		return $cap;
	}

	/**
	 * Return array of properties of this object.
	 *
	 * @since 1.0.0
	 *       
	 * @return array of fields.
	 */
	public function to_array() {
		$obj_fields = $this->get_object_vars();
		$add_fields = array( 'id', 'name', 'username', 'email', 'name', 'first_name', 'last_name', 'phone_number' );
		$fields = array();
		foreach ( $add_fields as $field ) {
			if ( array_key_exists( $field, $obj_fields ) ) {
				$fields[ $field ] = $obj_fields[ $field ];
			}
		}

		$fields[ 'nonce' ] = wp_create_nonce( 'wp_rest' );
		$actions = array( 
				CA_Controller_Rest_Subscription::LOGIN_ACTION, 
				CA_Controller_Rest_Subscription::LOGOUT_ACTION, 
				CA_Controller_Rest_Subscription::SIGNUP_ACTION, 
				CA_Controller_Rest_Subscription::SUBSCRIBE_ACTION,
				CA_Controller_Rest_Subscription::PAY_ACTION,
				CA_Controller_Rest_Subscription::FORGOT_PWD_ACTION, 
				CA_Controller_Rest_Subscription::SHIPPING_ACTION );
		foreach ( $actions as $action ) {
			$fields[ 'nonces' ][ $action ] = wp_create_nonce( $action );
		}

		return $fields;
	}

	/**
	 * Verify if current object is valid.
	 *
	 * @since 1.0.0
	 *       
	 * @return boolean True if is valid.
	 */
	public function is_valid() {
		$valid = ( $this->id > 0 );
		
		return apply_filters( 'wd_model_user_is_valid', $valid, $this );
	}

	/**
	 * Validate user info.
	 *
	 * @since 1.0.0
	 * 
	 * @return boolean True if validated.
	 * @throws Exception if not validated.
	 */
	public function validate_user_info() {
		$validation_errors = new \WP_Error();
		
		if ( ! is_email( $this->email ) ) {
			$validation_errors->add( 'emailnotvalid', __( 'The email address is not valid, sorry.', WD_TEXT_DOMAIN ) );
		}
		
		if ( $this->password != $this->password2 ) {
			Helper_Debug::log( 'no password match' );
			$validation_errors->add( 'passmatch', __( 'Please ensure the passwords match.', WD_TEXT_DOMAIN ) );
		}
		
		$errors = apply_filters( 'wd_model_user_validate_user_info_errors', $validation_errors->get_error_messages() );
		
		if ( ! empty( $errors ) ) {
			throw new \Exception( implode( '<br/>', $errors ) );
		}
		else {
			return true;
		}
	}

	/**
	 * Validate CPF.
	 * 
	 * @since 1.0.0
	 *
	 * @return 1: valid, 0: not valid
	 * @param array $cpf
	 */
	public static function validate_CPF( $cpf ) {
		$valid = 1;
		if ( strlen( $cpf ) == 11 && preg_match( "$[0-9]$", $cpf ) ) {
			$nulos = array( 
					"12345678909", 
					"11111111111", 
					"22222222222", 
					"33333333333", 
					"44444444444", 
					"55555555555", 
					"66666666666", 
					"77777777777", 
					"88888888888", 
					"99999999999", 
					"00000000000" );
			
			// verifies in not valid array
			if ( in_array( $cpf, $nulos ) ) {
				$valid = 0;
			}
			// calcs last-1 verify digit
			$acum = 0;
			for ( $i = 0; $i < 9; $i++ ) {
				$acum += $cpf[ $i ] * ( 10 - $i );
			}
			$x = $acum % 11;
			$acum = ( $x > 1 ) ? ( 11 - $x ) : 0;
			// compares calcs to suplied
			if ( $acum != $cpf[ 9 ] ) {
				$valid = 0;
			}
			// calcs last verfiy digit
			$acum = 0;
			for ( $i = 0; $i < 10; $i++ ) {
				$acum += $cpf[ $i ] * ( 11 - $i );
			}
			$x = $acum % 11;
			$acum = ( $x > 1 ) ? ( 11 - $x ) : 0;
			// compares calcs to suplied
			if ( $acum != $cpf[ 10 ] ) {
				$valid = 0;
			}
		}
		else {
			$valid = 0;
		}
		return $valid;
	}

	/**
	 * Validate CNPJ.
	 *
	 * @since 1.0.0
	 * 
	 * @return 1: valid, 0: not valid
	 * @param array $cnpj
	 */
	public static function validate_CNPJ( $cnpj ) {
		$valid = 1;
		$cnpj = preg_replace( "@[./-]@", "", $cnpj );
		if ( strlen( $cnpj ) != 14 or ! is_numeric( $cnpj ) ) {
			$valid = 0;
		}
		else {
			$j = 5;
			$k = 6;
			$soma1 = "";
			$soma2 = "";
			for ( $i = 0; $i < 13; $i++ ) {
				$j = $j == 1 ? 9 : $j;
				$k = $k == 1 ? 9 : $k;
				$soma2 += ( $cnpj{ $i } * $k );
				if ( $i < 12 ) {
					$soma1 += ( $cnpj{ $i } * $j );
				}
				$k--;
				$j--;
			}
			$digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
			$digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;
			$valid = ( ( $cnpj{ 12 } == $digito1 ) and ( $cnpj{ 13 } == $digito2 ) );
		}
		return $valid;
	}

	/**
	 * Set specific property.
	 *
	 * @since 1.0.0
	 *       
	 * @access public
	 * @param string $name The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'email':
					if ( is_email( $value ) ) {
						$this->$property = $value;
					}
					break;
				
				case 'username':
					$this->$property = sanitize_user( $value );
					break;
				
				case 'name':
				case 'first_name':
				case 'last_name':
					$this->$property = sanitize_text_field( $value );
					break;
				
				case 'is_admin':
				case 'manager':
					$this->$property = $this->validate_bool( $value );
					break;
				
				case 'phone_number':
					$value = trim( $value );
					$this->phone_number = $value;
					$value = preg_replace( '/\D/', '', $value );
					if ( preg_match( '/[0-9]{10,11}/', $value ) ) {
						$this->phone_area = substr( $value, 0, 2 );
						$this->phone_number_part = substr( $value, 2 );
					}
					else {
						$this->phone_area = '';
						$this->phone_number_part = '';
					}
					break;
				
				case 'birth_dt':
					$this->$property = null;
					if ( preg_match( '/\d{1,2}\/\d{1,2}\/\d{4}/', $value ) ) {
						$this->$property = $value;
					}
					break;
				
				default:
					$this->$property = $value;
					break;
			}
		}
		
		do_action( 'wd_model_user__set_after', $property, $value, $this );
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
				default:
					$value = $this->$property;
					break;
			}
		}
		
		return apply_filters( 'wd_model_user__get', $value, $property, $this );
	}
}