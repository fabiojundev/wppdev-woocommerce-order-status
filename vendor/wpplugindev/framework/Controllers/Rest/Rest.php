<?php
namespace WPPluginsDev\Controllers\Rest;
use Exception;
use WPPluginsDev\Traits\Trait_Request, WPPluginsDev\Traits\Trait_Hook;
use WPPluginsDev\Helpers\Helper_Debug;

/**
 * Base Rest Controller.
 *
 * @since 1.0.0
 */
class Rest extends \WP_REST_Controller {
	
	use Trait_Request, Trait_Hook;
	const REST_NAMESPACE = 'wd/v1';
	const REST_BASE = '';
	
	/**
	 * Singleton Instance.
	 */
	protected static $instance = null;
	
	/**
	 * Return an instance of this class.
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		
		// If the single instance hasn't been set, set it now.
		if ( null == static::$instance ) {
			static::$instance = new static;
			static::$instance->do_hooks();
		}
		return static::$instance;
	}
	
	/**
	 * Get Rest URL.
	 * @return string
	 */
	public static function get_rest_url() {
		return rest_url( static::REST_NAMESPACE . '/' . static::REST_BASE );
	}
	
	/**
	 * Set up WordPress hooks and filters
	 *
	 * @return void
	 */
	public function do_hooks() {
		$this->add_action( 'rest_api_init', 'register_routes' );
	}
	
	public function register_routes() {
		throw new \Exception( 'Override this method' );	
	}

	public function get_auth_error_response( $redir_url = null ) {
		$login_url = '/wp-login.php';
		return new \WP_REST_Response(
				403,
				'Entrar no site',
				array( 'login_url' => add_query_arg( 'redirect_to', $redir_url, $login_url ) )
				);
	}
}