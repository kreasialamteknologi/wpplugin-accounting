<?php
/**
 * Admin Reconciliations Page.
 *
 * @package     EverAccounting
 * @subpackage  Admin/Banking/Reconciliations
 * @since       1.0.2
 */
defined( 'ABSPATH' ) || exit();


function eaccounting_banking_tab_reconciliations() {
	?>

		<div class="ea-row">
			<div class="ea-col-12">
				<span class="ea-control-label"><?php _e( 'Items', 'wp-ever-accounting' ); ?></span>
				<div class="ea-transaction-table-wrap">
					<table class="ea-transaction-table" id="transaction-items">
						<thead>
						<tr>
							<th><?php _e( 'Actions', 'wp-ever-accounting' ); ?></th>
							<th><?php _e( 'Name', 'wp-ever-accounting' ); ?></th>
							<th><?php _e( 'Quantity', 'wp-ever-accounting' ); ?></th>
							<th><?php _e( 'Price', 'wp-ever-accounting' ); ?></th>
							<th><?php _e( 'Tax', 'wp-ever-accounting' ); ?></th>
							<th><?php _e( 'Total', 'wp-ever-accounting' ); ?></th>
						</tr>
						</thead>

						<tbody>

						<tr id="tr-add-item">
							<td>
								<button id="ea-button-add-item" class="button-primary button button-small">
									<i class="fa fa-plus"></i>
								</button>
							</td>
							<td colspan="5"></td>
						</tr>

						<tr id="tr-subtotal">
							<td colspan="5"><strong>Subtotal</strong></td>
							<td ><span id="sub-total">$10,000.00</span></td>
						</tr>

						<tr id="tr-discount">
							<td colspan="5">Add Discount</td>
							<td>
								<span id="discount-total"></span>
								<input id="discount" class="ea-form-control" name="discount" type="text" value="">
							</td>
						</tr>

						<tr id="tr-tax">
							<td colspan="5">
								<strong>Tax</strong>
							</td>
							<td><span id="tax-total">$100.00</span></td>
						</tr>

						<tr id="tr-total">
							<td colspan="5"><strong>Total</strong></td>
							<td><span id="grand-total">$10,100.00</span></td>
						</tr>

						</tbody>


					</table>
				</div>


			</div>

		</div>

	<?php
	$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : null;

	if ( in_array( $action, [ 'edit', 'add' ] ) ) {
		include __DIR__ . '/edit-reconciliation.php';
	} else {
		?>
		<h1>
			<?php _e( 'Reconciliations', 'wp-ever-accounting' ); ?>
			<a class="page-title-action" href="
			<?php
			echo eaccounting_admin_url(
				array(
					'tab'    => 'reconciliations',
					'action' => 'add',
				)
			);
			?>
												"><?php _e( 'Add New', 'wp-ever-accounting' ); ?></a>
		</h1>
		<?php
		require_once EACCOUNTING_ABSPATH . '/includes/admin/list-tables/list-table-reconciliations.php';
		$reconciliations_table = new \EverAccounting\Admin\ListTables\List_Table_Transfers();
		$reconciliations_table->prepare_items();
		?>
		<div class="wrap">
			<?php

			/**
			 * Fires at the top of the admin reconciliations page.
			 *
			 * Use this hook to add content to this section of reconciliations.
			 *
			 * @since 1.0.2
			 */
			do_action( 'eaccounting_reconciliations_page_top' );

			?>
			<form id="ea-reconciliations-filter" method="get" action="<?php echo esc_url( eaccounting_admin_url() ); ?>">
				<?php // $reconciliations_table->search_box( __( 'Search', 'wp-ever-accounting' ), 'eaccounting-reconciliations' ); ?>

				<input type="hidden" name="page" value="ea-banking"/>
				<input type="hidden" name="tab" value="reconciliations"/>

				<?php $reconciliations_table->views(); ?>
				<?php $reconciliations_table->display(); ?>
			</form>
			<?php
			/**
			 * Fires at the bottom of the admin reconciliations page.
			 *
			 * Use this hook to add content to this section of reconciliations Tab.
			 *
			 * @since 1.0.2
			 */
			do_action( 'eaccounting_reconciliations_page_bottom' );
			?>
		</div>
		<?php
	}
}

add_action( 'eaccounting_banking_tab_reconciliations', 'eaccounting_banking_tab_reconciliations' );
