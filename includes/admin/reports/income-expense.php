<?php
defined( 'ABSPATH' ) || exit();

function eaccounting_reports_income_expense_tab() {
	$year       = isset( $_REQUEST['year'] ) ? intval( $_REQUEST['year'] ) : date( 'Y' );
	$account_id = isset( $_REQUEST['account_id'] ) ? intval( $_REQUEST['account_id'] ) : '';
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
					'value' => 'income_expense',
				)
			);

			$years = range( date( 'Y' ), ( $year - 5 ), 1 );
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
					'default'     => '',
					'value'       => $account_id,
					'attr'        => array(
						'data-allow-clear' => true,
					),

				)
			);
			submit_button( __( 'Filter', 'wp-ever-accounting' ), 'action', false, false );
			?>
		</form>
	</div>
	<div class="ea-card">
		<?php
		global $wpdb;
		$dates        = $totals = $compares = $graph = $categories = array();
		$date_start   = eaccounting_get_financial_start( $year );
		$end          = eaccounting_get_financial_end( $year );
		$income_cats  = eaccounting_get_categories(
			array(
				'number' => - 1,
				'fields' => array( 'id', 'name' ),
				'type'   => 'income',
				'return' => 'raw',
			)
		);
		$expense_cats = eaccounting_get_categories(
			array(
				'number' => - 1,
				'fields' => array( 'id', 'name' ),
				'type'   => 'expense',
				'return' => 'raw',
			)
		);
		$income_cats  = wp_list_pluck( $income_cats, 'name', 'id' );
		$expense_cats = wp_list_pluck( $expense_cats, 'name', 'id' );
		$date         = new \EverAccounting\DateTime( $date_start );

		// Dates
		for ( $j = 1; $j <= 12; $j ++ ) {
			$dates[ $j ]                     = $date->format( 'F' );
			$graph[ $date->format( 'F-Y' ) ] = 0;
			// Totals
			$totals[ $dates[ $j ] ] = array(
				'amount' => 0,
			);
			foreach ( $income_cats as $category_id => $category_name ) {
				$compares['income'][ $category_id ][ $dates[ $j ] ] = array(
					'category_id' => $category_id,
					'name'        => $category_name,
					'amount'      => 0,
				);
			}
			foreach ( $expense_cats as $category_id => $category_name ) {
				$compares['expense'][ $category_id ][ $dates[ $j ] ] = array(
					'category_id' => $category_id,
					'name'        => $category_name,
					'amount'      => 0,
				);
			}
			$date->modify( '+1 month' )->format( 'Y-m' );
		}

		$where = "category_id NOT IN ( SELECT id from {$wpdb->prefix}ea_categories WHERE type='other')";
		$where .= $wpdb->prepare( ' AND (payment_date BETWEEN %s AND %s)', $date_start, $end );
		if ( ! empty( $account_id ) ) {
			$where .= $wpdb->prepare( ' AND account_id=%d', $account_id );
		}

		$transactions = $wpdb->get_results( "

		SELECT `type`, payment_date, currency_code, currency_rate, amount, category_id
		FROM {$wpdb->prefix}ea_transactions
		WHERE $where
		" );

		foreach ( $transactions as $transaction ) {
			$amount     = eaccounting_price_to_default( $transaction->amount, $transaction->currency_code, $transaction->currency_rate );
			$month      = date( 'F', strtotime( $transaction->payment_date ) );
			$month_year = date( 'F-Y', strtotime( $transaction->payment_date ) );

			if ( $transaction->type == 'income' && isset( $compares['income'][ $transaction->category_id ] ) ) {
				$compares['income'][ $transaction->category_id ][ $month ]['amount'] += $amount;
				$graph[ $month_year ]                                                += $amount;
				$totals[ $month ]['amount']                                          += $amount;
			} elseif ( $transaction->type == 'expense' && isset( $compares['expense'][ $transaction->category_id ] ) ) {
				$compares['expense'][ $transaction->category_id ][ $month ]['amount'] += $amount;
				$graph[ $month_year ]                                                 -= $amount;
				$totals[ $month ]['amount']                                           -= $amount;
			}
		}

		$chart = new \EverAccounting\Chart();
		$chart->type( 'line' )
			  ->width( 0 )
			  ->height( 300 )
			  ->set_line_options()
			  ->labels( array_values( $dates ) )
			  ->dataset(
				  array(
					  'label'           => __( 'Profit', 'wp-ever-accounting' ),
					  'data'            => array_values( $graph ),
					  'borderColor'     => '#06d6a0',
					  'backgroundColor' => '#06d6a0',
					  'borderWidth'     => 4,
					  'pointStyle'      => 'line',
					  'fill'            => false,
				  )
			  );
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

				<?php if ( ! empty( $compares ) ) : ?>
					<?php foreach ( $compares as $type => $categories ) : ?>
						<?php foreach ( $categories as $category_id => $category ) : ?>
							<tr>
								<?php if ( 'income' == $type ) : ?>
									<td><?php echo $income_cats[ $category_id ]; ?></td>
								<?php else : ?>
									<td><?php echo $expense_cats[ $category_id ]; ?></td>
								<?php endif; ?>
								<?php foreach ( $category as $item ) : ?>

									<?php if ( 'income' == $type ) : ?>
										<td class="align-right"><?php echo eaccounting_format_price( $item['amount'] ); ?></td>
									<?php else : ?>
										<td class="align-right">
											-<?php echo eaccounting_format_price( $item['amount'] ); ?></td>
									<?php endif; ?>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
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

add_action( 'eaccounting_reports_tab_income_expense', 'eaccounting_reports_income_expense_tab' );
