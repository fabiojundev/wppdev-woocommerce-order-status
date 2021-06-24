<?php
namespace WPPluginsDev\Views;
use WPPluginsDev\Traits\Trait_Getset;
use WPPluginsDev\Helpers\Helper_Html;

class View {

	use Trait_Getset;

	/**
	 * The storage of all data associated with this render.
	 *
	 * @since 1.0.0
	 *       
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *       
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array() ) {
		$this->data = $data;
		
		/**
		 * Actions to execute when constructing the parent View.
		 *
		 * @since 1.0.0
		 * @param object $this The View object.
		 */
		do_action( 'wppdev_view_construct', $this );
	}

	/**
	 * Builds template and return it as string.
	 *
	 * @since 1.0.0
	 *       
	 * @return string
	 */
	public function to_html() {
		/* This function is implemented different in each child class. */
		return apply_filters( 'wppdev_view_to_html', '' );
	}

	public function to_clean_html() {
		return preg_replace('/\s+/S', " ", $this->to_html() );
	}

	/**
	 * Renders the template.
	 *
	 * @since 1.0.0
	 */
	public function render() {
		$html = $this->to_html();
		
		echo apply_filters( 'wppdev_view_render', $html );
	}

	public function loading_element() {
		Helper_Html::html_element( [
				'id' => 'vp-loading',
				'type' => Helper_Html::TYPE_LOADING_IMAGE,
				'class' => 'vp-loading',
			] );
	}
}