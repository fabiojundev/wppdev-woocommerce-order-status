<?php
namespace WPPluginsDev\WooOrderWorkflow\Models;

use WC_Email;
use WooOrderWorkflowPlugin;
use WPPluginsDev\Helpers\Helper_Debug;


if ( ! class_exists('WC_Email') ) {
	Helper_Debug::debug("class not FOUND");
	$file = WP_PLUGIN_DIR . '/woocommerce/includes/emails/class-wc-email.php';
	include_once $file;	
}

/**
 * WooCommerce custom email.
 */
class Model_WC_Email extends WC_Email {
	
	public $email_type = 'html';

	public $message;

	public $attachments;

	public function __construct() {
		parent::__construct();

		$this->id               = 'wo_status_changed_email';
		$this->title            = __( 'Order Workflow', WO_TEXT_DOMAIN );
		$this->customer_email   = false;
		$this->description      = __( 'This email is sent when configured in Order Workflow settings.', WO_TEXT_DOMAIN );
		$this->heading          = __( 'Your order status has changed', WO_TEXT_DOMAIN );
		$this->subject          = __( '[{site_title}] Order #{order_number} status changed', WO_TEXT_DOMAIN );
		$this->message          = __( 'Hi there. Your recent order on {site_title} status has changed.', WO_TEXT_DOMAIN ) . PHP_EOL;
		$this->email_type 		= 'html';
		$this->template_html    = 'emails/wo-status-changed-email.php';
		$this->template_plain   = 'emails/plain/wo-status-changed-email.php';
		$this->template_base = static::get_templates_path();
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', WO_TEXT_DOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', WO_TEXT_DOMAIN ),
				'default' => 'yes',
			),
			'subject' => array(
				'title'       => __( 'Subject', WO_TEXT_DOMAIN ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', WO_TEXT_DOMAIN ), $this->subject ),
				'placeholder' => $this->subject,
				'default'     => '',
				'desc_tip'    => true,
			),
			'heading' => array(
				'title'       => __( 'Email Heading', WO_TEXT_DOMAIN ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email. Leave blank to use the default heading: <code>%s</code>.', WO_TEXT_DOMAIN ), $this->heading ),
				'placeholder' => $this->heading,
				'default'     => '',
				'desc_tip'    => true,
			),
			'message' => array(
				'title'       => __( 'Email Content', WO_TEXT_DOMAIN ),
				'type'        => 'textarea',
				'description' => sprintf( __( 'This controls the initial content of the email. Leave blank to use the default content: <code>%s</code>.', WO_TEXT_DOMAIN ), $this->message ),
				'placeholder' => $this->message,
				'default'     => '',
				'desc_tip'    => true,
			),
			'email_type' => array(
				'title'       => __( 'Email type', WO_TEXT_DOMAIN ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', WO_TEXT_DOMAIN ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_custom_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Email type options.
	 *
	 * @return array
	 */
	protected function get_custom_email_type_options() {
		if ( method_exists( $this, 'get_email_type_options' ) ) {
			return $this->get_email_type_options();
		}

		$types = array( 'plain' => __( 'Plain text', WO_TEXT_DOMAIN ) );

		if ( class_exists( 'DOMDocument' ) ) {
			$types['html']      = __( 'HTML', WO_TEXT_DOMAIN );
			$types['multipart'] = __( 'Multipart', WO_TEXT_DOMAIN );
		}
		else {
			Helper_Debug::debug("DOMDOC NOT EXTS");
		}

		return $types;
	}

	/**
	 * Trigger email.
	 *
	 * @param  int      $order_id      Order ID.
	 * @param  WC_Order $order         Order data.
	 */
	public function trigger( $order_id, $order = false ) {
		// Get the order object while resending emails.
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( is_object( $order ) ) {
			$this->object = $order;

			$this->placeholders['{order_number}'] = $order->get_order_number();
			$this->placeholders['{date}'] = date_i18n( wc_date_format(), time() );
		}

		if ( ! $this->get_recipient() ) {
			return;
		}

		$this->send( 
			$this->get_recipient(), 
			$this->get_subject(), 
			$this->get_content(), 
			$this->get_headers(), 
			$this->get_attachments() 
		);
	}

	/**
	 * Get saved message to send in email body.
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Get content HTML.
	 *
	 * @return string
	 */
	public function get_content_html() {
		ob_start();

		$args = array(
			'order' => $this->object,
			'email_heading' => $this->get_heading(),
			'message' => $this->get_message(),
			'sent_to_admin' => false,
			'plain_text' => false,
			'email' => $this,
		);
		wc_get_template( $this->template_html, $args, '', $this->template_base );

		return ob_get_clean();
	}

	/**
	 * Get content plain text.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();

		$message = $this->get_message();
		$message = str_replace( '<ul>', "\n", $message );
		$message = str_replace( '<li>', "\n - ", $message );
		$message = str_replace( array( '</ul>', '</li>' ), '', $message );

		wc_get_template( $this->template_plain, array(
			'order'            => $this->object,
			'email_heading'    => $this->get_heading(),
			'message' => $message,
			'sent_to_admin'    => false,
			'plain_text'       => true,
			'email'            => $this,
		), '', $this->template_base );

		return ob_get_clean();
	}

	/**
	 * Get custom email template path.
	 */
	public static function get_templates_path() {
		return WooOrderWorkflowPlugin::instance()->dir . 'templates/';
	}
	
	/**
	 * Get email attachments.
	 * 
	 * @return array The attachment path array.
	 */
	public function get_attachments() {
		return $this->attachments;
	}

	/**
	 * Set email attachments.
	 * 
	 * @param array The attachment path array.
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
	 * Set specific property.
	 *
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch( $property ) {
			    case 'recipient':
			    case 'subject':
			    case 'message':
				case 'email_type':
			        $this->$property = sanitize_text_field( $value );
			        break;
				case 'attachments':
					$this->set_attachments( $value );
					break;
				default:
					$this->$property = $value;
					break;
			}
		}
		else {
			$this->$property = $value;
		}
	}
}