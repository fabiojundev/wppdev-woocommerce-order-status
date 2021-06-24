<?php
namespace WPPluginsDev\Helpers;
/**
 * Html Helper Class.
 *
 * @since 1.0.0
 */
class Helper_Html {

	/* Constants for default HTML input elements. */
	const INPUT_TYPE_HIDDEN = 'hidden';
	const INPUT_TYPE_TEXT = 'text';
	const INPUT_TYPE_NUMBER = 'number';
	const INPUT_TYPE_NUMBER_CONTROL = 'number_control';
	const INPUT_TYPE_FILE = 'file';
	const INPUT_TYPE_PASSWORD = 'password';
	const INPUT_TYPE_TEXT_AREA = 'textarea';
	const INPUT_TYPE_SELECT = 'select';
	const INPUT_TYPE_RADIO = 'radio';
	const INPUT_TYPE_SUBMIT = 'submit';
	const INPUT_TYPE_BUTTON = 'button';
	const INPUT_TYPE_CHECKBOX = 'checkbox';
	const INPUT_TYPE_IMAGE = 'image';
	const INPUT_TYPE_SEARCH = 'search';
	const INPUT_TYPE_EXTRA_BILL = 'extra-bill';

	/* Constants for advanced HTML input elements. */
	const INPUT_TYPE_WP_EDITOR = 'wp_editor';
	const INPUT_TYPE_DATEPICKER = 'datepicker';
	const INPUT_TYPE_RADIO_SLIDER = 'radio_slider';
	const INPUT_TYPE_TAG_SELECT = 'tag_select';

	/* Constants for default HTML elements. */
	const TYPE_HTML_LINK = 'html_link';
	const TYPE_HTML_SEPARATOR = 'html_separator';
	const TYPE_HTML_TEXT = 'html_text';
	const TYPE_HTML_DIV = 'html_div';
	const TYPE_HTML_SPAN = 'html_span';
	const TYPE_HTML_FA_ICON_TEXT = 'html_icon_text';
	const TYPE_HTML_IMAGE = 'html_img';
	const TYPE_LOADING_IMAGE = 'html_loading_img';
	const TYPE_HTML_IMAGE_DELETE = 'html_img_delete';
	const TYPE_HTML_IMAGE_LIGHTBOX = 'html_img_lightbox';
	const TYPE_HTML_VIDEO_DELETE = 'html_video_delete';
	const TYPE_HTML_VIDEO_LIGHTBOX = 'html_video_lightbox';
	
	const UNSET = 'unset';

	/**
	 * Get field args.
	 * 
	 * @since 1.0.0 
	 * 
	 * @param string[] $field_args The html element args.
	 * @return string[] The field args with default values. 
	 */
	public static function get_field_args( $field_args ) {		
		$defaults = array(
				'id'             => '',
				'name'           => '',
				'section'        => '',
				'title'          => '',
				'desc'           => '',
				'value'          => '',
				'cvalue'          => 1, //checkbox value
				'type'           => 'text',
				'class'          => '',
				'maxlength'      => '',
				'equalTo'        => '',
				'field_options' => array(),
				'multiple'      => false,
				'tooltip_output' => '',
		        'disable_auto_complete' => false,
				'alt'           => '',
				'read_only'     => false,
				'placeholder'   => '',
				'data_placeholder' => '',
				'data_attr'       => '',
				'label_element' => 'label',
				// Specific for type 'tag_select':
				'title_selected'  => '',
				'empty_text'  => '',
				'button_text' => '',
				'onclick' => '',
				'disabled' => false,
				'inputmode' => '',
				'autocomplete' => false,
		);
		$field_args = wp_parse_args( $field_args, $defaults );
		extract( $field_args );
		
		if ( empty( $name ) ) {
			if ( ! empty( $section ) ) {
				$name = $section . "[$id]";
			}
			else {
				$name = $id;
			}
		}
		elseif( self::UNSET == $name ) {
			$name = '';
		}
		$field_args['name'] = $name;
		$field_args['attr_placeholder'] = $placeholder ? sprintf( 'placeholder="%s" ', esc_attr( $placeholder ) ) : '';
		$field_args['attr_data_placeholder'] = $data_placeholder ? sprintf( 'data-placeholder="%s" ', esc_attr( $data_placeholder ) ) : '';
		$field_args['data_attr'] = $data_attr ? sprintf( 'data-attr="%s" ', esc_attr( json_encode( $data_attr ) ) ) : '';
		$field_args['max_attr'] = $maxlength ? sprintf( 'maxlength="%s" ', esc_attr( $maxlength ) ) : '';
		$field_args['read_only'] = $read_only ? 'readonly="readonly" ' : '';
		$field_args['multiple'] = $multiple ? 'multiple ': '';
		$field_args['onclick'] = $onclick ? sprintf( 'onclick="%s"', esc_attr( $onclick ) ) : '';
		$field_args['disabled'] = $disabled ? 'disabled' : '';
		$field_args['inputmode'] = $inputmode ? sprintf( 'inputmode="%s" ', esc_attr( $inputmode) ) : '';
		$field_args['autocomplete'] = $autocomplete ? sprintf( 'autocomplete="%s" ', esc_attr( $autocomplete ) ) : '';
		
		return $field_args;
	}
	
