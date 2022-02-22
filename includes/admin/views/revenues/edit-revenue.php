<?php
/**
 * Admin Revenue Edit Page.
 * Page: Sales
 * Tab: Revenue
 *
 * @since       1.0.2
 * @subpackage  Admin/Views/Revenues
 * @package     EverAccounting
 */
defined( 'ABSPATH' ) || exit();

$revenue_id = isset( $_REQUEST['revenue_id'] ) ? absint( $_REQUEST['revenue_id'] ) : null;
try {
	$revenue = new \EverAccounting\Models\Revenue( $revenue_id );
} catch ( Exception $e ) {
	wp_die( $e->getMessage() );
}
?>
	<div class="ea-title-section">
		<div>
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Revenues', 'wp-ever-accounting' ); ?></h1>
			<?php if ( $revenue->exists() ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'revenues', 'page' => 'ea-sales', 'action' => 'add' ), admin_url( 'admin.php' ) ) );//phpcs:ignore ?>" class="page-title-action">
					<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo remove_query_arg( array( 'action', 'id' ) ); ?>" class="page-title-action"><?php esc_html_e( 'View All', 'wp-ever-accounting' ); ?></a>
			<?php endif; ?>

		</div>
	</div>
	<hr class="wp-header-end">

	<form id="ea-revenue-form" method="post" enctype="multipart/form-data">
		<div class="ea-card">

			<div class="ea-card__header">
				<h3 class="ea-card__title"><?php echo $revenue->exists() ? __( 'Update Revenue', 'wp-ever-accounting' ) : __( 'Add Revenue', 'wp-ever-accounting' ); ?></h3>
			</div>

			<div class="ea-card__inside">
				<div class="ea-row">
					<?php
					eaccounting_text_input(
						array(
							'wrapper_class' => 'ea-col-6',
							'label'         => __( 'Date', 'wp-ever-accounting' ),
							'name'          => 'payment_date',
							'placeholder'   => __( 'Enter Date', 'wp-ever-accounting' ),
							'data_type'     => 'date',
							'value'         => $revenue->get_payment_date() ? $revenue->get_payment_date() : null,
							'required'      => true,
						)
					);
					eaccounting_account_dropdown(
						array(
							'wrapper_class' => 'ea-col-6',
							'label'         => __( 'Account', 'wp-ever-accounting' ),
							'name'          => 'account_id',
							'value'         => $revenue->get_account_id(),
							'creatable'     => true,
							'placeholder'   => __( 'Select Account', 'wp-ever-accounting' ),
							'required'      => true,
						)
					);

					eaccounting_text_input(
						array(
							'label'         => __( 'Amount', 'wp-ever-accounting' ),
							'name'          => 'amount',
							'value'         => $revenue->get_amount(),
							'required'      => true,
							'wrapper_class' => 'ea-col-6',
							'placeholder'   => __( 'Enter Amount', 'wp-ever-accounting' ),
						)
					);
					eaccounting_customer_dropdown(
						array(
							'wrapper_class' => 'ea-col-6',
							'label'         => __( 'Customer', 'wp-ever-accounting' ),
							'name'          => 'contact_id',
							'id'            => 'customer_id',
							'value'         => $revenue->get_contact_id(),
							'placeholder'   => __( 'Select Customer', 'wp-ever-accounting' ),
							'type'          => 'customer',
							'creatable'     => true,
						)
					);
					eaccounting_category_dropdown(
						array(
							'wrapper_class' => 'ea-col-6',
							'label'         => __( 'Category', 'wp-ever-accounting' ),
							'name'          => 'category_id',
							'value'         => $revenue->get_category_id(),
							'required'      => true,
							'type'          => 'income',
							'creatable'     => true,
						)
					);
					eaccounting_payment_method_dropdown(
						array(
							'label'         => __( 'Payment Method', 'wp-ever-accounting' ),
							'name'          => 'payment_method',
							'wrapper_class' => 'ea-col-6',
							'required'      => true,
							'value'         => $revenue->get_payment_method(),
						)
					);
					eaccounting_textarea(
						array(
							'label'         => __( 'Description', 'wp-ever-accounting' ),
							'name'          => 'description',
							'value'         => $revenue->get_description(),
							'required'      => false,
							'wrapper_class' => 'ea-col-12',
							'placeholder'   => __( 'Enter description', 'wp-ever-accounting' ),
						)
					);
					eaccounting_text_input(
						array(
							'label'         => __( 'Reference', 'wp-ever-accounting' ),
							'name'          => 'reference',
							'value'         => $revenue->get_reference(),
							'required'      => false,
							'wrapper_class' => 'ea-col-6',
							'placeholder'   => __( 'Enter reference', 'wp-ever-accounting' ),
						)
					);
					eaccounting_file_input(
						array(
							'label'         => __( 'Attachments', 'wp-ever-accounting' ),
							'name'          => 'attachment_id',
							'allowed-types' => 'jpg,jpeg,png,pdf',
							'value'         => $revenue->get_attachment_id(),
							'required'      => false,
							'wrapper_class' => 'ea-col-6',
							'placeholder'   => __( 'Upload File', 'wp-ever-accounting' ),
						)
					);
					eaccounting_hidden_input(
						array(
							'name'  => 'id',
							'value' => $revenue->get_id(),
						)
					);

					eaccounting_hidden_input(
						array(
							'name'  => 'action',
							'value' => 'eaccounting_edit_revenue',
						)
					);
					?>
				</div>
			</div>

			<div class="ea-card__footer">
				<?php wp_nonce_field( 'ea_edit_revenue' ); ?>
				<?php submit_button( __( 'Submit', 'wp-ever-accounting' ), 'primary', 'submit' ); ?>
			</div>

		</div>
	</form>
<?php
eaccounting_enqueue_js(
	"
	jQuery('#ea-revenue-form #amount').inputmask('decimal', {
			alias: 'numeric',
			groupSeparator: '" . $revenue->get_currency_thousand_separator() . "',
			autoGroup: true,
			digits: '" . $revenue->get_currency_precision() . "',
			radixPoint: '" . $revenue->get_currency_decimal_separator() . "',
			digitsOptional: false,
			allowMinus: false,
			prefix: '" . $revenue->get_currency_symbol() . "',
			placeholder: '0.000',
			rightAlign: 0,
			autoUnmask: true
		});
"
);
