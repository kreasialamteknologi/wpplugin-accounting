<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * This template can be overridden by copying it to yourtheme/eaccounting/global/footer.php.
 *
 * @since 1.1.0
 */

defined( 'ABSPATH' ) || exit;
$host = eaccounting_get_site_name();
?>
</div><!--/ea-body-->
<footer class="ea-footer ea-noprint">
	<div class="ea-container">
		<p class="ea-copyright-info">
			<?php echo date_i18n( 'Y' ); ?>
			<?php echo sprintf(esc_html__('Copyright %s', 'wp-ever-accounting'), $host);?>
		</p>
	</div>
</footer>
