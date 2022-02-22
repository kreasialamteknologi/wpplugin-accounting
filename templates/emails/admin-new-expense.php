<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/eaccounting/invoice/items.php.
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

do_action( 'eaccounting_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer billing full name */ ?>
<p><?php printf( esc_html__( 'New expense added for the vendor %s:', 'wp-ever-accounting' ), $expense->get_vendor_name() );?></p>
