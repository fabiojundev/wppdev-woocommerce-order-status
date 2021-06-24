<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers;
use WPPluginsDev\Controllers\Controller as PController;

/**
 * Base Controller.
 * Modify default nonce field.
 */
class Controller extends PController{
	
    public $nonce_field = '_wononce';
}
