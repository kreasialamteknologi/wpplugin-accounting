<?php
/**
 * Displays single line items in emails.
 *
 * This template can be overridden by copying it to yourtheme/eaccounting/invoice/email-invoice-item.php.
 *
 * HOWEVER, on occasion WP Ever Accounting will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package eaccounting\Templates
 * @version 1.0.0
 */

$margin_side = is_rtl() ? 'left' : 'right';

foreach ( $invoice->get_line_items() as $item_id => $item ) : ?>
	<tr>
		<td class="td" style="text-align:left; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( $item->get_item_name() ); ?>
		</td>

		<td class="td text-right" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( $invoice->get_formatted_line_amount( $item, 'item_price' ) ); ?>
		</td>

		<td class="td text-right" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo esc_html( $item->get_quantity() ); ?>
		</td>

		<td class="td text-right" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			<?php echo wp_kses_post( $invoice->get_formatted_line_amount( $item ) ); ?>
		</td>
	</tr>

<?php endforeach; ?>
