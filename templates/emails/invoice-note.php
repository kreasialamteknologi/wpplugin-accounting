<?php
/**
 * Customer note email
 *
 * This template can be overridden by copying it to yourtheme/eaccounting/invoice/items.php.
 *
 * HOWEVER, on occasion WP Ever Accounting will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package EverAccounting\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php /* translators: %s: Customer first name */ ?>
	<p><?php printf( esc_html__( 'Hi %s,', 'wp-ever-accounting' ), esc_html( $invoice->get_name() ) ); ?></p>
	<p><?php esc_html_e( 'The following note has been added to your invoice:', 'wp-ever-accounting' ); ?></p>

	<blockquote><?php echo wpautop( wptexturize( make_clickable( $note ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></blockquote>

	<p><?php esc_html_e( 'As a reminder, here are your invoice details:', 'wp-ever-accounting' ); ?></p>

<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
/*
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}
*/

