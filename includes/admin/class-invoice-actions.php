<?php
/**
 * Admin Invoice Page
 *
 * Functions used for displaying invoice related pages.
 *
 * @author      EverAccounting
 * @category    Admin
 * @package     EverAccounting\Admin
 * @version     1.1.10
 */

namespace EverAccounting\Admin;

use EverAccounting\Models\Invoice;

defined( 'ABSPATH' ) || exit();

/**
 * Invoice_Actions class
 * @package EverAccounting\Admin
 *
 * @since 1.1.0
 */
class Invoice_Actions {
	/**
	 * Invoice_Actions constructor.
	 */
	public function __construct() {
		add_action( 'admin_post_eaccounting_invoice_action', array( $this, 'invoice_action' ) );
	}

	/**
	 * Invoice actions
	 *
	 * @since 1.1.0
	 */
	public function invoice_action() {
		$action     = eaccounting_clean( wp_unslash( $_REQUEST['invoice_action'] ) );
		$invoice_id = absint( wp_unslash( $_REQUEST['invoice_id'] ) );
		$invoice    = eaccounting_get_invoice( $invoice_id );

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'ea_invoice_action' ) || ! current_user_can( 'ea_manage_invoice' ) || ! $invoice->exists() ) {
			wp_die( __( 'no cheating!', 'wp-ever-accounting' ) );
		}
		$redirect_url = add_query_arg(
			array(
				'page'       => 'ea-sales',
				'tab'        => 'invoices',
				'action'     => 'view',
				'invoice_id' => $invoice_id,
			),
			admin_url( 'admin.php' )
		);
		switch ( $action ) {
			case 'status_pending':
				try {
					$invoice->set_status( 'pending' );
					$invoice->save();
					eaccounting_admin_notices()->add_success( __( 'Invoice status updated to pending.', 'wp-ever-accounting' ) );
				} catch ( \Exception $e ) {
					/* translators: %s reason */
					eaccounting_admin_notices()->add_error( sprintf( __( 'Invoice status was not changes : %s ', 'wp-ever-accounting' ), $e->getMessage() ) );
				}
				break;
			case 'status_cancelled':
				$invoice->set_cancelled();
				break;
			case 'status_refunded':
				$invoice->set_refunded();
				break;
			case 'status_paid':
				$invoice->set_paid();
				break;
			case 'delete':
				$invoice->delete();
				$redirect_url = remove_query_arg( array( 'action', 'invoice_id' ), $redirect_url );
				break;
		}

		if ( ! did_action( 'eaccounting_invoice_action_' . sanitize_title( $action ) ) ) {
			do_action( 'eaccounting_invoice_action_' . sanitize_title( $action ), $invoice, $redirect_url );
		}
		wp_redirect( $redirect_url ); //phpcs:ignore
		exit();
	}

	/**
	 * View invoice.
	 *
	 * @param $invoice_id
	 *
	 * @since 1.1.0
	 *
	 */
	public static function view_invoice( $invoice_id = null ) {
		try {
			$invoice = new Invoice( $invoice_id );
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}

		if ( empty( $invoice ) || ! $invoice->exists() ) {
			wp_die( __( 'Sorry, Invoice does not exist', 'wp-ever-accounting' ) );
		}

		eaccounting_get_admin_template(
			'invoices/view-invoice',
			array(
				'invoice' => $invoice,
				'action'  => 'view',
			)
		);
	}

	/**
	 * @param $invoice_id
	 *
	 * @param $invoice_id
	 *
	 * @since 1.1.0
	 *
	 */
	public static function edit_invoice( $invoice_id = null ) {
		try {
			$invoice = new Invoice( $invoice_id );
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}
		eaccounting_get_admin_template(
			'invoices/edit-invoice',
			array(
				'invoice' => $invoice,
				'action'  => 'edit',
			)
		);
	}

	/**
	 * Get invoice notes.
	 *
	 * @param Invoice $invoice
	 *
	 * @param Invoice $invoice
	 *
	 * @since 1.1.0
	 *
	 */
	public static function invoice_notes( $invoice ) {
		if ( ! $invoice->exists() ) {
			return;
		}
		eaccounting_get_admin_template( 'invoices/invoice-notes', array( 'invoice' => $invoice ) );
	}

	/**
	 * Get invoice payments.
	 *
	 * @param Invoice $invoice
	 *
	 * @since 1.1.0
	 *
	 */
	public static function invoice_payments( $invoice ) {
		if ( ! $invoice->exists() ) {
			return;
		}
		eaccounting_get_admin_template( 'invoices/invoice-payments', array( 'invoice' => $invoice ) );
	}
}

 new Invoice_Actions();
