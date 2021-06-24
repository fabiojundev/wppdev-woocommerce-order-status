<?php
namespace WPPluginsDev\WooOrderWorkflow\Views\Admin;
use WPPluginsDev\Views\View;
use WPPluginsDev\WooOrderWorkflow\Helpers\Helper_Html;

/**
 * Order Status View.
 */
class View_Admin_Status extends View {

	/**
	 * Data set by controller.
	 *
	 * @var mixed $data
	 */
	protected $data;

	/**
	 * Create view output.
	 *
	 * @return string
	 */
	public function to_html() {
		ob_start();

		$wo_react_app = array(
        	'id' => 'wo-react-app',
        	'type' => Helper_Html::TYPE_HTML_DIV,
        	'value' => '',
        	'data_wo' => $this->data['data_wo'],
        	'class' => 'wo-order-status-grid-wrap',
        );
		
		ob_start();
		?>
		<div class="wrap wo-wrap">
			<?php  Helper_Html::html_element( $wo_react_app ); ?>
		</div>
		<?php
		$html = ob_get_clean();

		return $html;
	}
}