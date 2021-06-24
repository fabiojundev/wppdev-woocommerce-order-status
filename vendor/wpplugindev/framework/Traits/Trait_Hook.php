<?php
namespace WPPluginsDev\Traits;
/**
 * WP Hook methods. 
 *
 * @since 1.0.0
 */
trait Trait_Hook {
	
	/**
	 * The array of registered actions hooks.
	 *
	 * @since 1.0.0
	 *       
	 * @var array
	 */
	private $actions = array();
	
	/**
	 * The array of registered filters hooks.
	 *
	 * @since 1.0.0
	 *       
	 * @var array
	 */
	private $filters = array();
	
	/**
	 * The array of registered shortcode hooks.
	 *
	 * @since 1.0.0
	 *       
	 * @var array
	 */
	private $shortcodes = array();

	/**
	 * Builds and returns hook key.
	 *
	 * @since 1.0.0
	 *       
	 * @param array $args The hook arguments.
	 * @return string The hook key.
	 */
	private static function get_hook_key( array $args ) {
		return md5( implode( '/', $args ) );
	}

	/**
	 * Registers an action hook.
	 *
	 * @since 1.0.0
	 *       
	 * @uses add_action() To register action hook.
	 *      
	 * @param string $tag The name of the action to which the $method is hooked.
	 * @param string $method The name of the method to be called.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return Trait_Hook The Object.
	 */
	protected function add_action( $tag, $method = '', $priority = 10, $accepted_args = 1 ) {
		$args = func_get_args();
		$this->actions[ self::get_hook_key( $args ) ] = $args;
		
		add_action( $tag, array( $this, ! empty( $method ) ? $method : $tag ), $priority, $accepted_args );
		return $this;
	}

	/**
	 * Removes an action hook.
	 *
	 * @since 1.0.0
	 * @uses remove_action() To remove action hook.
	 *      
	 * @param string $tag The name of the action to which the $method is hooked.
	 * @param string $method The name of the method to be called.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return Trait_Hook
	 */
	protected function remove_action( $tag, $method = '', $priority = 10, $accepted_args = 1 ) {
		remove_action( $tag, array( $this, ! empty( $method ) ? $method : $tag ), $priority, $accepted_args );
		return $this;
	}

	/**
	 * Registers AJAX action hook.
	 *
	 * @since 1.0.0
	 *       
	 * @param string $tag The name of the AJAX action to which the $method is hooked.
	 * @param string $method Optional. The name of the method to be called. If the name of the method is not provided, tag name will be used as method name.
	 * @param boolean $private Optional. Determines if we should register hook for logged in users.
	 * @param boolean $public Optional. Determines if we should register hook for not logged in users.
	 * @return Trait_Hook
	 */
	protected function add_ajax_action( $tag, $method = '', $private = true, $public = false ) {
		if ( $private ) {
			$this->add_action( 'wp_ajax_' . $tag, $method );
		}
		
		if ( $public ) {
			$this->add_action( 'wp_ajax_nopriv_' . $tag, $method );
		}
		
		return $this;
	}

	/**
	 * Removes AJAX action hook.
	 *
	 * @since 1.0.0
	 *       
	 * @param string $tag The name of the AJAX action to which the $method is hooked.
	 * @param string $method Optional. The name of the method to be called. If the name of the method is not provided, tag name will be used as method name.
	 * @param boolean $private Optional. Determines if we should register hook for logged in users.
	 * @param boolean $public Optional. Determines if we should register hook for not logged in users.
	 * @return Trait_Hook
	 */
	protected function remove_ajax_action( $tag, $method = '', $private = true, $public = false ) {
		if ( $private ) {
			$this->remove_action( 'wp_ajax_' . $tag, $method );
		}
		
		if ( $public ) {
			$this->remove_action( 'wp_ajax_nopriv_' . $tag, $method );
		}
		
		return $this;
	}

	/**
	 * Registers a filter hook.
	 *
	 * @since 1.0.0
	 *       
	 * @uses add_filter() To register filter hook.
	 *      
	 * @param string $tag The name of the filter to hook the $method to.
	 * @param string $method The name of the method to be called when the filter is applied.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return Trait_Hook
	 */
	protected function add_filter( $tag, $method = '', $priority = 10, $accepted_args = 1 ) {
		$args = func_get_args();
		$this->filters[ self::get_hook_key( $args ) ] = $args;
		
		add_filter( $tag, array( $this, ! empty( $method ) ? $method : $tag ), $priority, $accepted_args );
		return $this;
	}

	/**
	 * Removes a filter hook.
	 *
	 * @since 1.0.0
	 *       
	 * @uses remove_filter() To remove filter hook.
	 *      
	 * @access protected
	 * @param string $tag The name of the filter to remove the $method to.
	 * @param string $method The name of the method to remove.
	 * @param int $priority optional. The priority of the function (default: 10).
	 * @param int $accepted_args optional. The number of arguments the function accepts (default: 1).
	 * @return Trait_Hook
	 */
	protected function remove_filter( $tag, $method = '', $priority = 10, $accepted_args = 1 ) {
		remove_filter( $tag, array( $this, ! empty( $method ) ? $method : $tag ), $priority, $accepted_args );
		return $this;
	}

	/**
	 * Registers a shortcode hook.
	 *
	 * @since 1.0.0
	 *       
	 * @uses add_shortcode() To register shortcode hook.
	 *      
	 * @param string $tag The name of the shortcode to hook the $method to.
	 * @param string $method The name of the method to be called when the shortcode is applied.
	 * @return Trait_Hook
	 */
	protected function add_shortcode( $tag, $method = '' ) {
		$args = func_get_args();
		$this->shortcodes[ self::get_hook_key( $args ) ] = $args;
		
		add_shortcode( $tag, array( $this, ! empty( $method ) ? $method : $tag ) );
		return $this;
	}

	/**
	 * Removes a shortcode hook.
	 *
	 * @since 1.0.0
	 *       
	 * @uses remove_shortcode() To remove shortcode hook.
	 *      
	 * @access protected
	 * @param string $tag The name of the shortcode to remove the $method to.
	 * @return Trait_Hook
	 */
	protected function remove_shortcode( $tag ) {
		remove_shortcode( $tag );
		return $this;
	}

	/**
	 * Unbinds all hooks previously registered for actions, filters and shortcodes.
	 *
	 * @since 1.0.0
	 *       
	 * @param boolean $actions Optional. TRUE to unbind all actions hooks.
	 * @param boolean $filters Optional. TRUE to unbind all filters hooks.
	 */
	public function unbind( $actions = true, $filters = true, $shortcodes = true ) {
		$types = array();
		
		if ( $actions ) {
			$types[ 'actions' ] = 'remove_action';
		}
		
		if ( $filters ) {
			$types[ 'filters' ] = 'remove_filter';
		}
		
		if ( $shortcodes ) {
			$types[ 'filters' ] = 'remove_shortcode';
		}
		
		foreach ( $types as $hooks => $method ) {
			foreach ( $this->$hooks as $hook ) {
				call_user_func_array( $method, $hook );
			}
		}
	}
}
