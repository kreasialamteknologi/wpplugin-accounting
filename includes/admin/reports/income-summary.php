<?php
defined( 'ABSPATH' ) || exit();

function eaccounting_reports_income_summary_tab() {
	$year        = isset( $_REQUEST['year'] ) ? intval( $_REQUEST['year'] ) : date( 'Y' );
	$category_id = isset( $_REQUEST['category_id'] ) ? absint( $_REQUEST['category_id'] ) : '';
	$account_id  = isset( $_REQUEST['account_id'] ) ? absint( $_REQUEST['account_id'] ) : '';
	$customer_id = isset( $_REQUEST['customer_id'] ) ? absint( $_REQUEST['customer_id'] ) : '';

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
					'value' => 'income_summary',
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
					'default'     => '',
					'name'        => 'account_id',
					'value'       => $account_id,
					'attr'        => array(
						'data-allow-clear' => true,
					),
				)
			);
			eaccounting_contact_dropdown(
				array(
					'placeholder' => __( 'Customer', 'wp-ever-accounting' ),
					'name'        => 'customer_id',
					'type'        => 'customer',
					'value'       => $customer_id,
					'attr'        => array(
						'data-allow-clear' => true,
					),
				)
			);
			eaccounting_category_dropdown(
				array(
					'placeholder' => __( 'Category', 'wp-ever-accounting' ),
					'name'        => 'category_id',
					'default'     => '',
					'type'        => 'income',
					'value'       => $category_id,
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
		$dates = $totals = $incomes = $graph = $categories = array();
		$start = eaccounting_get_financial_start( $year );
		$end   = eaccounting_get_financial_end( $year );
		$where = "category_id NOT IN ( SELECT id from {$wpdb->prefix}ea_categories WHERE type='other')";
		$where .= $wpdb->prepare( ' AND (payment_date BETWEEN %s AND %s)', $start, $end );
		if ( ! empty( $account_id ) ) {
			$where .= $wpdb->prepare( ' AND account_id=%d', $account_id );
		}
		if ( ! empty( $vendor_id ) ) {
			$where .= $wpdb->prepare( ' AND contact_id=%d', $vendor_id );
		}
		if ( ! empty( $category_id ) ) {
			$where .= $wpdb->prepare( ' AND category_id=%d', $category_id );
		}

		$transactions = $wpdb->get_results(
			"
		SELECT name, payment_date, currency_code, currency_rate, amount, ea_categories.id category_id
		FROM {$wpdb->prefix}ea_transactions ea_transactions
		LEFT JOIN {$wpdb->prefix}ea_categories ea_categories ON ea_categories.id=ea_transactions.category_id
		WHERE $where AND ea_transactions.type = 'income'
		"
		);

		foreach ( $transactions as $key => $transaction ) {
			$transaction->amount = eaccounting_price_to_default( $transaction->amount, $transaction->currency_code, $transaction->currency_rate );

			$transactions[ $key ] = $transaction;
		}

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
				$incomes[ $cat_id ][ $dates[ $j ] ] = array(
					'category_id' => $cat_id,
					'name'        => $category_name,
					'amount'      => 0,
				);
			}
			$date->modify( '+1 month' )->format( 'Y-m' );
		}

		foreach ( $transactions as $transaction ) {
			if ( isset( $incomes[ $transaction->category_id ] ) ) {
				$month                                                    = date( 'F', strtotime( $transaction->payment_date ) );
				$month_year                                               = date( 'F-Y', strtotime( $transaction->payment_date ) );
				$incomes[ $transaction->category_id ][ $month ]['amount'] += $transaction->amount;
				$graph[ $month_year ]                                     += $transaction->amount;
				$totals[ $month ]['amount']                               += $transaction->amount;
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
					  'label'           => __( 'Income', 'wp-ever-accounting' ),
					  'data'            => array_values( $graph ),
					  'borderColor'     => '#3644ff',
					  'backgroundColor' => '#3644ff',
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

				<?php if ( ! empty( $incomes ) ) : ?>
					<?php foreach ( $incomes as $category_id => $category ) : ?>
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

add_action( 'eaccounting_reports_tab_income_summary', 'eaccounting_reports_income_summary_tab' );
