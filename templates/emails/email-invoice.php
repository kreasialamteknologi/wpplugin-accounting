<?php
/**
 * Email New Invoice
 *
 * This template can be overridden by copying it to yourtheme/eaccounting/emails/invoice/email-new-invoice.php.
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

// Generate the custom message body.
if ( isset( $message_body ) ) {
	echo $message_body;
}

// Print invoice details.
do_action( 'eaccounting_email_invoice_details', $invoice, $sent_to_admin );

// Print invoice items.
do_action( 'eaccounting_email_invoice_items', $invoice, $sent_to_admin );

// Print the billing details.
do_action( 'eaccounting_email_invoice_customer_details', $invoice, $sent_to_admin );


