<?php

use \EverAccounting\Query_Transaction;

function eaccounting_reports_profit_loss_tab() {
	$year        = isset( $_REQUEST['year'] ) ? intval( $_REQUEST['year'] ) : date( 'Y' );
	$category_id = isset( $_REQUEST['category_id'] ) ? intval( $_REQUEST['category_id'] ) : '';
	$account_id  = isset( $_REQUEST['account_id'] ) ? intval( $_REQUEST['account_id'] ) : '';
	$vendor_id   = isset( $_REQUEST['vendor_id'] ) ? intval( $_REQUEST['vendor_id'] ) : '';

	?>
	<div class="ea-card is-compact">
		<form action="" class="ea-report-filter">
			<?php
			eaccounting_hidden_input(
				array(
					'name'  => 'page',
					'value' => 'ea-reports',
				)
			);
			eaccounting_hidden_input(
				array(
					'name'  => 'tab',
					'value' => 'profit_loss',
				)
			);

			$years = range( $year, ( $year - 5 ), 1 );
			eaccounting_select2(
				array(
					'placeholder' => __( 'Year', 'wp-ever-accounting' ),
					'name'        => 'year',
					'options'     => array_combine( array_values( $years ), $years ),
					'value'       => $year,
				)
			);
			eaccounting_account_dropdown(
				array(
					'placeholder' => __( 'Account', 'wp-ever-accounting' ),
					'name'        => 'account_id',
					'value'       => $account_id,
				)
			);
			submit_button( __( 'Filter', 'wp-ever-accounting' ), 'action', false, false );
			?>
		</form>
	</div>
	<div class="ea-card">
		<?php
		global $wpdb;
		$dates        = $totals = $expenses = $graph = $categories = array();
		$start        = eaccounting_get_financial_start( $year );
		$transactions = \EverAccounting\Transactions\query()
				->select( 'name, payment_date, currency_code, currency_rate, amount, ea_categories.id category_id' )
				->where_raw( $wpdb->prepare( 'YEAR(payment_date) = %d', $year ) )
				->where(
					array(
						'contact_id'  => $vendor_id,
						'account_id'  => $account_id,
						'category_id' => $category_id,
					)
				)
				->left_join( 'ea_categories', 'ea_categories.id', 'ea_transactions.category_id' )
				->where( 'ea_categories.type', 'expense' )
				->get(
					OBJECT,
					function ( $expense ) {
						$expense->amount = eaccounting_price_to_default( $expense->amount, $expense->currency_code, $expense->currency_rate );

						return $expense;
					}
				);

		$categories = wp_list_pluck( $transactions, 'name', 'category_id' );
		$date       = new \EverAccounting\DateTime( $start );
		// Dates
		for ( $j = 1; $j <= 12; $j ++ ) {
			$dates[ $j ]                     = $date->format( 'F' );
			$graph[ $date->format( 'F-Y' ) ] = 0;
			// Totals
			$totals[ $dates[ $j ] ] = array(
				'amount' => 0,
			);

			foreach ( $categories as $cat_id => $category_name ) {
				$expenses[ $cat_id ][ $dates[ $j ] ] = array(
					'category_id' => $cat_id,
					'name'        => $category_name,
					'amount'      => 0,
				);
			}
			$date->modify( '+1 month' )->format( 'Y-m' );
		}

		foreach ( $transactions as $transaction ) {
			$month      = date( 'F', strtotime( $transaction->payment_date ) );
			$month_year = date( 'F-Y', strtotime( $transaction->payment_date ) );
			$expenses[ $transaction->category_id ][ $month ]['amount'] += $transaction->amount;
			$graph[ $month_year ]                                      += $transaction->amount;
			$totals[ $month ]['amount']                                += $transaction->amount;
		}
		$chart = new \EverAccounting\Chart();
		$chart->type( 'line' )
			  ->width( 0 )
			  ->height( 300 )
			  ->set_line_options()
			  ->labels( array_values( $dates ) )
			->dataset(
				array(
					'label'           => __( 'Expense', 'wp-ever-accounting' ),
					'data'            => array_values( $graph ),
					'borderColor'     => '#f2385a',
					'backgroundColor' => '#f2385a',
					'borderWidth'     => 4,
					'pointStyle'      => 'line',
					'fill'            => false,
				)
			)
		?>
		<div class="ea-report-graph">
			<?php $chart->render(); ?>
		</div>
		<div class="ea-table-report">
			<table class="ea-table">
				<thead>
				<tr>
					<th><?php _e( 'Categories', 'wp-ever-accounting' ); ?></th>
					<?php foreach ( $dates as $date ) : ?>
						<th class="align-right"><?php echo $date; ?></th>
					<?php endforeach; ?>
				</tr>
				</thead>
				<tbody>

				<?php if ( ! empty( $expenses ) ) : ?>
					<?php foreach ( $expenses as $category_id => $category ) : ?>
						<tr>
							<td><?php echo $categories[ $category_id ]; ?></td>
							<?php foreach ( $category as $item ) : ?>
								<td class="align-right"><?php echo eaccounting_format_price( $item['amount'] ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="no-results">
						<td colspan="13">
							<p><?php _e( 'No records found', 'wp-ever-accounting' ); ?></p>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
				<tfoot>
				<tr>
					<th><?php _e( 'Total', 'wp-ever-accounting' ); ?></th>
					<?php foreach ( $totals as $total ) : ?>
						<th class="align-right"><?php echo eaccounting_format_price( $total['amount'] ); ?></th>
					<?php endforeach; ?>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<?php
}

add_action( 'eaccounting_reports_tab_profit_loss', 'eaccounting_reports_profit_loss_tab' );
