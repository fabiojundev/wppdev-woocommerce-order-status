<?php
namespace WPPluginsDev\WooOrderWorkflow\Controllers\Admin;

use WPPluginsDev\WooOrderWorkflow\Controllers\Controller;
use WPPluginsDev\WooOrderWorkflow\Helpers\Helper_Html;
use WPPluginsDev\Helpers\Helper_Debug;
use WPPluginsDev\WooOrderWorkflow\Models\Model_Status;
/**
 * Woocommerce Orders Admin.
 * 
 */
class Controller_Admin_Order extends Controller {

	public function __construct() {
    	$this->add_filter( 'bulk_actions-edit-shop_order', 'define_order_bulk_actions', 999 );
		$this->add_action( 'admin_head',  'print_order_status_css' );

		$this->add_action( 'woocommerce_admin_order_actions_start',  'actions_column_btns' );

		$this->add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );
		$this->add_action( 'admin_enqueue_scripts', 'enqueue_styles' );
    }
    
    /**
     * Add custom bulk actions in WC admin products list.
	 * 
     * @param array $actions The existing actions.
     * @return array The filtered actions.
     */
    public function define_order_bulk_actions( $actions ) {
    	
    	$statuses = Model_Status::get_custom_statuses();
    	foreach ( $statuses as $status ) {
			if( $status->enabled_in_bulk_actions ) {
				$action = 'mark_' . $status->get_slug();
				$actions[ $action ] = esc_html( sprintf( 
					'%s %s', __( 'Change status to', 'wppdev-woocommerce-order-status' ), 
					$status->name 
				) );
			}
    	}

    	return $actions;
    }

	/**
	 * Print order status css.
	 * Style for status background and font colors.
	 */
	public function print_order_status_css() {
		global $typenow;

    	$statuses = Model_Status::get_custom_statuses();
		if( 'shop_order' == $typenow && ! empty( $statuses ) ): ?>
			<style>
			<?php foreach ( $statuses as $status ):
				$slug = $status->get_slug();
				$color = $status->color;
				$background = $status->background;
			?>
				mark.status-<?php echo esc_attr( $slug );?> {
					color:<?php echo esc_attr( $color );?>;
					background-color:<?php echo esc_attr( $background );?>;
				}
			<?php endforeach; ?>
			</style>
		<?php endif;
	}

	/**
	 * Add action button in admin products list column.
	 * Add configured order status butons.
	 */
	public function actions_column_btns( $order ) {
		$statuses = Model_Status::get_statuses();
		foreach( $statuses as $status ) {
			$slug = $status->get_slug();
			if( Model_Status::TYPE_CORE == $status->type && 'processing' != $slug ) {
				continue;
			}
			if( $order->get_status() == $slug && $status->next_statuses ) {
				foreach( $status->next_statuses as $next ) {
					$next_status = Model_Status::load( $next );
					Helper_Html::html_element([
						'id' => 'wo-status-' . $next_status->slug,
						'type' => Helper_Html::TYPE_HTML_LINK,
						'title' => $next_status->name,
						'url' => wp_nonce_url( 
							admin_url( sprintf( 
									'admin-ajax.php?action=woocommerce_mark_order_status&status=%s&order_id=%s',
									$next_status->slug,
									$order->get_id() 
							) ), 
							'woocommerce-mark-order-status' 
						),
						'action' => $next_status->slug,
						'class' => sprintf( 
							'wo-status-icon button wc-action-button wc-action-button-%1$s %1$s', 
							$next_status->slug 
						),
						'data_wo' => [
							'icon' => $next_status->icon,
							'color' => $next_status->color,
							'background' => $next_status->background,
						],
					]);
				}
			}
		}
	}

	/**
	 * Enqueue scripts used in admin order page.
	 */
	public function enqueue_scripts() {
		global $typenow;

		if( 'shop_order' == $typenow ) {
			wp_enqueue_script( 'wo-order-admin' );
		}    	
    }

	/**
	 * Enqueue styles used in admin order page.
	 */
	public function enqueue_styles() {
		global $typenow;

		if( 'shop_order' == $typenow ) {
	    	wp_enqueue_style( 'wo-order-admin' );
		}
    }

}