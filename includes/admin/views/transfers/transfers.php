<?php
/**
 * Admin Transfers Page.
 *
 * @package     EverAccounting
 * @subpackage  Admin/Banking/Transfers
 * @since       1.0.2
 */
defined( 'ABSPATH' ) || exit();


function eaccounting_render_transfers_tab() {
	$requested_view = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
	if ( in_array( $requested_view, array( 'add', 'edit' ), true ) ) {
		$transfer_id = isset( $_GET['transfer_id'] ) ? absint( $_GET['transfer_id'] ) : null;
		include dirname( __FILE__ ) . '/edit-transfer.php';
	} else {
		include dirname( __FILE__ ) . '/list-transfer.php';
	}
}

add_action( 'eaccounting_banking_tab_transfers', 'eaccounting_render_transfers_tab' );
