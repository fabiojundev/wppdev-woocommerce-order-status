<?php
/**
 * Cancelled Order sent to Customer.
 */

use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
use WPPluginsDev\Helpers\Helper_Debug;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p>
	<?php esc_html( printf( '%s #%s %s %s',
		__( 'Just to let you know - your order', WO_TEXT_DOMAIN ), 
		$order->get_order_number(),
		__( 'status has changed to', WO_TEXT_DOMAIN ), 
		trim( $order->get_status() )
		) ); 
	?>
	</p>
	<p>
		<?php echo wpautop( $message ); ?>
	</p>
<?php

$order_status = $order->get_status();
$wo_status = Model_Status::load_by_field( 'slug', $order_status );
if( $wo_status->email_settings->include_order ) {
	/**
	 * @hooked WC_Emails::order_details() Shows the order details table.
	 * @hooked WC_Emails::order_schema_markup() Adds Schema.org markup.
	 * @since 2.5.0
	 */
	do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
	/**
	 * @hooked WC_Emails::order_meta() Shows order meta data.
	 */
	do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
	/**
	 * @hooked WC_Emails::customer_details() Shows customer details
	 * @hooked WC_Emails::email_address() Shows email address
	 */
	do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
}

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );