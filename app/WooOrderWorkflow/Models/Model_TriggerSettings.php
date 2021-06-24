<?php
namespace WPPluginsDev\WooOrderWorkflow\Models;
use WPPluginsDev\Models\Model;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\WooOrderWorkflow\Traits\Trait_Conditions;

/**
 * Trigger Settings Model.
 */
class Model_TriggerSettings extends Model {

	use Trait_Conditions;

	const TRIGGER_TYPE_CHANGE_STATUS = 'trigger_change_status';
	const TRIGGER_TYPE_RESEND_INVOICE = 'trigger_resend_invoice';
	
	protected $trigger_type = '';
	
	protected $to_status = '';
	
	protected $to_emails = [];
	
	protected $include_order = false;
	
	protected $conditions = [];

	/**
	 * Get trigger types.
	 * 
	 * @return array The label and value types array.
	 */
	public function get_trigger_types() {
		return [
			[
				'label' => __( 'Select', WPPDEV_WO_TXT_DM ),
				'value' => '',
			],
			[
				'label' => __( 'Change Status', WPPDEV_WO_TXT_DM ),
				'value' => self::TRIGGER_TYPE_CHANGE_STATUS,
			],
			[
				'label' => __( 'Resend Invoice', WPPDEV_WO_TXT_DM ),
				'value' => self::TRIGGER_TYPE_RESEND_INVOICE,
			],
		];
	}

	/**
	 * Trigger actions for an event.
	 * 
	 * @param Model_Status_Event The event to trigger action.
	 */
	public function trigger_actions( $event ) {
		switch( $this->trigger_type ) {
			case self::TRIGGER_TYPE_CHANGE_STATUS:
				$this->trigger_change_status( $event );
				break;
			case self::TRIGGER_TYPE_RESEND_INVOICE:
				$this->trigger_resend_invoice( $event );
				break;
	
		}
	}

	/**
	 * Trigger change actions for an event.
	 * 
	 * @param Model_Status_Event The event to trigger action.
	 */
	public function trigger_change_status( $event ) {
		$order = $event->get_order();
		$to_status = $event->get_to_status();
		$res = $order->update_status( 
			$to_status->slug, 
			__( 'Order Status automatically changed - Woocommerce Order Workflow', WPPDEV_WO_TXT_DM ) 
		);
		Helper_Debug::debug("Order Status automatically changed - Woocommerce Order Workflow to {$to_status->slug}");
		Helper_Debug::debug($res);
		
	}

	/**
	 * Trigger resend invoice actions for an event.
	 * 
	 * @todo implement this method.
	 * @param Model_Status_Event The event to trigger action.
	 */
	public function trigger_resend_invoice( $event ) {
		$order = $event->get_order();

		$mailer = WC()->mailer();
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
			    case 'name':
			    case 'to':
			    case 'from':
			        $this->$property = sanitize_text_field( $value );
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