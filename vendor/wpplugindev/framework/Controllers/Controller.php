<?php
namespace WPPluginsDev\Controllers;
use WPPluginsDev\Traits\Trait_Hook;
use WPPluginsDev\Traits\Trait_Getset;
use WPPluginsDev\Traits\Trait_Request;

class Controller {
	
	use Trait_Hook, Trait_Getset, Trait_Request;

	/**
	 * Capability required to use access metabox.
	 *
	 * @since 1.0.0
	 *       
	 * @var $capability
	 */
	protected $capability = 'manage_options';
	
	/**
	 * Parent constuctor of all controller.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		/**
		 * Actions to execute when constructing the parent controller.
		 *
		 * @since 1.0.0
		 * @param object $this The CA_Controller object.
		 */
		do_action( 'wd_controller_construct', $this );		
	}	
}