	/**
	 * Method for creating HTML elements/fields.
	 *
	 * Pass in array with field arguments. See $defaults for argmuments.
	 * Use constants to specify field type. e.g. Helper_Html::INPUT_TYPE_TEXT
	 *
	 * @since 1.0.0
	 *
	 * @return void|string If $return param is false the HTML will be echo'ed,
	 *           otherwise returned as string
	 */
	public static function html_element( $field_args, $return = false ) {

		$field_args = static::get_field_args( $field_args );
		$type = ! empty( $field_args['type'] ) ? $field_args['type'] : '';
		// Capture to output buffer
		if ( $return ) { 
			ob_start(); 
		}

		switch ( $type ) {

			case static::INPUT_TYPE_HIDDEN:
				static::html_input_hidden( $field_args );
				break;

			case static::INPUT_TYPE_TEXT:
			case static::INPUT_TYPE_PASSWORD:
			case static::INPUT_TYPE_FILE:
			case static::INPUT_TYPE_SEARCH:
				static::html_input_text( $field_args );
				break;

			case static::INPUT_TYPE_NUMBER:
				static::html_input_number( $field_args );
				break;
				
			case static::INPUT_TYPE_NUMBER_CONTROL:
				static::html_input_number_control( $field_args );
				break;
				
			case static::INPUT_TYPE_DATEPICKER:
				static::html_input_datepicker( $field_args );
				break;

			case static::INPUT_TYPE_TEXT_AREA:
				static::html_input_textarea( $field_args );
				break;

			case static::INPUT_TYPE_SELECT:
				static::html_input_select( $field_args );
				break;

			case static::INPUT_TYPE_RADIO:
				static::html_input_radio( $field_args );
				break;

			case static::INPUT_TYPE_CHECKBOX:
				static::html_input_checkbox( $field_args );
				break;

			case static::INPUT_TYPE_WP_EDITOR:
				static::html_input_wpeditor( $field_args );
				break;

			case static::INPUT_TYPE_BUTTON:
				static::html_input_button( $field_args );
				break;

			case static::INPUT_TYPE_SUBMIT:
				static::html_input_submit( $field_args );
				break;

			case static::INPUT_TYPE_IMAGE:
				static::html_input_image( $field_args );
				break;

			case static::INPUT_TYPE_RADIO_SLIDER:
				static::html_input_radio_slider( $field_args );
				break;

			case static::INPUT_TYPE_TAG_SELECT:
				static::html_input_tag_select( $field_args );
				break;

			case static::INPUT_TYPE_EXTRA_BILL:
				static::html_input_extra_bill( $field_args );
				break;
				
			case static::TYPE_HTML_LINK:
				static::html_link( $field_args );
				break;

			case static::TYPE_HTML_SEPARATOR:
				static::html_separator( $field_args );
				break;

			case static::TYPE_HTML_TEXT:
				static::html_text( $field_args );				
				break;
				
			case static::TYPE_HTML_DIV:
				static::html_div( $field_args );
				break;
			
			case static::TYPE_HTML_SPAN:
				static::html_span( $field_args );
				break;
			
			case static::TYPE_HTML_FA_ICON_TEXT:
				static::html_fa_ico_text( $field_args );
				break;
				
			case static::TYPE_HTML_IMAGE:
				static::html_image( $field_args );
				break;
				
			case static::TYPE_LOADING_IMAGE:
				static::html_loading_image( $field_args );
				break;
			
			case static::TYPE_HTML_IMAGE_DELETE:
				static::html_image_delete( $field_args );
				break;
			
			case static::TYPE_HTML_IMAGE_LIGHTBOX:
				static::html_image_lightbox( $field_args );
				break;
				
			case static::TYPE_HTML_VIDEO_DELETE:
				static::html_video_delete( $field_args );
				break;
				
			case static::TYPE_HTML_VIDEO_LIGHTBOX:
				static::html_video_lightbox( $field_args );
				break;
		}

		// Return the output buffer
		if ( $return ) { 
			return ob_get_clean(); 
		}
	}

