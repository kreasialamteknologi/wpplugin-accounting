<?php
$margin_side = is_rtl() ? 'left' : 'right';

foreach ( $invoice->get_line_items() as $item_id => $item ) : ?>
	<tr>
		<td class="td" style="text-align:left; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( $item->get_item_name() ); ?>
		</td>

		<td class="td text-right" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( eaccounting_price( $item->get_unit_price(), $invoice->get_currency_code() ) ); ?>
		</td>

		<td class="td text-right" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( $item->get_quantity() ); ?>
		</td>

		<td class="td text-right" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( eaccounting_price( $item->get_subtotal(), $invoice->get_currency_code() ) ); ?>
		</td>
	</tr>

<?php endforeach; ?>
