<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers;

use WPPluginsDev\WooOrderWorkflow\Controllers\Controller;
use WPPluginsDev\WooOrderWorkflow\Controllers\Controller_Cron;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
use WPPluginsDev\WooOrderWorkflow\Models\Model_WC_Email;
use WPPluginsDev\WooOrderWorkflow\Models\Status\Model_Status_Event;
use WPPluginsDev\Helpers\Helper_Period;

/**
 * Email controller.
 */
class Controller_Email extends Controller {

	public function __construct() {
		$this->add_filter( 'woocommerce_email_classes', 'register_email' );
		$this->add_action( 'wo_model_status_event_create_event', 'trigger_emails' );
		$this->add_action( Controller_Cron::CRON_PROCESS_EVENTS, 'verify_events' );
    }

	/**
	 * Register WC Email class.
	 * @param array $email_classes All active email classes.
	 * @return array $email_classes Custom email class added.
	 */
	public function register_email( $email_classes ) {

		$email_classes['Model_WC_Email'] = new Model_WC_Email();
		return $email_classes;
	}

	/**
	 * Verify if trigger emails. 
	 * Started by cron.
	 */
	public function verify_events() {
		$events = Model_Status_Event::get_events([
			'sent_dt' => '',
		]);
		// Helper_Debug::debug( $events);
		foreach( $events as $event ) {
			$this->trigger_emails( $event );
		}
	}

	/**
	 * Trigger Emails.
	 * 
	 * @param Model_Event $event The event to send email.
	 */
	public function trigger_emails( $event ) {

		// Helper_Debug::debug("trigger_emails");
		$statuses = Model_Status::get_statuses();
		foreach( $statuses as $status ) {
			$settings = $status->email_settings;
			if( $settings->verify_event_condition( $event, $status ) ) {
				if( $settings->enabled ) {
					// Helper_Debug::debug("Event Email: $event->id, TRIGGER $status->slug, id: $status->id  email -- FROM: {$event->get_from_status()->slug}, to: {$event->get_to_status()->slug}, order_id: $event->order_id");
					$this->send_email( $event->order_id, $status );
				}
				$event->sent_dt = Helper_Period::current_date();
				$event->save();
				// Helper_Debug::debug($event);
			}
		}
	}

	/**
	 * Send email for order status.
	 * 
	 * @param int $order_id The order ID.
	 * @param Model_Status $status The order status configuration.
	 */
	public function send_email( $order_id, $status ) {

		$settings = $status->email_settings;

		if( $settings->enabled ) {
			// Helper_Debug::debug("Send Email order_id: $order_id, status_id: $status->id");
			$mailer = WC()->mailer();
			$wc_email = $mailer->emails['Model_WC_Email'];
	
			$wc_email->enabled = 'yes';
			$wc_email->subject = $settings->subject;
			$wc_email->message = $settings->message;
			$wc_email->email_type = $settings->attachments ? 'multipart' : 'html';
			$wc_email->recipient = $settings->recipients;
			$wc_email->attachments = $settings->get_attachment_files();
			// Helper_Debug::debug($wc_email);
			$wc_email->trigger( $order_id );	
		}
	}
}