	/**
	 * Returns HTML code containing options used to build a select tag.
	 *
	 * @since  1.0.0
	 * @param  array $list List items as 'key => value' pairs.
	 * @param  array|string $value The selected value.
	 * @param  string $type Either 'default' or 'taglist'.
	 *
	 * @return string
	 */
	private static function select_options( $list, $value = '', $type = 'default' ) {
		$options = '';

		foreach ( $list as $key => $option ) {
			if ( is_array( $option ) ) {
				if ( empty( $option ) ) { continue; }
				$options .= sprintf(
					'<optgroup label="%1$s">%2$s</optgroup>',
					esc_attr( $key ),
					self::select_options( $option, $value, $type )
				);
			} else {
				if ( is_array( $value ) ) {
					$is_selected = ( in_array( $key, $value ) );
				}
				else {
					$is_selected = $key == $value;
				}

				switch ( $type ) {
					case 'default':
						$attr = selected( $is_selected, true, false );
						$options .= sprintf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $key ),
							$attr,
							$option
						);
						break;

					case 'taglist':
						$attr = ($is_selected ? 'disabled="disabled"' : '');
						$options .= sprintf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $key ),
							$attr,
							$option
						);
						break;
				}
			}
		}

		return $options;
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.0.0
	 */
	private static function html_element_label( $title, $label_element = 'label', $id = '', $tooltip_output = '', $class = '' ) {
		if ( ! empty( $title ) ) {
			printf(
				'<%1$s for="%2$s" class="wd-field-label wd-field-input-label %5$s">%3$s %4$s</%1$s>',
				$label_element,
				esc_attr( $id ),
				$title,
				$tooltip_output,
				esc_attr( $class )
			);
		}
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.0.0
	 */
	private static function html_element_desc( $desc ) {
		if ( $desc != '' ) {
			printf(
				'<span class="wd-field-description">%1$s</span>',
				$desc
			);
		}
	}

	/**
	 * Helper function used by `html_element`
	 *
	 * @since  1.0.0
	 */
	private static function html_element_hint( $title, $tooltip_output ) {
		if ( empty( $title ) ) {
			printf( $tooltip_output );
		}
	}

	/**
	 * Echo the header part of a settings form, including the title and
	 * description.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $args Title, description and breadcrumb infos.
	 */
	public static function settings_header( $args = null ) {
		$defaults = array(
			'title' => '',
			'title_icon_class' => '',
			'desc' => '',
			'bread_crumbs' => null,
		);
		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'ca_helper_html_settings_header_args', $args );
		extract( $args );

		if ( ! is_array( $desc ) ) {
			$desc = array( $desc );
		}

		Helper_Html::bread_crumbs( $bread_crumbs );
		?>
		<h2 class="wd-settings-title">
			<?php if ( ! empty( $title_icon_class ) ) : ?>
				<i class="<?php echo esc_attr( $title_icon_class ); ?>"></i>
			<?php endif; ?>
			<?php printf( $title ); ?>
		</h2>
		<div class="wd-settings-desc-wrapper">
			<?php foreach ( $desc as $description ) : ?>
				<div class="wd-settings-desc wd-description">
					<?php printf( $description ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Echo the footer section of a settings form.
	 *
	 * @since  1.0.0
	 *
	 * @param  null|array $fields List of fields to display in the footer.
	 * @param  bool|array $submit_info What kind of submit button to add.
	 */
	public static function settings_footer( $fields = null, $submit_info = null ) {
		// Default Submit-Button is "Next >>"
		if ( null === $submit_info || true === $submit_info ) {
			$submit_info = array(
				'id' => 'next',
				'value' => __( 'Next', WD_TEXT_DOMAIN ),
				'action' => 'next',
			);
		}

		if ( null === $fields ) {
			$fields = array();
		}

		if ( $submit_info ) {
			$submit_fields = array(
				'next' => array(
					'id' => @$submit_info['id'],
					'type' => Helper_Html::INPUT_TYPE_SUBMIT,
					'value' => @$submit_info['value'],
				),
				'action' => array(
					'id' => 'action',
					'type' => Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => @$submit_info['action'],
				),
				'_canonce' => array(
					'id' => '_canonce',
					'type' => Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => wp_create_nonce( @$submit_info['action'] ),
				),
			);

			foreach ( $submit_fields as $key => $field ) {
				if ( ! isset( $fields[ $key ] ) ) {
					$fields[ $key ] = $field;
				}
			}
		}

		$args = array(
			'saving_text' => __( 'Saving changes...', WD_TEXT_DOMAIN ),
			'saved_text' => __( 'All changes saved.', WD_TEXT_DOMAIN ),
			'error_text' => __( 'Could not save changes.', WD_TEXT_DOMAIN ),
			'fields' => $fields,
		);
		$args = apply_filters( 'ca_helper_html_settings_footer_args', $args );
		$fields = $args['fields'];
		unset( $args['fields'] );

		?>
		<div class="wd-settings-footer">
			<form method="post" action="">
				<?php
				foreach ( $fields as $field ) {
					Helper_Html::html_element( $field );
				}
				self::save_text( $args );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Echo the header section of a settings form.
	 *
	 * @since  1.0.0
	 *
	 * @param  null|array $args The header args.
	 */
	 public static function settings_tab_header( $args = null ) {
		$defaults = array(
			'title' => '',
			'desc' => '',
		);
		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'ca_helper_html_settings_header_args', $args );
		extract( $args );

		if ( ! is_array( $desc ) ) {
			$desc = array( $desc );
		}
		?>
		<div class="wd-header">
			<div class="wd-settings-tab-title">
				<h3><?php printf( $title ); ?></h3>
			</div>
			<div class="wd-settings-description">
				<?php foreach ( $desc as $description ): ?>
					<div class="wd-description">
						<?php printf( $description ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Echo a single content box including the header and footer of the box.
	 * The fields-list will be used to render the box body.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $fields_in List of fields to render
	 * @param  string $title Box title
	 * @param  string $description Description to display
	 * @param  string $state Toggle-state of the box: static/open/closed
	 */
	public static function settings_box( $fields_in, $title = '', $description = '', $state = 'static' ) {
		// If its a fields array, great, if not, make a fields array.
		$fields = $fields_in;
		if ( ! is_array( $fields_in ) ) {
			$fields = array();
			$fields[] = $fields_in;
		}

		self::settings_box_header( $title, $description, $state );
		foreach ( $fields as $field ) {
			Helper_Html::html_element( $field );
		}
		self::save_text();
		self::settings_box_footer();
	}

	/**
	 * Echo the header of a content box. That box has a similar layout to a
	 * normal WordPress meta-box.
	 * The box has a title and description and can optionally be collapsible.
	 *
	 * @since  1.0.0
	 * @param  string $title Box title displayed in the top
	 * @param  string $description Description to display
	 * @param  string $state Toggle-state of the box: static/open/closed
	 */
	public static function settings_box_header( $title = '', $description = '', $state = 'static' ) {
		do_action( 'ca_helper_settings_box_header_init', $title, $description, $state );

		$handle = '';
		if ( $state !== 'static' ) {
			$state = ('closed' === $state ? 'closed' : 'open');
			$handle = sprintf(
				'<div class="handlediv" title="%s"></div>',
				__( 'Click to toggle' ) // Intentionally no text-domain, so we use WordPress default translation.
			);
		}
		$box_class = $state;
		if ( ! strlen( $title ) && ! strlen( $description ) ) {
			$box_class .= ' nohead';
		}

		?>
		<div class="wd-settings-box-wrapper">
			<div class="wd-settings-box <?php echo esc_attr( $box_class ); ?>">
				<div class="wd-header">
					<?php printf( $handle ); ?>
					<?php if ( ! empty( $title ) ) : ?>
						<h3><?php printf( $title ); ?></h3>
					<?php endif; ?>
					<span class="wd-settings-description wd-description"><?php printf( $description ); ?></span>
				</div>
				<div class="inside">
		<?php
		do_action( 'ca_helper_settings_box_header_end', $title, $description, $state );
	}

	/**
	 * Echo the footer of a content box.
	 *
	 * @since  1.0.0
	 */
	public static function settings_box_footer() {
		do_action( 'ca_helper_settings_box_footer_init' );
		?>
		</div> <!-- .inside -->
		</div> <!-- .wd-settings-box -->
		</div> <!-- .wd-settings-box-wrapper -->
		<?php
		do_action( 'ca_helper_settings_box_footer_end' );
	}

	/**
	 * Method for creating submit button.
	 *
	 * Pass in array with field arguments. See $defaults for argmuments.
	 *
	 * @since 1.0.0
	 *
	 * @return void But does output HTML.
	 */
	public static function html_submit( $field_args = array() ) {
		$defaults = array(
			'id'        => 'submit',
			'value'     => __( 'Save Changes', WD_TEXT_DOMAIN ),
			'class'     => 'button button-primary',
			);
		extract( wp_parse_args( $field_args, $defaults ) );

		printf(
			'<input class="wd-field-input wd-submit %1$s" type="submit" id="%2$s" name="%2$s" value="%3$s" />',
			esc_attr( $class ),
			esc_attr( $id ),
			esc_attr( $value )
		);
	}

	/**
	 * Method for outputting tooltips.
	 *
	 * @since 1.0.0
	 *
	 * @return string But does output HTML.
	 */
	public static function tooltip( $tip = '', $return = false ) {
		if ( empty( $tip ) ) {
			return;
		}

		if ( $return ) { ob_start(); }
		?>
		<div class="wd-tooltip-wrapper">
		<div class="wd-tooltip-info"><i class="wd-fa wd-fa-info-circle"></i></div>
		<div class="wd-tooltip">
			<div class="wd-tooltip-button">&times;</div>
			<div class="wd-tooltip-content">
			<?php printf( $tip ); ?>
			</div>
		</div>
		</div>
		<?php
		if ( $return ) { return ob_get_clean(); }
	}

	/**
	 * Echo HTML structure for save-text and animation.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $texts Optionally override the default save-texts.
	 */
	public static function save_text( $texts = array() ) {
		$defaults = array(
			'saving_text' => __( 'Saving changes...', WD_TEXT_DOMAIN ),
			'saved_text' => __( 'All changes saved.', WD_TEXT_DOMAIN ),
			'error_text' => __( 'Could not save changes.', WD_TEXT_DOMAIN ),
		);
		extract( wp_parse_args( $texts, $defaults ) );

		printf(
			'<span class="wd-save-text-wrapper">
				<span class="wd-saving-text"><div class="loading-animation"></div> %1$s</span>
				<span class="wd-saved-text">%2$s</span>
				<span class="wd-error-text">%3$s<span class="err-code"></span></span>
			</span>',
			$saving_text,
			$saved_text,
			$error_text
		);
	}

	/**
	 * Used by the overview views to display a list of available content items.
	 * The items are typically formatted like a taglist via CSS.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Post $item The item to display.
	 * @param  string $tag The tag will be wrapped inside this HTML tag.
	 */
	public static function content_tag( $item, $tag = 'li' ) {
		$label = property_exists( $item, 'post_title' ) ? $item->post_title : $item->name;

		if ( ! empty( $item->id ) && is_a( $item, 'WP_Post' ) ) {
			printf(
				'<%1$s class="wd-content-tag"><a href="%3$s">%2$s</a></%1$s>',
				esc_attr( $tag ),
				esc_html( $label ),
				get_edit_post_link( $item->id )
			);
		}
		else {
			printf(
				'<%1$s class="wd-content-tag"><span>%2$s</span></%1$s>',
				esc_attr( $tag ),
				esc_html( $label )
			);
		}
	}

	public static function bread_crumbs( $bread_crumbs ) {
		$crumbs = array();
		$html = '';

		if ( is_array( $bread_crumbs ) ) {
			foreach ( $bread_crumbs as $key => $bread_crumb ) {
				if ( ! empty( $bread_crumb['url'] ) ) {
					$crumbs[] = sprintf(
						'<span class="wd-bread-crumb-%s"><a href="%s">%s</a></span>',
						esc_attr( $key ),
						esc_url( $bread_crumb['url'] ),
						$bread_crumb['title']
					);
				}
				elseif ( ! empty( $bread_crumb['title'] ) ) {
					$crumbs[] = sprintf(
						'<span class="wd-bread-crumb-%s">%s</span>',
						esc_attr( $key ),
						$bread_crumb['title']
					);
				}
			}

			if ( count( $crumbs ) > 0 ) {
				$html = '<div class="wd-bread-crumb">';
				$html .= implode( '<span class="wd-bread-crumb-sep"> &raquo; </span>', $crumbs );
				$html .= '</div>';
			}
		}
		$html = apply_filters( 'ca_helper_html_bread_crumbs', $html );

		printf( $html );
	}

	public static function period_desc( $period, $class = '' ) {
		$html = sprintf(
			'<span class="wd-period-desc %s"> <span class="wd-period-unit">%s</span> <span class="wd-period-type">%s</span></span>',
			esc_attr( $class ),
			$period['period_unit'],
			$period['period_type']
		);

		return apply_filters( 'ca_helper_html_period_desc', $html );
	}

	public static function html_input_hidden( $field_args ) {
		extract( $field_args );

		printf(
				'<input class="wd-field-input wd-hidden %5$s" type="hidden" id="%1$s" name="%2$s" value="%3$s" %4$s />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				$data_attr,
				esc_attr( $class )
				);
		
	}
	
	public static function html_input_text( $field_args ) {
		extract( $field_args );
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		if( $disable_auto_complete ) {
			$disable_auto_complete = 'autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no"';
		}
		
		printf(
				'<input class="wd-field-input wd-%1$s %2$s" type="%1$s" id="%3$s" name="%4$s" value="%5$s" %6$s />',
				esc_attr( $type ),
				esc_attr( $class ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				$read_only . $max_attr . $attr_placeholder . $data_attr . $disable_auto_complete . $autocomplete . $inputmode
				);

		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_number( $field_args ) {
		extract( $field_args );
		$inputmode = 'inputmode="number"';
		$max = $max ? sprintf( 'max="%s" ', esc_attr( $max ) ) : '';
		$min = $min ? sprintf( 'min="%s" ', esc_attr( $min ) ) : '';
		$step = $step ? sprintf( 'step="%s" ', esc_attr( $step ) ) : '';
		$size = $size ? sprintf( 'size="%s" ', esc_attr( $size ) ) : '';
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		if( $disable_auto_complete ) {
			$disable_auto_complete = 'autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no"';
		}
		
		printf(
				'<input class="wd-field-input wd-%1$s %2$s" type="%1$s" id="%3$s" name="%4$s" value="%5$s" %6$s %7$s />',
				esc_attr( 'number' ),
				esc_attr( $class ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				$read_only . $max_attr . $attr_placeholder . $data_attr . $disable_auto_complete . $autocomplete,
				$inputmode . $max . $min . $step . $size
		);
		
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_number_control( $field_args ) {
		$title = @$field_args['title']; 
		unset( $field_args['title'] );
		unset( $field_args['desc'] );
		?>
		<div class="wd-number-wrap">
    		<div class="wd-attribute-title"><?php echo $title;?></div>
        	<div class="quantity buttons_added">
        		<input type="button" value="-" class="minus">
				<?php static::html_input_number( $field_args );?>
				<input type="button" value="+" class="plus">
			</div>
		</div>
		<?php 
	}
	
	public static function html_input_datepicker( $field_args ) {
		extract( $field_args );
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		printf(
				'<span class="wd-datepicker-wrapper wd-field-input"><input class="wd-datepicker %1$s" type="text" id="%2$s" name="%3$s" value="%4$s" %5$s /><i class="wd-icon wd-fa wd-fa-calendar"></i></span>',
				esc_attr( $class ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				$max_attr . $attr_placeholder . $data_attr
				);
		
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_textarea( $field_args ) {
		extract( $field_args );
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		printf(
				'<textarea class="wd-field-input wd-textarea %1$s" type="text" id="%2$s" name="%3$s" %4$s>%5$s</textarea>',
				esc_attr( $class ),
				esc_attr( $id ),
				esc_attr( $name ),
				$read_only . $attr_placeholder . $data_attr,
				esc_textarea( $value )
				);
		
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_select( $field_args ) {
		extract( $field_args );
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		$options = self::select_options( $field_options, $value );
		
		$name = ( $multiple ) ? $name . '[]' : $name;
		printf(
				'<select id="%1$s" class="wd-field-input wd-select %2$s" name="%3$s" %4$s %6$s>%5$s</select>',
				esc_attr( $id ),
				esc_attr( $class ),
				esc_attr( $name ),
				$multiple . $read_only . $attr_data_placeholder . $data_attr,
				$options,
				$disabled
				);
		
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_radio( $field_args ) {
		extract( $field_args );
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		printf(
				'<div class="wd-radio-wrapper wrapper-%1$s">',
				esc_attr( $id )
				);
		foreach ( $field_options as $key => $option ) {
			if ( is_array( $option ) ) {
				$item_text = $option['text'];
				$item_desc = $option['desc'];
			}
			else {
				$item_text = $option;
				$item_desc = '';
			}
			$checked = checked( $value, $key, false );
			$radio_desc = '';
			if ( ! empty( $item_desc ) ) {
				$radio_desc = sprintf( '<div class="wd-input-description"><p>%1$s</p></div>', $item_desc );
			}
			printf(
					'<div class="wd-radio-input-wrapper %1$s %2$s"><label class="wd-field-input-label"><input class="wd-field-input wd-radio %1$s" type="radio" name="%3$s" id="%4$s_%2$s" value="%2$s" %5$s /><div class="wd-radio-caption">%6$s</div>%7$s</label></div>',
					esc_attr( $class ),
					esc_attr( $key ),
					esc_attr( $name ),
					esc_attr( $id ),
					$data_attr . $checked,
					$item_text,
					$radio_desc
					);
		}
		
		self::html_element_hint( $title, $tooltip_output );
		echo '</div>';
	}
	
	public static function html_input_checkbox( $field_args ) {
		extract( $field_args );
		$checked = checked( $value, true, false );
		
		$item_desc = '';
		if ( ! empty( $desc ) ) {
			$item_desc = sprintf( '<div class="wd-field-description"><p>%1$s</p></div>', $desc );
		}
		
		$item_label = '';
		if ( empty( $field_options['checkbox_position'] ) ||  'left' == $field_options['checkbox_position'] ) {
			$item_label = sprintf(
					'<span class="wd-checkbox-caption %3$s">%1$s %2$s</span>',
					$title,
			        $tooltip_output,
					esc_attr( $class ? $class . '-caption': '' )
					);
		}
		
		printf(
				'<label class="wd-checkbox-wrapper wd-field-input-label %8$s"><input id="%1$s" class="wd-field-input wd-checkbox %2$s" type="checkbox" name="%3$s" value="%7$s" %4$s />%5$s %6$s</label>',
				esc_attr( $id ),
				esc_attr( $class ),
				esc_attr( $name ),
				$data_attr . $checked,
				$item_label,
				$item_desc,
				$cvalue,
				esc_attr( $class ? $class . '-label': '' )
				);
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_wpeditor( $field_args ) {
		extract( $field_args );
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		wp_editor( $value, $id, $field_options );
	}
	
	public static function html_input_button( $field_args ) {
		extract( $field_args );
		
		printf(
				'<button class="wd-field-input button %1$s" type="button" id="%2$s" name="%3$s" %5$s %6$s %7$s>%4$s</button>',
				esc_attr( $class ),
				esc_attr( $id ),
				esc_attr( $name ),
				$value,
				$data_attr,
				$onclick,
				$disabled
				);
		
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_submit( $field_args ) {
		extract( $field_args );
		
		printf(
				'<input class="wd-field-input wd-submit button-primary %1$s" type="submit" id="%2$s" name="%3$s" value="%4$s" %5$s %6$s />',
				esc_attr( $class ),
				esc_attr( $id ),
				esc_attr( $name ),
				$value,
				$data_attr,
				$disabled
				);
		
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_image( $field_args ) {
		extract( $field_args );
		printf(
				'<input type="image" class="wd-field-input wd-input-image %1$s" id="%2$s" name="%3$s" border="0" src="%4$s" alt="%5$s" %6$s/>',
				esc_attr( $class ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_url( $value ),
				esc_attr( $alt ),
				$data_attr
				);
		
		self::html_element_hint( $title, $tooltip_output );
	}
	
	public static function html_input_radio_slider( $field_args ) {
		extract( $field_args );
		
		echo '<div class="wd-radio-slider-wrapper">';
		
		$turned = ( $value ) ? 'on' : '';
		$link_url = ! empty( $url ) ? '<a href="' . esc_url( $url ) . '"></a>' : '';
		
		$attr_input = '';
		if ( ! $read_only ) {
			$attr_input = sprintf(
					'<input class="wd-field-input wd-hidden" type="hidden" id="%1$s" name="%2$s" value="%3$s" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value )
					);
		}
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		printf(
				'<div class="wd-radio-slider %1$s wd-slider-%5$s %7$s" %6$s><div class="wd-toggle" %2$s>%3$s</div>%4$s</div>',
				esc_attr( $turned ),
				$data_attr,
				$link_url,
				$attr_input,
				esc_attr( $id ),
				$read_only,
				esc_attr( $class )
				);
		
		self::html_element_hint( $title, $tooltip_output );
		echo '</div>';
	}

	public static function html_input_tag_select( $field_args ) {
		extract( $field_args );
		
		echo '<div class="wd-tag-selector-wrapper">';
		
		self::html_element_label( $title, $label_element, '_src_' . $id, $tooltip_output );
		self::html_element_desc( $desc );
		
		$options_selected = '';
		$options_available = '<option value=""></option>';
		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}
		
		if ( empty( $field_options ) ) {
			// No values available, display a note instead of the input elements.
			printf(
			'<div id="%1$s" class="wd-no-data wd-field-input %2$s">%3$s</div>',
			esc_attr( $id ),
			esc_attr( $class ),
			$empty_text
			);
		} else {
			// There are values to select or remove. Display the input elements.
			$options_selected .= self::select_options( $field_options, $value );
			$options_available .= self::select_options( $field_options, $value, 'taglist' );
			
			// First Select: The value selected here can be added to the tag-list.
			printf(
			'<select id="_src_%1$s" class="wd-field-input wd-tag-source %2$s" %4$s>%5$s</select>',
			esc_attr( $id ),
			esc_attr( $class ),
			esc_attr( $name ),
			$multiple . $read_only . $attr_data_placeholder,
			$options_available
			);
			
			// Button: Add element from First Select to Second Select.
			printf(
			'<button id="_src_add_%1$s" class="wd-field-input wd-tag-button button %2$s" type="button">%3$s</button>',
			esc_attr( $id ),
			esc_attr( $class ),
			$button_text
			);
			
			self::html_element_label( $title_selected, $label_element, $id, '', 'wd-tag-label' );
			
			// Second Select: The actual tag-list
			printf(
			'<select id="%1$s" class="wd-field-input wd-select wd-tag-data %2$s" multiple="multiple" readonly="readonly" %4$s>%5$s</select>',
			esc_attr( $id ),
			esc_attr( $class ) . ( ! empty( $data_attr ) ? ' wd-ajax-update' : ''),
			esc_attr( $name ),
			$data_attr,
			$options_selected
			);
		}
		
		self::html_element_hint( $title, $tooltip_output );
		echo '</div>';
	}
	
	public static function html_input_extra_bill( $field_args ) {

		echo "<div id='extra-bill-wrapper' class='{$field_args['class']}'>";
		
		if( empty( $field_args['value'] ) ) {
			$field_args['value'] = array( 0 => array( 'amount' => 0, 'desc' => '' ) );
		}

		foreach( $field_args['value'] as $key => $bill ) {
			echo "<div class='extra-bill-wrap {$field_args['class']}' id='extra-bill-wrap-$key'>";
			$args = $field_args;
			$args['type'] = self::INPUT_TYPE_TEXT;
			$args['id'] = "wd-bill-val-$key";
			$args['name'] = sprintf( '%s[%s][amount]', $field_args['id'], $key );
			$args['value'] = isset( $bill['amount'] ) ? $bill['amount'] : 0;
			self::html_input_text( $args );
			$args['id'] = "wd-bill-desc-$key";
			$args['title'] = __( 'Descrição', WD_TEXT_DOMAIN );
			$args['name'] = sprintf( '%s[%s][desc]', $field_args['id'], $key );
			$args['value'] = isset( $bill['desc'] ) ? $bill['desc'] : '';
			self::html_input_text( $args );
			
			$args['id'] = "wd-bill-delete-$key";
			$args['name'] = sprintf( '%s[%s][amount]', $field_args['id'], $key );
			$args['value'] = __( 'Excluir', WD_TEXT_DOMAIN );
			$args['class'] = "wd-bill-delete";
			$args['url'] = '#';
			self::html_input_button( $args );
			echo '</div>';
		}

		echo "</div>";
	}

	public static function html_link( $field_args ) {
		extract( $field_args );
		if ( empty( $title ) ) { 
			$title = $value; 
		}
		
		printf(
				'<a id="%1$s" title="%2$s" class="wd-link %3$s" href="%4$s" %5$s %7$s>%6$s</a>',
				esc_attr( $id ),
				esc_attr( $title ),
				esc_attr( $class ),
				esc_url( $url ),
				$data_attr,
				$value,
				$disabled
				);
		
	}
	
	public static function html_separator( $field_args ) {
		extract( $field_args );
		
		if ( $value != 'vertical' ) { 
			$value = 'horizontal'; 
		}
		
		if ( 'vertical' === $value ) {
			printf( '<div id="%s" class="wd-divider"></div>', esc_attr( $id ) );
		} 
		else {
			printf( '<div id="%s" class="wd-separator"></div>', esc_attr( $id ) );
		}
	}
	
	public static function html_text( $field_args ) {
		extract( $field_args );
		
		if ( empty( $wrapper ) ) { 
			$wrapper = 'span'; 
		}

		printf('<div class="wd-html-text-wrapper %s-wrapper">', esc_attr( $class ) );
		
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		
		printf(
				'<%1$s class="%2$s">%3$s</%1$s>',
				esc_attr( $wrapper ),
				esc_attr( $class ),
				$value
				);
		
		self::html_element_hint( $title, $tooltip_output );
		echo '</div>';
	}
	
	public static function html_div( $field_args ) {
		extract( $field_args );
		
		printf( '<div id=%s class="%s" %s>%s</div>',
				esc_attr( $id ),
				esc_attr( $class ),
				$data_attr,
				$value
				);
	}
	
	public static function html_span( $field_args ) {
		extract( $field_args );
		
		printf( '<span id=%s class="%s" %s>%s</span>',
				esc_attr( $id ),
				esc_attr( $class ),
				$data_attr,
				$value
				);
	}
	
	public static function html_fa_ico_text( $field_args ) {
		extract( $field_args );
		
		if ( empty( $wrapper ) ) { 
			$wrapper = 'span'; 
		}
		echo "<div class='$class-wrapper'>";
		
		$fa_icon = sprintf( '<i class="fa %s" aria-hidden="true"></i>', esc_attr( $fa_icon ) );
		
		printf(
				'<div id="%7$s" class="%2$s" %6$s><div class="dp-icon">%5$s</div><div><%1$s class="dp-value">%3$s</%1$s><%1$s class="dp-title">%4$s</%1$s></div></div>',
				esc_attr( $wrapper ),
				esc_attr( $class ),
				$value,
				$title,
				$fa_icon,
				$data_attr,
				esc_attr( $id )
				);
		
		self::html_element_hint( $title, $tooltip_output );
		echo '</div>';
	}
	
	public static function html_image( $field_args ) {
		extract( $field_args );

		printf(
				'<img src="%s" alt="%s" id="%s" class="%s">',
				esc_url( $value ),
				esc_attr( $alt ),
				esc_attr( $id ),
				esc_attr( $class )
				);
	}

	public static function html_loading_image( $field_args ) {
		extract( $field_args );
		
		printf( '<div class="%s">',  esc_attr( $class ) );
		echo '<i class="fa fa-spinner fa-pulse fa-8x fa-fw"></i>';
		echo '</div>';

	}
	
	public static function html_image_delete( $field_args ) {
		extract( $field_args );
// 		Helper_Debug::log($field_args);
		printf( '<div class="%s-wrap">', esc_attr( $class ) );
		self::html_element_label( $title, $label_element, $id, $tooltip_output );
		printf(
				'<img src="%1$s" alt="%2$s" id="%3$s" class="%4$s"><span title="%6$s" class="dashicons dashicons-no wd-delete %4$s-delete-ico" %5$s></span>',
				esc_url( $value ),
				esc_attr( $alt ),
				esc_attr( $id ),
				esc_attr( $class ),
				$data_attr,
				__( 'Excluir', WD_TEXT_DOMAIN )
				);
		
		if( isset( $legend ) ) {
			printf(
					'<input class="wd-field-input wd-%1$s %2$s" type="%1$s" id="%3$s" name="%4$s" value="%5$s" %6$s />',
					esc_attr( 'text' ),
					esc_attr( "$class-edit-legend" ),
					esc_attr( "$id-legend" ),
					esc_attr( "legends[$image_id]" ),
					esc_attr( $legend ),
					$read_only . $max_attr . $attr_placeholder . $data_attr . $disable_auto_complete . $attr_data_placeholder
					);
		}
		echo '</div>';
	}
	
	public static function html_image_lightbox( $field_args ) {
		extract( $field_args );
		
		printf(
				'<div class="%9$s">%8$s</div><a href="%7$s" data-rel="%2$s" data-title="%3$s" class="wd-img-lightbox"><img src="%1$s" alt="%4$s" id="%5$s" class="%6$s"></a>',
				esc_url( $value ),
				esc_attr( $data_rel ),
				esc_attr( $title ),
				esc_attr( $alt ),
				esc_attr( $id ),
				esc_attr( $class ),
				esc_attr( $url ),
				esc_attr( $legend ),
				esc_attr( "$class-legend" )
				);
	}
	
	public static function html_video_delete( $field_args ) {
		extract( $field_args );
		
		printf( '<div class="%s-wrap"><video controls class="%s">', esc_attr( $class ), esc_attr( $class ) );
		printf('<source src="%1$s" alt="%2$s" id="%3$s" class="%4$s">',
				esc_url( $value ),
				esc_attr( $alt ),
				esc_attr( $id ),
				esc_attr( $class )
				);
		
		printf( '</video><span class="dashicons dashicons-dismiss wd-delete" %s></span>',
				esc_attr( $class ),
				$data_attr
				);
		if( isset( $legend ) ) {
			printf(
					'<input class="wd-field-input wd-%1$s %2$s" type="%1$s" id="%3$s" name="%4$s" value="%5$s" %6$s />',
					esc_attr( 'text' ),
					esc_attr( "$class-edit-legend" ),
					esc_attr( "$id-legend" ),
					esc_attr( "legends[$video_id]" ),
					esc_attr( $legend ),
					$read_only . $max_attr . $attr_placeholder . $data_attr . $disable_auto_complete . $attr_data_placeholder
					);
		}
		echo '</div>';
		
	}

	public static function html_video_lightbox( $field_args ) {
		extract( $field_args );
		
		printf(
				'<div class="%8$s">%7$s</div><a href="%1$s" data-rel="%2$s" data-title="%3$s" class="wd-img-lightbox"><video class="%6$s" controls><source src="%1$s" alt="%4$s" id="%5$s" class="%6$s"></video></a>',
				esc_url( $value ),
				esc_attr( $data_rel ),
				esc_attr( $title ),
				esc_attr( $alt ),
				esc_attr( $id ),
				esc_attr( $class ),
				esc_attr( $legend ),
				esc_attr( "$class-legend" )
				);
	}

}