<?php
namespace WPPluginsDev\WooOrderWorkflow\Models;

use WPPluginsDev\WooOrderWorkflow\Traits\Trait_Conditions;
use WPPluginsDev\Models\Model;
use WPPluginsDev\Helpers\Helper_Debug;

/**
 * Email Settins Model.
 */
class Model_EmailSettings extends Model {
	
	use Trait_Conditions;
	
	protected $enabled = false;

	protected $recipients = '';
	
	protected $subject = '';
	
	protected $message = '';
	
	protected $include_order = true;

	protected $conditions = [];
	
	protected $attachments;

	public function __construct() {
		$this->message = __( 'Your Order status has changed', WPPDEV_WO_TXT_DM );
	}

	/**
	 * Get email recipients.
	 * 
	 * @param bool $to_array The flag to return as array or string.
	 * @return array|string The email recipients.
	 */
	public function get_recipients( $to_array = false ) {
		$recipients = $this->recipients;
		if( $to_array ) {
			$recipients = explode( ',', $recipients );
		}
		return $recipients;
	}
	
	/**
	 * Set email recipients.
	 * 
	 * @param array|string $emails The array or comma separated email list.
	 */
	public function set_recipients( $emails ) {
		if( ! empty( $emails ) ) {
			if( ! is_array( $emails ) ) {
				$emails = array_map( 'trim', explode( ',', $emails ) );
			}
			$emails = array_filter( $emails, 'is_email' );
			$emails = implode( ', ', $emails );
		}
		$this->recipients = $emails;
	}

	/**
	 * Set email attachments.
	 * 
	 * @param int[] The arary of attachments IDs.
	 */
	public function set_attachments( $attachments ) {
		if ( is_array( $attachments ) ) {
            $this->attachments = array_map( 'sanitize_text_field', $attachments );
        }
        else {
            $this->attachments = array( sanitize_text_field( $attachments ) );
        }
	}

	/**
	 * Get attachment id files.
	 * 
	 * @return array The attachment files path.
	 */
	public function get_attachment_files() {
		$files = [];
		// Helper_Debug::debug($this->attachments);
		if( isset( $this->attachments['id'] ) ) {
			$files[] = get_attached_file( $this->attachments['id'] );
		}
		elseif( isset( $this->attachments[0] ) ) {
			foreach( $this->attachments as $attach ) {
				$files[] = get_attached_file( $attach['id'] );
			}	
		}
		// Helper_Debug::debug($files);
		return $files;
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
				case 'recipients':
					$this->set_recipients( $value );
					break;
			    case 'subject':
			        $this->$property = sanitize_text_field( $value );
			        break;
				case 'message':
					$this->$property = sanitize_textarea_field( $value );
					break;
				case 'conditions':
					$this->set_conditions( $value );
					break;
				case 'attachments':
					$this->set_attachments( $value );
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
}