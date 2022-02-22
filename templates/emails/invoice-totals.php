<tr>
	<td class="td" colspan="3" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
		<strong><?php esc_html_e( 'Subtotal', 'wp-ever-accounting' ); ?></strong>
	</td>
	<td class="td" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
		<?php echo esc_html( eaccounting_price( $invoice->get_subtotal(), $invoice->get_currency_code() ) ); ?>
	</td>
</tr>
<?php if ( ! empty( $invoice->get_total_tax() ) ) : ?>
	<tr>
		<td class="td" colspan="3" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<strong><?php esc_html_e( 'Tax', 'wp-ever-accounting' ); ?></strong>
		</td>
		<td class="td" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( eaccounting_price( $invoice->get_total_tax(), $invoice->get_currency_code() ) ); ?>
		</td>
	</tr>
<?php endif; ?>

<?php if ( ! empty( $invoice->get_total_discount() ) ) : ?>
	<tr>
		<td class="td" colspan="3" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<strong><?php esc_html_e( 'Discount', 'wp-ever-accounting' ); ?></strong>
		</td>
		<td class="td" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( eaccounting_price( $invoice->get_total_discount(), $invoice->get_currency_code() ) ); ?>
		</td>
	</tr>
<?php endif; ?>

<tr>
	<td class="td" colspan="3" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
		<strong><?php esc_html_e( 'Paid', 'wp-ever-accounting' ); ?></strong>
	</td>
	<td class="td" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
		<?php echo esc_html( eaccounting_price( $invoice->get_total_paid(), $invoice->get_currency_code() ) ); ?>
	</td>
</tr>

<?php if ( ! empty( $invoice->get_total_due() ) ) : ?>
	<tr>
		<td class="td" colspan="3" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<strong><?php esc_html_e( 'Due', 'wp-ever-accounting' ); ?></strong>
		</td>
		<td class="td" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( eaccounting_price( $invoice->get_total_due(), $invoice->get_currency_code() ) ); ?>
		</td>
	</tr>
<?php endif; ?>
