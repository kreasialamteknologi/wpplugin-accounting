<?php
/**
 * Admin Reconciliation Edit Page.
 *
 * @since       1.0.2
 * @subpackage  Admin/Banking/Reconciliations
 * @package     EverAccounting
 */
defined( 'ABSPATH' ) || exit();
?>

<div class="ea-card">
	<form action="">
		<div class="ea-row">
			<div class="ea-col-2">
				<?php
				eaccounting_text_input(
					array(
						'label'     => __( 'Start Date', 'wp-ever-accounting' ),
						'name'      => 'started_at',
						'data_type' => 'date',
						'required'  => true,
					)
				);
				?>
			</div>
			<div class="ea-col-2">
				<?php
				eaccounting_text_input(
					array(
						'label'     => __( 'End Date', 'wp-ever-accounting' ),
						'name'      => 'ended_at',
						'data_type' => 'date',
						'required'  => true,
					)
				);
				?>
			</div>
			<div class="ea-col-2">
				<?php
				eaccounting_text_input(
					array(
						'label'     => __( 'Closing Balance', 'wp-ever-accounting' ),
						'name'      => 'amount',
						'data_type' => 'price',
						'required'  => true,
					)
				);
				?>
			</div>
			<div class="ea-col-2">
				<?php
				eaccounting_account_dropdown(
					array(
						'label'    => __( 'Account', 'wp-ever-accounting' ),
						'name'     => 'account_id',
						'required' => true,
					)
				);
				?>
			</div>
			<div class="ea-col-2">
				<br>
				<?php
				submit_button( __( 'Transactions', 'wp-ever-accounting' ), 'primary', false, false );
				?>
			</div>
		</div>
		<input type="hidden" name="page" value="ea-banking">
		<input type="hidden" name="tab" value="reconciliations">
		<input type="hidden" name="action" value="add">
	</form>
</div>
