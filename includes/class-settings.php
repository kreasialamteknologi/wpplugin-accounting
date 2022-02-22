<?php
/**
 * EverAccounting Settings.
 *
 * @since       1.0.2
 * @subpackage  Classes
 * @package     EverAccounting
 */

namespace EverAccounting;

defined( 'ABSPATH' ) || exit();

/**
 * Class Settings
 *
 * @since   1.0.2
 * @package EverAccounting\Admin
 */
class Settings {
	/**
	 * Stores all settings.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Settings constructor.
	 *
	 */
	public function __construct() {
		$this->settings = (array) get_option( 'eaccounting_settings', array() );

		// Set up.
		add_filter( 'option_page_capability_eaccounting_settings', array( $this, 'option_page_capability' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'eaccounting_settings_sanitize_text', 'sanitize_text_field' );
		add_filter( 'eaccounting_settings_sanitize_url', 'wp_http_validate_url' );
		//add_filter( 'eaccounting_settings_sanitize_checkbox', 'eaccounting_bool_to_string' );
		add_filter( 'eaccounting_settings_sanitize_number', 'absint' );
		add_filter( 'eaccounting_settings_sanitize_rich_editor', 'wp_kses_post' );
	}

	/**
	 * Let manager edit settings.
	 *
	 * @param $capability
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function option_page_capability( $capability ) {
		return 'ea_manage_options';
	}

	/**
	 * Retrieve the array of plugin settings
	 *
	 * @return array
	 * @since 1.0.2
	 */
	function get_registered_settings() {
		$settings = array(
			/** General Settings */
			'general'  => apply_filters(
				'eaccounting_settings_general',
				array(
					'main'     => array(
						array(
							'id'   => 'company_settings',
							'name' => __( 'Company Settings', 'wp-ever-accounting' ),
							'desc' => '',
							'type' => 'header',
						),
						array(
							'id'          => 'company_name',
							'name'        => __( 'Name', 'wp-ever-accounting' ),
							'type'        => 'text',
							'required'    => 'required',
							'placeholder' => __( 'XYZ Company', 'wp-ever-accounting' ),
						),
						array(
							'id'                => 'company_email',
							'name'              => __( 'Email', 'wp-ever-accounting' ),
							'type'              => 'email',
							'std'               => get_option( 'admin_email' ),
							'sanitize_callback' => 'sanitize_email',
						),
						array(
							'id'   => 'company_phone',
							'name' => __( 'Phone Number', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'company_vat_number',
							'name' => __( 'VAT Number', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'company_address',
							'name' => __( 'Street', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'company_city',
							'name' => __( 'City', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'company_state',
							'name' => __( 'State', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'company_postcode',
							'name' => __( 'Postcode', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'          => 'company_country',
							'name'        => __( 'Country', 'wp-ever-accounting' ),
							'type'        => 'select',
							'input_class' => 'ea-select2',
							'options'     => array( '' => __( 'Select Country', 'wp-ever-accounting' ) ) + eaccounting_get_countries(),
						),
						array(
							'id'   => 'company_logo',
							'name' => __( 'Logo', 'wp-ever-accounting' ),
							'type' => 'upload',
						),
						array(
							'id'   => 'general_settings',
							'name' => __( 'General Settings', 'wp-ever-accounting' ),
							'desc' => '',
							'type' => 'header',
						),
						array(
							'id'    => 'financial_year_start',
							'name'  => __( 'Financial Year Start', 'wp-ever-accounting' ),
							'tip'   => __( 'Enter the company financial start date.', 'wp-ever-accounting' ),
							'std'   => '01-01',
							'class' => 'ea-financial-start',
							'type'  => 'text',
						),
						array(
							'id'   => 'tax_enabled',
							'name' => __( 'Enable taxes', 'wp-ever-accounting' ),
							'desc' => 'Enable tax rates and calculations',
							'type' => 'checkbox',
						),
						array(
							'id'   => 'dashboard_transactions_limit',
							'name' => __( 'Total Transactions', 'wp-ever-accounting' ),
							'type' => 'checkbox',
							'desc' => 'Limit dashboard total transactions to current financial year.',
						),
						array(
							'id'   => 'local_settings',
							'name' => __( 'Default Settings', 'wp-ever-accounting' ),
							'desc' => '',
							'type' => 'header',
						),
						array(
							'id'          => 'default_account',
							'name'        => __( 'Account', 'wp-ever-accounting' ),
							'type'        => 'select',
							'tip'         => __( 'Default account will be used for automatic transactions.', 'wp-ever-accounting' ),
							'input_class' => 'ea-select2',
							'options'     => $this->get_accounts(),
						),
						array(
							'id'          => 'default_currency',
							'name'        => __( 'Currency', 'wp-ever-accounting' ),
							'type'        => 'select',
							'tip'         => __( 'Default currency rate will update to 1.', 'wp-ever-accounting' ),
							'input_class' => 'ea-select2',
							'options'     => $this->get_currencies(),
						),
						array(
							'id'          => 'default_payment_method',
							'name'        => __( 'Payment Method', 'wp-ever-accounting' ),
							'std'         => 'cash',
							'type'        => 'select',
							'tip'         => __( 'Default currency rate will update to 1.', 'wp-ever-accounting' ),
							'input_class' => 'ea-select2',
							'options'     => eaccounting_get_payment_methods(),
						),
					),
					'invoices' => array(
						array(
							'id'   => 'invoice_prefix',
							'name' => __( 'Invoice Prefix', 'wp-ever-accounting' ),
							'std'  => 'INV-',
							'type' => 'text',
						),
						array(
							'id'   => 'invoice_digit',
							'name' => __( 'Minimum Digits', 'wp-ever-accounting' ),
							'std'  => '5',
							'type' => 'number',
						),
						array(
							'id'   => 'invoice_terms',
							'name' => __( 'Invoice Terms', 'wp-ever-accounting' ),
							'std'  => '',
							'type' => 'textarea',
						),
						array(
							'id'   => 'invoice_note',
							'name' => __( 'Invoice Note', 'wp-ever-accounting' ),
							'std'  => '',
							'type' => 'textarea',
						),
						array(
							'id'      => 'invoice_due',
							'name'    => __( 'Invoice Due', 'wp-ever-accounting' ),
							'std'     => '15',
							'type'    => 'select',
							'options' => array(
								'7'  => __( 'Due within 7 days', 'wp-ever-accounting' ),
								'15' => __( 'Due within 15 days', 'wp-ever-accounting' ),
								'30' => __( 'Due within 30 days', 'wp-ever-accounting' ),
								'45' => __( 'Due within 45 days', 'wp-ever-accounting' ),
								'60' => __( 'Due within 60 days', 'wp-ever-accounting' ),
								'90' => __( 'Due within 90 days', 'wp-ever-accounting' ),
							),
						),
						array(
							'id'   => 'invoice_item_label',
							'name' => __( 'Item Label', 'wp-ever-accounting' ),
							'std'  => __( 'Item', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'invoice_price_label',
							'name' => __( 'Price Label', 'wp-ever-accounting' ),
							'std'  => __( 'Price', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'invoice_quantity_label',
							'name' => __( 'Quantity Label', 'wp-ever-accounting' ),
							'std'  => __( 'Quantity', 'wp-ever-accounting' ),
							'type' => 'text',
						),
					),
					'bills'    => array(
						array(
							'id'   => 'bill_prefix',
							'name' => __( 'Bill Prefix', 'wp-ever-accounting' ),
							'std'  => 'BILL-',
							'type' => 'text',
						),
						array(
							'id'   => 'bill_digit',
							'name' => __( 'Bill Digits', 'wp-ever-accounting' ),
							'std'  => '5',
							'type' => 'number',
						),
						array(
							'id'   => 'bill_terms',
							'name' => __( 'Bill Terms & Conditions', 'wp-ever-accounting' ),
							'std'  => '',
							'type' => 'textarea',
						),
						array(
							'id'   => 'bill_note',
							'name' => __( 'Bill Note', 'wp-ever-accounting' ),
							'std'  => '',
							'type' => 'textarea',
						),
						array(
							'id'      => 'bill_due',
							'name'    => __( 'Bill Due', 'wp-ever-accounting' ),
							'std'     => '15',
							'type'    => 'select',
							'options' => array(
								'7'  => __( 'Due within 7 days', 'wp-ever-accounting' ),
								'15' => __( 'Due within 15 days', 'wp-ever-accounting' ),
								'30' => __( 'Due within 30 days', 'wp-ever-accounting' ),
								'45' => __( 'Due within 45 days', 'wp-ever-accounting' ),
								'60' => __( 'Due within 60 days', 'wp-ever-accounting' ),
								'90' => __( 'Due within 90 days', 'wp-ever-accounting' ),
							),
						),
						array(
							'id'   => 'bill_item_label',
							'name' => __( 'Item Label', 'wp-ever-accounting' ),
							'std'  => __( 'Item', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'bill_price_label',
							'name' => __( 'Price Label', 'wp-ever-accounting' ),
							'std'  => __( 'Price', 'wp-ever-accounting' ),
							'type' => 'text',
						),
						array(
							'id'   => 'bill_quantity_label',
							'name' => __( 'Quantity Label', 'wp-ever-accounting' ),
							'std'  => __( 'Quantity', 'wp-ever-accounting' ),
							'type' => 'text',
						),
					),
				)
			),
			'licenses' => array(
				'main' => apply_filters( 'eaccounting_settings_licenses', array() ),
			),
		);

		if ( eaccounting_tax_enabled() ) {
			$settings['general']['taxes'] = array(
				array(
					'id'   => 'tax_subtotal_rounding',
					'name' => __( 'Rounding', 'wp-ever-accounting' ),
					'type' => 'checkbox',
					'desc' => __( 'Round tax at subtotal level, instead of rounding per tax rate.', 'wp-ever-accounting' ),
				),
				array(
					'id'      => 'prices_include_tax',
					'name'    => __( 'Prices entered with tax', 'wp-ever-accounting' ),
					'type'    => 'select',
					'std'     => 'yes',
					'options' => array(
						'yes' => __( 'Yes, I will enter prices inclusive of tax', 'wp-ever-accounting' ),
						'no'  => __( 'No, I will enter prices exclusive of tax', 'wp-ever-accounting' ),
					),
				),
				array(
					'id'      => 'tax_display_totals',
					'name'    => __( 'Display tax totals	', 'wp-ever-accounting' ),
					'type'    => 'select',
					'std'     => 'total',
					'options' => array(
						'total'      => __( 'As a single total', 'wp-ever-accounting' ),
						'individual' => __( 'As individual tax rates', 'wp-ever-accounting' ),
					),
				),
			);
		}

		/**
		 * Filters the entire default settings array.
		 * add_filter( 'eaccounting_settings', function( $settings ){
		 *
		 * } )
		 *
		 * @param array $settings Array of default settings.
		 *
		 * @since 1.0.2
		 *
		 */
		return apply_filters( 'eaccounting_settings', $settings );
	}

	/**
	 * Add all settings sections and fields
	 *
	 * @return void
	 * @since 1.0.2
	 */
	function register_settings() {
		$whitelisted = array();
		foreach ( $this->get_registered_settings() as $tab => $sections ) {
			if ( ! is_array( $sections ) ) {
				continue;
			}
			foreach ( $sections as $section => $settings ) {

				add_settings_section(
					'eaccounting_settings_' . $tab . '_' . $section,
					__return_null(),
					'__return_false',
					'eaccounting_settings_' . $tab . '_' . $section
				);

				foreach ( $settings as $option ) {
					// Without option not allowed.
					if ( empty( $option['id'] ) ) {
						continue;
					}

					// Restrict duplicate.
					if ( in_array( $option['id'], $whitelisted, true ) ) {
						eaccounting_doing_it_wrong(
							__METHOD__,
							sprintf(
							/* translators: %s settings id name */
								__( '%s settings field was registered earlier, duplicate settings key is not allowed.', 'wp-ever-accounting' ),
								$option['id']
							),
							null
						);
						continue;
					}
					$args = wp_parse_args(
						$option,
						array(
							'section'     => $section,
							'desc'        => '',
							'id'          => $option['id'],
							'tip'         => '',
							'name'        => '',
							'type'        => 'text',
							'size'        => 'regular',
							'options'     => array(),
							'std'         => '',
							'min'         => null,
							'max'         => null,
							'step'        => null,
							'multiple'    => null,
							'placeholder' => null,
							'required'    => '',
							'disabled'    => '',
							'input_class' => '',
							'class'       => '',
							'callback'    => '',
							'style'       => '',
							'html'        => '',
							'attr'        => array(),
						)
					);

					$callback = ! empty( $args['callback'] ) ? $args['callback'] : array( $this, $args['type'] . '_callback' );
					$tip      = ! empty( $args['tip'] ) ? eaccounting_help_tip( $args['tip'] ) : '';

					if ( ! in_array( $args['type'], array( 'checkbox', 'multicheck', 'radio', 'header' ), true ) ) {
						$args['name'] = sprintf( '<label for="eaccounting_settings[%1$s]">%2$s</label>%3$s', $option['id'], $args['name'], $tip );
					} elseif ( 'header' === $args['type'] ) {
						$args['name'] = sprintf( '<h3>%s</h3>', esc_html( $args['name'] ) );
					}

					add_settings_field(
						'eaccounting_settings[' . $option['id'] . ']',
						$args['name'],
						is_callable( $callback ) ? $callback : array( $this, 'missing_callback' ),
						'eaccounting_settings_' . $tab . '_' . $section,
						'eaccounting_settings_' . $tab . '_' . $section,
						$args
					);

				}
			}
		}
		register_setting( 'eaccounting_settings', 'eaccounting_settings', array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Load accounts on settings.
	 *
	 * @return array|int
	 * @since 1.1.0
	 */
	protected function get_accounts() {
		$accounts = array();
		if ( isset( $_GET['page'] ) && 'ea-settings' === $_GET['page'] ) {
			$results    = eaccounting_get_accounts(
				array(
					'number' => - 1,
					'return' => 'raw',
				)
			);
			$accounts[] = __( 'Select account', 'wp-ever-accounting' );
			foreach ( $results as $result ) {
				$accounts[ $result->id ] = $result->name . '(' . $result->currency_code . ')';
			}
		}

		return $accounts;
	}

	/**
	 * Load categories on settings.
	 *
	 * @param string $type
	 *
	 * @return array|int
	 * @since 1.1.0
	 *
	 */
	protected function get_categories( $type = 'income' ) {
		$categories = array();
		if ( isset( $_GET['page'] ) && 'ea-settings' === $_GET['page'] ) {
			$results      = eaccounting_get_categories(
				array(
					'number' => - 1,
					'type'   => $type,
					'return' => 'raw',
				)
			);
			$categories[] = __( 'Select category', 'wp-ever-accounting' );
			$categories   = array_merge( $categories, wp_list_pluck( $results, 'name', 'id' ) );
		}

		return $categories;
	}

	/**
	 * Load currencies
	 *
	 * @param string $type
	 *
	 * @return array|int
	 * @since 1.1.0
	 *
	 */
	protected function get_currencies() {
		$currencies = array();
		if ( isset( $_GET['page'] ) && 'ea-settings' === $_GET['page'] ) {
			$results      = eaccounting_get_currencies(
				array(
					'number' => - 1,
					'return' => 'raw',
				)
			);
			$currencies[] = __( 'Select currencies', 'wp-ever-accounting' );
			foreach ( $results as $result ) {
				$currencies[ $result->code ] = $result->name . '(' . $result->symbol . ')';
			}
		}

		return $currencies;
	}

	/**
	 * Header Callback
	 *
	 * Renders the header.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	function header_callback( $args ) {
		if ( ! empty( $args['desc'] ) ) {
			echo $args['desc'];
		}
	}

	/**
	 * Text Callback
	 *
	 * Renders text fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	function text_callback( $args ) {
		$default = isset( $args['std'] ) ? $args['std'] : '';
		$value   = $this->get( $args['id'], $default );
		$attrs   = array( 'required', 'placeholder', 'disabled', 'style' );
		foreach ( $attrs as $attr ) {
			if ( ! empty( $args[ $attr ] ) ) {
				$args['attr'][ $attr ] = $args[ $attr ];
			}
		}

		echo sprintf(
			'<input type="text" class="%1$s-text %2$s" style="%3$s" name="eaccounting_settings[%4$s]" id="eaccounting_settings[%4$s]" value="%5$s" %6$s/>',
			esc_attr( $args['size'] ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( stripslashes( $value ) ),
			eaccounting_implode_html_attributes( $args['attr'] )
		);

		echo ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';

	}

	/**
	 * Text Callback
	 *
	 * Renders text fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	function email_callback( $args ) {
		$default = isset( $args['std'] ) ? $args['std'] : '';
		$value   = $this->get( $args['id'], $default );
		$attrs   = array( 'required', 'placeholder', 'disabled', 'style' );
		foreach ( $attrs as $attr ) {
			if ( ! empty( $args[ $attr ] ) ) {
				$args['attr'][ $attr ] = $args[ $attr ];
			}
		}

		echo sprintf(
			'<input type="email" class="%1$s-text %2$s" style="%3$s" name="eaccounting_settings[%4$s]" id="eaccounting_settings[%4$s]" value="%5$s" %6$s/>',
			esc_attr( $args['size'] ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( stripslashes( $value ) ),
			eaccounting_implode_html_attributes( $args['attr'] )
		);

		echo ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';

	}

	/**
	 * Checkbox Callback
	 *
	 * Renders checkboxes.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function checkbox_callback( $args ) {
		$value      = $this->get( $args['id'] );
		$checked    = isset( $value ) ? checked( 'yes', $value, false ) : '';
		$attributes = eaccounting_implode_html_attributes( $args['attr'] );
		$id         = 'eaccounting_settings[' . $args['id'] . ']';
		$html       = '<label for="' . $id . '">';
		$html      .= '<input type="checkbox" id="' . $id . '" name="' . $id . '" value="yes" ' . $checked . ' ' . $attributes . '/>&nbsp;';
		$html      .= $args['desc'];
		$html      .= '</label>';

		echo $html;
	}

	/**
	 * Multicheck Callback
	 *
	 * Renders multiple checkboxes.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function multicheck_callback( $args ) {

		if ( ! empty( $args['options'] ) ) {
			foreach ( $args['options'] as $key => $option ) {
				if ( isset( $this->settings[ $args['id'] ][ $key ] ) ) {
					$enabled = $option;
				} else {
					$enabled = null;
				}
				echo '<label for="eaccounting_settings[' . $args['id'] . '][' . $key . ']">';
				echo '<input name="eaccounting_settings[' . $args['id'] . '][' . $key . ']" id="eaccounting_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
				echo $option . '</label><br/>';
			}
			echo '<p class="description">' . $args['desc'] . '</p>';
		}
	}

	/**
	 * Radio Callback
	 *
	 * Renders radio boxes.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function radio_callback( $args ) {

		echo '<fieldset id="eaccounting_settings[' . $args['id'] . ']">';
		echo '<legend class="screen-reader-text">' . $args['name'] . '</legend>';

		foreach ( $args['options'] as $key => $option ) :
			$checked = false;

			if ( isset( $this->settings[ $args['id'] ] ) && $this->settings[ $args['id'] ] == $key ) { //phpcs:ignore
				$checked = true;
			} elseif ( isset( $args['std'] ) && $args['std'] == $key && ! isset( $this->options[ $args['id'] ] ) ) { //phpcs:ignore
				$checked = true;
			}

			echo '<label for="eaccounting_settings[' . $args['id'] . '][' . $key . ']">';
			echo '<input name="eaccounting_settings[' . $args['id'] . ']" id="eaccounting_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>';
			echo $option . '</label><br/>';
		endforeach;

		echo '</fieldset><p class="description">' . $args['desc'] . '</p>';
	}

	/**
	 * URL Callback
	 *
	 * Renders URL fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function url_callback( $args ) {

		if ( isset( $this->settings[ $args['id'] ] ) && ! empty( $this->settings[ $args['id'] ] ) ) {
			$value = $this->settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size       = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$attributes = eaccounting_implode_html_attributes( $args['attr'] );
		$desc       = ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';

		$html  = sprintf(
			'<input type="url" class="%s-text %s" style="%s" name="eaccounting_settings[%s]" id="eaccounting_settings[%s]" value="%s" %s/>',
			esc_attr( $size ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			esc_attr( stripslashes( $value ) ),
			$attributes
		);
		$html .= $desc;

		echo $html;
	}

	/**
	 * Number Callback
	 *
	 * Renders number fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function number_callback( $args ) {

		// Get value, with special consideration for 0 values, and never allowing negative values
		$value = isset( $this->settings[ $args['id'] ] ) ? $this->settings[ $args['id'] ] : null;
		$value = ( ! is_null( $value ) && '' !== $value && floatval( $value ) >= 0 ) ? floatval( $value ) : null;

		// Saving the field empty will revert to std value, if it exists
		$std   = ( isset( $args['std'] ) && ! is_null( $args['std'] ) && '' !== $args['std'] && floatval( $args['std'] ) >= 0 ) ? $args['std'] : null;
		$value = ! is_null( $value ) ? $value : ( ! is_null( $std ) ? $std : null );
		$value = eaccounting_format_decimal( $value, false );

		$size       = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$attributes = eaccounting_implode_html_attributes( $args['attr'] );
		$desc       = ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';

		$html  = sprintf(
			'<input type="number" class="%s-text %s" style="%s" name="eaccounting_settings[%s]" id="eaccounting_settings[%s]" value="%s" %s/>',
			esc_attr( $size ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			esc_attr( stripslashes( $value ) ),
			$attributes
		);
		$html .= $desc;

		echo $html;
	}

	/**
	 * Textarea Callback
	 *
	 * Renders textarea fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function textarea_callback( $args ) {

		if ( isset( $this->settings[ $args['id'] ] ) ) {
			$value = $this->settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size       = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$attributes = eaccounting_implode_html_attributes( $args['attr'] );
		$desc       = ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';

		$html  = sprintf(
			'<textarea type="text" class="%s-text %s" style="%s" name="eaccounting_settings[%s]" id="eaccounting_settings[%s]" %s>%s</textarea>',
			esc_attr( $size ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			$attributes,
			esc_textarea( stripslashes( $value ) )
		);
		$html .= $desc;

		echo $html;

	}

	/**
	 * Password Callback
	 *
	 * Renders password fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function password_callback( $args ) {

		if ( isset( $this->settings[ $args['id'] ] ) ) {
			$value = $this->settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size       = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$attributes = eaccounting_implode_html_attributes( $args['attr'] );
		$desc       = ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';

		$html  = sprintf(
			'<input type="password" class="%s-text %s" style="%s" name="eaccounting_settings[%s]" id="eaccounting_settings[%s]" value="%s" %s/>',
			esc_attr( $size ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			esc_attr( stripslashes( $value ) ),
			$attributes
		);
		$html .= $desc;

		echo $html;
	}

	/**
	 * Select Callback
	 *
	 * Renders select fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 * @global      $this ->options Array of all the EverAccounting Options
	 *
	 */
	function select_callback( $args ) {

		if ( isset( $this->settings[ $args['id'] ] ) ) {
			$value = $this->settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$html = sprintf(
			'<select class="%s-text %s" style="%s" name="eaccounting_settings[%s]" id="eaccounting_settings[%s]" %s>',
			$args['size'],
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			eaccounting_implode_html_attributes( $args['attr'] )
		);

		foreach ( $args['options'] as $key => $option_value ) {
			$html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $key ), eaccounting_selected( esc_attr( $key ), esc_attr( $value ) ), esc_html( $option_value ) );
		}

		$html .= '</select>';
		echo $html;

		echo ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';
	}

	/**
	 * Rich Editor Callback
	 *
	 * Renders rich editor fields.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @global        $this ->options Array of all the EverAccounting Options
	 *
	 * @global string $wp_version WordPress Version
	 *
	 * @since 1.0.2
	 */
	function rich_editor_callback( $args ) {

		if ( ! empty( $this->settings[ $args['id'] ] ) ) {
			$value = $this->settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		ob_start();
		wp_editor( stripslashes( $value ), 'eaccounting_settings_' . $args['id'], array( 'textarea_name' => 'eaccounting_settings[' . $args['id'] . ']' ) );
		$html = ob_get_clean();

		$html .= '<br/><p class="description"> ' . $args['desc'] . '</p>';

		echo $html;
	}

	/**
	 * Upload Callback
	 *
	 * Renders file upload fields.
	 *
	 * @param array $args Arguements passed by the setting
	 *
	 * @since 1.0.2
	 *
	 */
	function upload_callback( $args ) {
		if ( isset( $this->settings[ $args['id'] ] ) ) {
			$value = $this->settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size       = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$attributes = eaccounting_implode_html_attributes( $args['attr'] );
		$desc       = ! empty( $args['desc'] ) ? sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) ) : '';

		$html  = sprintf(
			'<input type="text" class="%s-text %s" style="%s" name="eaccounting_settings[%s]" id="eaccounting_settings[%s]" value="%s" %s/>',
			esc_attr( $size ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['id'] ),
			esc_attr( stripslashes( $value ) ),
			$attributes
		);
		$html .= sprintf( '<span>&nbsp;<input type="button" class="ea_settings_upload_button button-secondary" value="%s"/></span>', __( 'Upload File', 'wp-ever-accounting' ) );
		$html .= $desc;

		echo $html;
	}


	function html_callback( $args ) {
		$args = wp_parse_args( $args, array( 'html' => '' ) );
		echo sprintf( '<div class="ea-settings-html %s">%s</div>', sanitize_html_class( $args['input_class'] ), wp_kses_post( $args['html'] ) );
	}

	/**
	 * License key callback.
	 *
	 * @param $args
	 *
	 * @since 1.1.0
	 *
	 */
	function license_key_callback( $args ) {
		$value    = $this->get( $args['id'], '' );
		$license  = $args['license_status'];
		$messages = array();

		echo sprintf(
			'<input type="text" class="%1$s-text %2$s" style="%3$s" name="eaccounting_settings[%4$s]" id="eaccounting_settings[%4$s]" value="%5$s"/>',
			esc_attr( $args['size'] ),
			esc_attr( $args['input_class'] ),
			esc_attr( $args['style'] ),
			esc_attr( $args['id'] ),
			esc_attr( stripslashes( $value ) )
		);

		$messages = array();
		if ( is_object( $license ) && false === $license->success ) {
			switch ( $license->error ) {
				case 'expired':
					$messages[] = sprintf(
					/* translators: %s extension name */
						__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank">renew your license key</a>.', 'wp-ever-accounting' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ), //phpcs:ignore
						'https://wpeveraccounting.com/checkout/?edd_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
					);
					break;

				case 'disabled':
				case 'revoked':
					$messages[] = sprintf(
					/* translators: %s extension name */
						__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'wp-ever-accounting' ),
						'https://wpeveraccounting.com/support?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
					);
					break;

				case 'missing':
					$messages[] = sprintf(
					/* translators: %s extension name */
						__( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'wp-ever-accounting' ),
						'https://wpeveraccounting.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
					);
					break;

				case 'invalid':
				case 'site_inactive':
					$messages[] = sprintf(
					/* translators: %s extension name */
						__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank">visit your account page</a> to manage your license key URLs.', 'wp-ever-accounting' ),
						$args['name'],
						'https://wpeveraccounting.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
					);
					break;

				case 'item_name_mismatch':
					/* translators: %s extension name */
					$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'wp-ever-accounting' ), $args['name'] );

					break;

				case 'no_activations_left':
					/* translators: %s extension name */
					$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'wp-ever-accounting' ), 'https://wpeveraccounting.com/your-account/' );
					break;

				case 'license_not_activable':
					$messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'wp-ever-accounting' );
					break;

				default:
					$error = ! empty( $license->error ) ? $license->error : __( 'unknown_error', 'wp-ever-accounting' );
					/* translators: %s extension name */
					$messages[] = sprintf( __( 'There was an error with this license key: %1$s. Please <a href="%2$s">contact our support team</a>.', 'wp-ever-accounting' ), $error, 'https://wpeveraccounting.com/support' );
					break;

			}
		}

		if ( is_object( $license ) && $license->success && $license->license ) {
			$now        = current_time( 'timestamp' ); //phpcs:ignore
			$expiration = strtotime( $license->expires, current_time( 'timestamp' ) ); //phpcs:ignore
			if ( 'lifetime' === $license->expires ) {
				$messages[] = __( 'License key never expires.', 'wp-ever-accounting' );
			} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {
				$messages[] = sprintf(
				/* translators: %s extension name */
					__( 'Your license key expires soon! It expires on %1$s. <a href="%2$s" target="_blank">Renew your license key</a>.', 'wp-ever-accounting' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ), //phpcs:ignore
					'https://wpeveraccounting.com/checkout/?edd_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
				);
			} else {
				$messages[] = sprintf(
				/* translators: %s extension name */
					__( 'Your license key expires on %s.', 'wp-ever-accounting' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ) //phpcs:ignore
				);
			}
		}
		if ( empty( $messages ) ) {
			$messages[] = sprintf(
			/* translators: %s extension name */
				__( 'To receive updates, please enter your valid %s license key.', 'wp-ever-accounting' ),
				strip_tags( $args['name'] )
			);
		}

		if ( ( is_object( $license ) && 'valid' === $license->license ) || 'valid' === $license ) {
			echo '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License', 'wp-ever-accounting' ) . '"/>';
		}

		//echo '<label for="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $message ) {
				echo '<div class="edd-license-data edd-license-">';
				echo '<p>' . $message . '</p>';
				echo '</div>';

			}
		}

		wp_nonce_field( sanitize_key( $args['id'] ) . '-nonce', sanitize_key( $args['id'] ) . '-nonce' );
	}

	/**
	 * Missing Callback
	 *
	 * If a function is missing for settings callbacks alert the user.
	 *
	 * @param array $args Arguments passed by the setting
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	function missing_callback( $args ) {
		/* translators: %s name of the callback */
		printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'wp-ever-accounting' ), $args['id'] );
	}

	/**
	 * Get the value of a specific setting
	 *
	 * Note: By default, zero values are not allowed. If you have a custom
	 * setting that needs to allow 0 as a valid value, but sure to add its
	 * key to the filtered array seen in this method.
	 *
	 * @param mixed $default (optional)
	 *
	 * @param string $key
	 *
	 * @return mixed
	 * @since  1.0.2
	 *
	 */
	public function get( $key, $default = false ) {

		// Only allow non-empty values, otherwise fallback to the default
		$value = ! empty( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;

		$zero_values_allowed = array();

		/**
		 * Filters settings allowed to accept 0 as a valid value without
		 * falling back to the default.
		 *
		 * @param array $zero_values_allowed Array of setting IDs.
		 */
		$zero_values_allowed = (array) apply_filters( 'eaccounting_settings_zero_values_allowed', $zero_values_allowed );

		// Allow 0 values for specified keys only
		if ( in_array( $key, $zero_values_allowed ) ) { // phpcs:ignore

			$value = isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : null;
			$value = ( ! is_null( $value ) && '' !== $value ) ? $value : $default;

		}

		return $value;
	}

	/**
	 * Retrieve the array of plugin settings
	 *
	 * @return array
	 * @since 1.0.2
	 */
	function sanitize_settings( $input = array() ) {
		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		parse_str( $_POST['_wp_http_referer'], $referrer );

		$saved = get_option( 'eaccounting_settings', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$settings = $this->get_registered_settings();
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
		$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';
		$settings = isset( $settings[ $tab ] ) ? $settings[ $tab ][ $section ] : array();

		$input = $input ? $input : array();

		// Ensure a value is always passed for every checkbox
		if ( ! empty( $settings ) ) {

			foreach ( $settings as $setting ) {

				// Single checkbox
				if ( 'checkbox' === $setting['type'] ) {
					$input[ $setting['id'] ] = empty( $input[ $setting['id'] ] ) ? 'no' : 'yes';
				}

				// Multicheck list
				if ( 'multicheck' === $setting['type'] ) {
					if ( empty( $input[ $setting['id'] ] ) ) {
						$input[ $setting['id'] ] = array();
					}
				}
			}
		}

		// Loop through each setting being saved and pass it through a sanitization filter
		foreach ( $input as $key => $value ) {

			// Get the setting type (checkbox, select, etc)
			$type              = isset( $settings[ $key ]['type'] ) ? $settings[ $key ]['type'] : false;
			$sanitize_callback = isset( $settings[ $key ]['sanitize_callback'] ) ? $settings[ $key ]['sanitize_callback'] : false;
			$input[ $key ]     = $value;

			if ( $type ) {
				/**
				 * Filters the sanitized value for a setting of a given type.
				 *
				 * This filter is appended with the setting type (checkbox, select, etc), for example:
				 *
				 *     `eaccounting_settings_sanitize_checkbox`
				 *     `eaccounting_settings_sanitize_select`
				 *
				 * @param string $key The settings key.
				 *
				 * @param array $value The input array and settings key defined within.
				 *
				 * @since 1.0.2
				 *
				 */
				$input[ $key ] = apply_filters( 'eaccounting_settings_sanitize_' . $type, $input[ $key ], $key );

				if ( $sanitize_callback && is_callable( $sanitize_callback ) ) {
					$input[ $key ] = call_user_func( $sanitize_callback, $value );
				}
			}

			/**
			 * General setting sanitization filter
			 *
			 * @param string $key The settings key.
			 *
			 * @param array $input [ $key ] The input array and settings key defined within.
			 *
			 * @since 1.0
			 *
			 */
			$input[ $key ] = apply_filters( 'eaccounting_settings_sanitize', $input[ $key ], $key );
		}

		add_settings_error( 'eaccounting-notices', '', __( 'Settings updated.', 'wp-ever-accounting' ), 'updated' );

		return array_merge( $saved, $input );
	}

	/**
	 * Sets an option (in memory).
	 *
	 * @param bool $save Optional. Whether to trigger saving the option or options. Default false.
	 *
	 * @param array $settings An array of `key => value` setting pairs to set.
	 *
	 * @return bool If `$save` is not false, whether the options were saved successfully. True otherwise.
	 * @since  1.0.2
	 * @access public
	 *
	 */
	public function set( $settings, $save = false ) {
		foreach ( $settings as $option => $value ) {
			$this->settings[ $option ] = $value;
		}

		if ( false !== $save ) {
			return $this->save();
		}

		return true;
	}

	/**
	 * Saves option values queued in memory.
	 *
	 * Note: If posting separately from the main settings submission process, this method should
	 * be called directly for direct saving to prevent memory pollution. Otherwise, this method
	 * is only accessible via the optional `$save` parameter in the set() method.
	 *
	 * @param array $options Optional. Options to save/overwrite directly. Default empty array.
	 *
	 * @return bool False if the options were not updated (saved) successfully, true otherwise.
	 * @since 1.0.2
	 *
	 */
	protected function save( $options = array() ) {
		$all_options = $this->get_all();

		if ( ! empty( $options ) ) {
			$all_options = array_merge( $all_options, $options );
		}

		$updated = update_option( 'eaccounting_settings', $all_options );

		// Refresh the options array available in memory (prevents unexpected race conditions).
		$this->settings = get_option( 'eaccounting_settings', array() );

		return $updated;
	}

	/**
	 * Get all settings
	 *
	 * @return array
	 * @since 1.0.2
	 */
	public function get_all() {
		return $this->settings;
	}
}
