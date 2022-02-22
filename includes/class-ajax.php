<?php
/**
 * EverAccounting  AJAX Event Handlers.
 *
 * @since       1.0.2
 * @package     EverAccounting
 * @class       Ajax
 */

namespace EverAccounting;

use EverAccounting\Models\Bill;
use EverAccounting\Models\Invoice;
use EverAccounting\Models\Note;

defined( 'ABSPATH' ) || exit();

/**
 * Class Ajax
 *
 * @since 1.0.2
 */
class Ajax {

	/**
	 * Ajax constructor.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Set EA AJAX constant and headers.
	 *
	 * @since 1.0.2
	 */
	public static function define_ajax() {
		// phpcs:disable
		if ( ! empty( $_GET['ea-ajax'] ) ) {
			eaccounting_maybe_define_constant( 'DOING_AJAX', true );
			eaccounting_maybe_define_constant( 'EACCOUNTING_DOING_AJAX', true );
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON.
			}
			$GLOBALS['wpdb']->hide_errors();
		}
		// phpcs:enable
	}


	/**
	 * Send headers for EverAccounting Ajax Requests.
	 *
	 * @since 1.0.2
	 */
	private static function ajax_headers() {
		if ( ! headers_sent() ) {
			send_origin_headers();
			send_nosniff_header();
			header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			header( 'X-Robots-Tag: noindex' );
			status_header( 200 );
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			headers_sent( $file, $line );
			trigger_error( "eaccounting_ajax_headers cannot set headers - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Check for EverAccounting Ajax request and fire action.
	 *
	 * @since 1.0.2
	 */
	public static function do_ajax() {
		global $wp_query;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['ea-ajax'] ) ) {
			$wp_query->set( 'ea-ajax', sanitize_text_field( wp_unslash( $_GET['ea-ajax'] ) ) );
		}

		$action = $wp_query->get( 'ea-ajax' );

		if ( $action ) {
			self::ajax_headers();
			$action = sanitize_text_field( $action );
			do_action( 'eaccounting_ajax_' . $action );
			wp_die();
		}
		// phpcs:enable
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 *
	 * @since 1.0.2
	 */
	public static function add_ajax_events() {
		$ajax_events_nopriv = array();

		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_eaccounting_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			add_action( 'wp_ajax_nopriv_eaccounting_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			// EverAccounting AJAX can be used for frontend ajax requests.
			add_action( 'eaccounting_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}

		$ajax_events = array(
			//currency
			'get_currencies',
			'get_currency',
			'get_currency_codes',
			'edit_currency',

			//category
			'get_expense_categories',
			'get_income_categories',
			'get_item_categories',
			'edit_category',

			//invoice
			'add_invoice_payment',
			'add_invoice_note',
			'invoice_recalculate',
			'edit_invoice',

			//revenue
			'edit_revenue',

			//customer
			'get_customers',
			'edit_customer',

			//bill
			'add_bill_payment',
			'add_bill_note',
			'bill_recalculate',
			'edit_bill',

			//payment
			'edit_payment',

			//vendor
			'get_vendors',
			'edit_vendor',

			//account
			'get_account',
			'get_accounts',
			'get_account_currency',
			'edit_account',

			//transfer
			'edit_transfer',

			//note
			'delete_note',

			//item
			'get_items',
			'edit_item',
		);

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_eaccounting_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	/**
	 * Get expense categories.
	 *
	 * @since 1.1.0
	 */
	public static function get_expense_categories() {
		self::verify_nonce( 'ea_categories' );
		$search = isset( $_REQUEST['search'] ) ? eaccounting_clean( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;


		wp_send_json_success(
			eaccounting_get_categories(
				array(
					'search' => $search,
					'type'   => 'expense',
					'page'   => $page,
					'return' => 'raw',
					'status' => 'active',
				)
			)
		);
	}

	/**
	 * Get income categories.
	 *
	 * @since 1.1.0
	 */
	public static function get_income_categories() {
		self::verify_nonce( 'ea_categories' );
		$search = isset( $_REQUEST['search'] ) ? eaccounting_clean( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;


		wp_send_json_success(
			eaccounting_get_categories(
				array(
					'search' => $search,
					'type'   => 'income',
					'page'   => $page,
					'return' => 'raw',
					'status' => 'active',
				)
			)
		);
	}

	/**
	 * Get item categories.
	 *
	 * @since 1.1.0
	 */
	public static function get_item_categories() {
		self::verify_nonce( 'ea_categories' );
		$search = isset( $_REQUEST['search'] ) ? eaccounting_clean( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;


		wp_send_json_success(
			eaccounting_get_categories(
				array(
					'search' => $search,
					'type'   => 'item',
					'page'   => $page,
					'return' => 'raw',
					'status' => 'active',
				)
			)
		);
	}

	/**
	 * Handle ajax action of creating/updating account.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function edit_category() {
		self::verify_nonce( 'ea_edit_category' );
		self::check_permission( 'ea_manage_category' );
		$posted  = eaccounting_clean( wp_unslash( $_REQUEST ) );
		$created = eaccounting_insert_category( $posted );
		if ( is_wp_error( $created ) ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Category updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Category created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}
		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Add payment to invoice.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function add_invoice_payment() {
		self::verify_nonce( 'ea_add_invoice_payment' );
		self::check_permission( 'ea_manage_invoice' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );

		try {
			$invoice = new Invoice( $posted['invoice_id'] );
			if ( ! $invoice->exists() ) {
				throw new \Exception( __( 'Invalid Invoice Item', 'wp-ever-accounting' ) );
			}
			$invoice->add_payment( $posted );
			$invoice->save();

			wp_send_json_success(
				array(
					'message' => __( 'Invoice Payment saved', 'wp-ever-accounting' ),
					'total'   => $invoice->get_total(),
					'due'     => $invoice->get_total_due(),
					'paid'    => $invoice->get_total_paid(),
					'status'  => $invoice->get_status(),
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Add invoice note.
	 * @since 1.1.0
	 */
	public static function add_invoice_note() {
		self::verify_nonce( 'ea_add_invoice_note' );
		self::check_permission( 'ea_manage_invoice' );
		$invoice_id    = absint( $_REQUEST['invoice_id'] );
		$note          = eaccounting_clean( $_REQUEST['note'] );
		$customer_note = isset( $_REQUEST['type'] ) && 'customer' === $_REQUEST['type'] ? true : false;
		if ( empty( $note ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Note Content empty.', 'wp-ever-accounting' ),
				)
			);
		}
		try {
			$invoice = new Invoice( $invoice_id );
			$invoice->add_note( $note );
			$notes = eaccounting_get_admin_template_html( 'invoices/invoice-notes', array( 'invoice' => $invoice ) );
			wp_send_json_success(
				array(
					'message' => __( 'Note Added.', 'wp-ever-accounting' ),
					'notes'   => $notes,
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Recalculate invoice totals
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function invoice_recalculate() {
		self::verify_nonce( 'ea_edit_invoice' );
		self::check_permission( 'ea_manage_invoice' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );
		try {
			$posted  = wp_parse_args( $posted, array( 'id' => null ) );
			$invoice = new Invoice( $posted['id'] );
			$invoice->set_props( $posted );
			$totals = $invoice->calculate_totals();
			wp_send_json_success(
				array(
					'html'   => eaccounting_get_admin_template_html(
						'invoices/invoice-items',
						array(
							'invoice' => $invoice,
						)
					),
					'line'   => array_map( 'strval', $invoice->get_items() ),
					'totals' => $totals,
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Handle ajax action of creating/updating invoice.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function edit_invoice() {
		self::verify_nonce( 'ea_edit_invoice' );
		self::check_permission( 'ea_manage_invoice' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );

		try {
			$posted  = wp_parse_args( $posted, array( 'id' => null ) );
			$invoice = new Invoice( $posted['id'] );
			$invoice->set_props( $posted );
			$invoice->save();

			$redirect = add_query_arg(
				array(
					'action'     => 'view',
					'invoice_id' => $invoice->get_id(),
				),
				eaccounting_clean( $_REQUEST['_wp_http_referer'] )
			);
			$response = array(
				'items'    => eaccounting_get_admin_template_html(
					'invoices/invoice-items',
					array(
						'invoice' => $invoice,
					)
				),
				'line'     => array_map( 'strval', $invoice->get_items() ),
				'redirect' => $redirect,
			);
			if ( empty( $posted['id'] ) ) {
				$response['redirect'] = $redirect;
				$invoice->add_note( sprintf( __( '%s added', 'wp-ever-accounting' ), $invoice->get_document_number() ) );
			}
			wp_send_json_success( $response );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Handle ajax action of creating/updating revenue.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function edit_revenue() {
		self::verify_nonce( 'ea_edit_revenue' );
		self::check_permission( 'ea_manage_revenue' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );

		$created = eaccounting_insert_revenue( $posted );
		if ( is_wp_error( $created ) || ! $created->exists() ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Revenue updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Revenue created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}

		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Get customers.
	 *
	 * @since 1.1.0
	 */
	public static function get_customers() {
		self::verify_nonce( 'ea_get_customers' );
		$search = isset( $_REQUEST['search'] ) ? eaccounting_clean( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;


		wp_send_json_success(
			eaccounting_get_customers(
				array(
					'search' => $search,
					'page'   => $page,
					'return' => 'raw',
					'status' => 'active',
				)
			)
		);
	}


	/**
	 * Handle ajax action of creating/updating customer.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function edit_customer() {
		self::verify_nonce( 'ea_edit_customer' );
		self::check_permission( 'ea_manage_customer' );
		$posted  = eaccounting_clean( $_REQUEST );
		$created = eaccounting_insert_customer( $posted );
		if ( is_wp_error( $created ) || ! $created->exists() ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Customer updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Customer created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}

		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Add payment to bill.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function add_bill_payment() {
		self::verify_nonce( 'ea_add_bill_payment' );
		self::check_permission( 'ea_manage_bill' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );

		try {
			$bill = new Bill( $posted['bill_id'] );
			if ( ! $bill->exists() ) {
				throw new \Exception( __( 'Invalid Invoice Item', 'wp-ever-accounting' ) );
			}
			$bill->add_payment( $posted );
			$bill->save();

			wp_send_json_success(
				array(
					'message' => __( 'Bill Payment saved', 'wp-ever-accounting' ),
					'total'   => $bill->get_total(),
					'due'     => $bill->get_total_due(),
					'paid'    => $bill->get_total_paid(),
					'status'  => $bill->get_status(),
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Add bill note.
	 *
	 * @since 1.1.0
	 */
	public static function add_bill_note() {
		self::verify_nonce( 'ea_add_bill_note' );
		self::check_permission( 'ea_manage_bill' );
		$bill_id       = absint( $_REQUEST['bill_id'] );
		$note          = eaccounting_clean( $_REQUEST['note'] );
		$customer_note = isset( $_REQUEST['type'] ) && 'customer' === $_REQUEST['type'];
		if ( empty( $note ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Note Content empty.', 'wp-ever-accounting' ),
				)
			);
		}
		try {
			$bill = new Bill( $bill_id );
			$bill->add_note( $note );
			$notes = eaccounting_get_admin_template_html( 'bills/bill-notes', array( 'bill' => $bill ) );
			wp_send_json_success(
				array(
					'message' => __( 'Note Added.', 'wp-ever-accounting' ),
					'notes'   => $notes,
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Recalculate bill totals
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function bill_recalculate() {
		self::verify_nonce( 'ea_edit_bill' );
		self::check_permission( 'ea_manage_bill' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );
		try {
			$posted = wp_parse_args( $posted, array( 'id' => null ) );
			$bill   = new Bill( $posted['id'] );
			$bill->set_props( $posted );
			$totals = $bill->calculate_totals();
			wp_send_json_success(
				array(
					'html'   => eaccounting_get_admin_template_html(
						'bills/bill-items',
						array(
							'bill' => $bill,
							'mode' => 'edit',
						)
					),
					'line'   => array_map( 'strval', $bill->get_items() ),
					'totals' => $totals,
				)
			);

		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Handle ajax action of creating/updating bill.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function edit_bill() {
		self::verify_nonce( 'ea_edit_bill' );
		self::check_permission( 'ea_manage_bill' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );

		try {
			$posted = wp_parse_args( $posted, array( 'id' => null ) );
			$bill   = new Bill( $posted['id'] );
			$bill->set_props( $posted );
			$bill->save();
			$redirect = add_query_arg(
				array(
					'action'  => 'view',
					'bill_id' => $bill->get_id(),
				),
				eaccounting_clean( $_REQUEST['_wp_http_referer'] )
			);

			$response = array(
				'items'    => eaccounting_get_admin_template_html(
					'bills/bill-items',
					array(
						'bill' => $bill,
					)
				),
				'line'     => array_map( 'strval', $bill->get_items() ),
				'redirect' => $redirect,
			);
			if ( empty( $posted['id'] ) ) {
				$response['redirect'] = $redirect;
				$bill->add_note( sprintf( __( '%s added', 'wp-ever-accounting' ), $bill->get_document_number() ) );
			}
			wp_send_json_success( $response );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Get currencies.
	 *
	 * @since 1.1.0
	 */
	public static function get_currencies() {
		self::verify_nonce( 'ea_get_currencies' );
		$currencies = eaccounting_get_currencies(
			array(
				'number' => - 1,
				'return' => 'raw',
			)
		);

		wp_send_json_success( $currencies );
	}

	/**
	 * Get currency data.
	 *
	 * @since 1.0.2
	 */
	public static function get_currency() {
		self::verify_nonce( 'ea_get_currency' );
		self::check_permission( 'manage_eaccounting' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );
		$code   = ! empty( $posted['code'] ) ? $posted['code'] : false;
		if ( ! $code ) {
			wp_send_json_error(
				array(
					'message' => __( 'No code received', 'wp-ever-accounting' ),
				)
			);
		}
		$currency = eaccounting_get_currency( $code );
		if ( empty( $currency ) || is_wp_error( $currency ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not find the currency', 'wp-ever-accounting' ),
				)
			);
		}

		wp_send_json_success( $currency->get_data() );
	}

	/**
	 * Get currency codes.
	 *
	 * @since 1.1.0
	 */
	public static function get_currency_codes() {
		self::verify_nonce( 'ea_get_currency_codes' );
		self::check_permission( 'manage_eaccounting' );
		$currencies = eaccounting_get_global_currencies();
		wp_send_json_success( $currencies );
	}

	/**
	 * Handle ajax action of creating/updating currencies.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function edit_currency() {
		self::verify_nonce( 'ea_edit_currency' );
		self::check_permission( 'ea_manage_currency' );
		$posted  = eaccounting_clean( wp_unslash( $_REQUEST ) );
		$created = eaccounting_insert_currency( $posted );
		if ( is_wp_error( $created ) ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Currency updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Currency created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}

		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Handle ajax action of creating/updating payment.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function edit_payment() {
		self::verify_nonce( 'ea_edit_payment' );
		self::check_permission( 'ea_manage_payment' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );

		$created = eaccounting_insert_payment( $posted );
		if ( is_wp_error( $created ) || ! $created->exists() ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Payment updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Payment created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}

		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Get vendors.
	 *
	 * @since 1.1.0
	 */
	public static function get_vendors() {
		self::verify_nonce( 'ea_get_vendors' );
		$search = isset( $_REQUEST['search'] ) ? eaccounting_clean( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;

		wp_send_json_success(
			eaccounting_get_vendors(
				array(
					'search' => $search,
					'page'   => $page,
					'return' => 'raw',
					'status' => 'active',
				)
			)
		);
	}

	/**
	 * Handle ajax action of creating/updating vendor.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function edit_vendor() {
		self::verify_nonce( 'ea_edit_vendor' );
		self::check_permission( 'ea_manage_vendor' );
		$posted = eaccounting_clean( wp_unslash( $_REQUEST ) );

		$created = eaccounting_insert_vendor( $posted );
		if ( is_wp_error( $created ) || ! $created->exists() ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Vendor updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Vendor created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}

		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Get single account.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function get_account() {
		self::verify_nonce( 'ea_get_account' );
		self::check_permission( 'manage_eaccounting' );
		$id      = empty( $_REQUEST['id'] ) ? null : absint( $_REQUEST['id'] );
		$account = eaccounting_get_account( $id );
		if ( $account ) {
			wp_send_json_success( $account->get_data() );
			wp_die();
		}

		wp_send_json_error( array() );

		wp_die();
	}

	/**
	 * Get accounts.
	 *
	 * @since 1.1.0
	 */
	public static function get_accounts() {
		self::verify_nonce( 'ea_get_accounts' );
		$search = isset( $_REQUEST['search'] ) ? eaccounting_clean( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;


		wp_send_json_success(
			eaccounting_get_accounts(
				array(
					'search' => $search,
					'page'   => $page,
					'return' => 'raw',
					'status' => 'active',
				)
			)
		);
	}

	/**
	 * Get currency data.
	 *
	 * @since 1.0.2
	 */
	public static function get_account_currency() {
		self::verify_nonce( 'ea_get_currency' );
		self::check_permission( 'manage_eaccounting' );
		$posted     = eaccounting_clean( wp_unslash( $_REQUEST ) );
		$account_id = ! empty( $posted['account_id'] ) ? $posted['account_id'] : false;

		if ( ! $account_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'No account id received', 'wp-ever-accounting' ),
				)
			);
		}
		$account = eaccounting_get_account( $account_id );
		if ( empty( $account ) || is_wp_error( $account ) || empty( $account->get_currency()->exists() ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not find the currency', 'wp-ever-accounting' ),
				)
			);
		}

		wp_send_json_success( $account->get_currency()->get_data() );
	}


	/**
	 * Handle ajax action of creating/updating account.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function edit_account() {
		self::verify_nonce( 'ea_edit_account' );
		self::check_permission( 'ea_manage_account' );
		$posted  = eaccounting_clean( wp_unslash( $_REQUEST ) );
		$created = eaccounting_insert_account( $posted );
		if ( is_wp_error( $created ) ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Account updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Account created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}

		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Handle ajax action of creating/updating transfer.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function edit_transfer() {
		self::verify_nonce( 'ea_edit_transfer' );
		self::check_permission( 'ea_manage_transfer' );
		$posted  = eaccounting_clean( wp_unslash( $_REQUEST ) );
		$created = eaccounting_insert_transfer( $posted );
		if ( is_wp_error( $created ) || ! $created->exists() ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Transfer updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Transfer created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}

		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}


	/**
	 * Delete note from database.
	 *
	 * @since 1.1.0
	 */
	public static function delete_note() {
		self::verify_nonce( 'ea_delete_note' );
		self::check_permission( 'ea_manage_invoice' );
		$note    = new Note( $_REQUEST['id'] );
		$note    = eaccounting_get_note( $note );
		$deleted = eaccounting_delete_note( absint( $_REQUEST['id'] ) );
		if ( ! $deleted ) {
			wp_send_json_error(
				array(
					'message' => __( 'Note could not be deleted.', 'wp-ever-accounting' ),
				)
			);
		}
		if ( 'invoice' === $note->get_type() ) {
			$invoice_id = $note->get_parent_id();
			$invoice    = new Invoice( $invoice_id );
			$notes      = eaccounting_get_admin_template_html( 'invoices/invoice-notes', array( 'invoice' => $invoice ) );
		} elseif ( 'bill' === $note->get_type() ) {
			$bill_id = $note->get_parent_id();
			$bill    = new Bill( $bill_id );
			$notes   = eaccounting_get_admin_template_html( 'bills/bill-notes', array( 'bill' => $bill ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Note Deleted.', 'wp-ever-accounting' ),
				'notes'   => $notes,
			)
		);
	}

	/**
	 * Get all items
	 *
	 * @since 1.1.0
	 */
	public static function get_items() {
		self::verify_nonce( 'ea_get_items' );
		self::check_permission( 'manage_eaccounting' );

		$search = isset( $_REQUEST['search'] ) ? eaccounting_clean( $_REQUEST['search'] ) : '';

		wp_send_json_success(
			eaccounting_get_items(
				array(
					'search' => $search,
					'return' => 'raw',
					'status' => 'active',
				)
			)
		);
	}

	/**
	 * Handle ajax action of creating/updating item.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function edit_item() {
		self::verify_nonce( 'ea_edit_item' );
		self::check_permission( 'ea_manage_item' );
		$posted  = eaccounting_clean( wp_unslash( $_REQUEST ) );
		$created = eaccounting_insert_item( $posted );
		if ( is_wp_error( $created ) ) {
			wp_send_json_error(
				array(
					'message' => $created->get_error_message(),
				)
			);
		}

		$message  = __( 'Item updated successfully!', 'wp-ever-accounting' );
		$update   = empty( $posted['id'] ) ? false : true;
		$redirect = '';
		if ( ! $update ) {
			$message  = __( 'Item created successfully!', 'wp-ever-accounting' );
			$redirect = remove_query_arg( array( 'action' ), eaccounting_clean( $_REQUEST['_wp_http_referer'] ) );
		}
		wp_send_json_success(
			array(
				'message'  => $message,
				'redirect' => $redirect,
				'item'     => $created->get_data(),
			)
		);

		wp_die();
	}

	/**
	 * Check permission
	 *
	 * since 1.0.2
	 *
	 * @param string $cap
	 */
	public static function check_permission( $cap = 'manage_eaccounting' ) {
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error( array( 'message' => __( 'Error: You are not allowed to do this.', 'wp-ever-accounting' ) ) );
		}
	}

	/**
	 * Verify our ajax nonce.
	 *
	 * @param $action
	 *
	 * @param $action
	 *
	 * @since 1.0.2
	 *
	 */
	public static function verify_nonce( $action ) {
		$nonce = '';
		if ( isset( $_REQUEST['_ajax_nonce'] ) ) {
			$nonce = $_REQUEST['_ajax_nonce'];
		} elseif ( isset( $_REQUEST['_wpnonce'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];
		} elseif ( isset( $_REQUEST['nonce'] ) ) {
			$nonce = $_REQUEST['nonce'];
		}
		if ( false === wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error( array( 'message' => __( 'Error: Cheatin&#8217; huh?.', 'wp-ever-accounting' ) ) );
			wp_die();
		}

	}
}

return new Ajax();
