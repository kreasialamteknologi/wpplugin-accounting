<?php
/**
 * Invoice Details
 *
 * This template can be overridden by copying it to yourtheme/eaccounting/invoice/email-invoice-details.php.
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

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';
?>
<div style="margin-bottom: 40px;">
	<h2><?php esc_html_e( 'Invoice Items', 'wp-ever-accounting' ); ?></h2>

	<table class="td table-bordered" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
		<tr>
			<th class="td text-left" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Item', 'wp-ever-accounting' ); ?></th>
			<th class="td text-right" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'wp-ever-accounting' ); ?></th>
			<th class="td text-right" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'wp-ever-accounting' ); ?></th>
			<th class="td text-right" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Subtotal', 'wp-ever-accounting' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		eaccounting_get_template(
			'emails/invoice-items.php',
			array(
				'invoice'       => $invoice,
				'sent_to_admin' => $sent_to_admin,
			)
		);
		?>
		</tbody>
		<tfoot>
		<?php
		eaccounting_get_template(
			'emails/invoice-totals.php',
			array(
				'invoice'       => $invoice,
				'sent_to_admin' => $sent_to_admin,
			)
		);
		?>
		</tfoot>
	</table>

</div>
