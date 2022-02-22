<?php
/**
 * Bills Admin List Table
 *
 * @since       1.1.0
 * @subpackage  EverAccounting\Admin\ListTables
 * @package     EverAccounting
 */

use EverAccounting\Models\Bill;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( '\EverAccounting_List_Table' ) ) {
	require_once dirname( __FILE__ ) . '/class-list-table.php';
}

/**
 * Class EverAccounting_Bill_List_Table
 * @since 1.1.0
 */
class EverAccounting_Bill_List_Table extends EverAccounting_List_Table {
	/**
	 * Default number of items to show per page
	 *
	 * @since 1.1.0
	 * @var string
	 */
	public $per_page = 20;

	/**
	 * Total number of item found
	 *
	 * @since 1.1.0
	 * @var int
	 */
	public $total_count;

	/**
	 * Get things started
	 *
	 * @param array $args Optional. Arbitrary display and query arguments to pass through the list table. Default empty array.
	 *
	 * @since  1.1.0
	 *
	 * @see WP_List_Table::__construct()
	 */
	public function __construct( $args = array() ) {
		$args = (array) wp_parse_args(
			$args,
			array(
				'singular' => 'bill',
				'plural'   => 'bills',
			)
		);

		parent::__construct( $args );
	}

	/**
	 * Check if there is contents in the database.
	 *
	 * @return bool
	 * @since 1.1.0
	 */
	public function is_empty() {
		global $wpdb;

		return ! (int) $wpdb->get_var( "SELECT COUNT(id) from {$wpdb->prefix}ea_documents where type='bill'" );
	}

	/**
	 * Render blank state.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	protected function render_blank_state() {
		?>
		<div class="ea-empty-table">
			<p class="ea-empty-table__message">
				<?php echo esc_html__( 'Create and manage bills so your finances are always accurate and healthy. Print and share bill with your vendor. Bill also support tax calculation & discount.', 'wp-ever-accounting' ); ?>
			</p>
			<a href="<?php echo esc_url( eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'bills', 'action' => 'edit', ) ) ); //phpcs:ignore ?>" class="button-primary ea-empty-table__cta"><?php _e( 'Add Bills', 'wp-ever-accounting' ); ?></a>
			<a href="https://wpeveraccounting.com/docs/general/add-bills/?utm_source=listtable&utm_medium=link&utm_campaign=admin" class="button-secondary ea-empty-table__cta" target="_blank"><?php _e( 'Learn More', 'wp-ever-accounting' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function define_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'bill_number' => __( 'Number', 'wp-ever-accounting' ),
			'total'       => __( 'Total', 'wp-ever-accounting' ),
			'name'        => __( 'Vendor', 'wp-ever-accounting' ),
			'issue_date'  => __( 'Bill Date', 'wp-ever-accounting' ),
			'due_date'    => __( 'Due Date', 'wp-ever-accounting' ),
			'status'      => __( 'Status', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array
	 * @since 1.1.0
	 */
	protected function define_sortable_columns() {
		return array(
			'bill_number' => array( 'bill_number', false ),
			'name'        => array( 'name', false ),
			'total'       => array( 'total', false ),
			'issue_date'  => array( 'issue_date', false ),
			'due_date'    => array( 'due_date', false ),
			'status'      => array( 'status', false ),
		);
	}

	/**
	 * Define bulk actions
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function define_bulk_actions() {
		return array(
			'cancel'   => __( 'Cancel', 'wp-ever-accounting' ),
			'paid'     => __( 'Paid', 'wp-ever-accounting' ),
			'received' => __( 'Received', 'wp-ever-accounting' ),
			'delete'   => __( 'Delete', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Define primary column.
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function get_primary_column_name() {
		return 'bill_number';
	}

	/**
	 * Renders the checkbox column in the accounts list table.
	 *
	 * @param Bill $bill The current account object.
	 *
	 * @return string Displays a checkbox.
	 * @since  1.1.0
	 *
	 */
	function column_cb( $bill ) {
		return sprintf( '<input type="checkbox" name="bill_id[]" value="%d"/>', $bill->get_id() );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param string $column_name The name of the column
	 *
	 * @param Bill $bill
	 *
	 * @return string The column value.
	 * @since 1.1.0
	 *
	 */
	function column_default( $bill, $column_name ) {
		$bill_id = $bill->get_id();
		switch ( $column_name ) {
			case 'bill_number':
				$bill_number = $bill->get_bill_number();
				$nonce       = wp_create_nonce( 'bill-nonce' );
				$view_url    = eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'bills', 'action' => 'view', 'bill_id' => $bill_id ) );// phpcs:ignore
				$edit_url    = eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'bills', 'action' => 'edit', 'bill_id' => $bill_id ) );// phpcs:ignore
				$del_url     = eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'bills', 'action' => 'delete', 'bill_id' => $bill_id, '_wpnonce' => $nonce ) );// phpcs:ignore

				$actions          = array();
				$actions['view']  = '<a href="' . $view_url . '">' . __( 'View', 'wp-ever-accounting' ) . '</a>';
				$actions['print'] = '<a href="' . $bill->get_url() . '" target="_blank">' . __( 'Print', 'wp-ever-accounting' ) . '</a>';
				if ( $bill->is_editable() ) {
					$actions['edit'] = '<a href="' . $edit_url . '">' . __( 'Edit', 'wp-ever-accounting' ) . '</a>';
				}
				$actions['delete'] = '<a href="' . $del_url . '" class="del">' . __( 'Delete', 'wp-ever-accounting' ) . '</a>';

				$value = '<a href="' . esc_url( $view_url ) . '">' . $bill_number . '</a>' . $this->row_actions( $actions );
				break;
			case 'total':
				$value = eaccounting_price( $bill->get_total(), $bill->get_currency_code() );
				break;
			case 'name':
				$value = esc_html( $bill->get_name() );
				if ( ! empty( $bill->get_contact_id() ) ) {
					$value = sprintf( '<a href="%1$s">%2$s</a>', esc_url( eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'vendors', 'action' => 'view', 'vendor_id' => $bill->get_contact_id() ) ) ), $bill->get_name() );// phpcs:ignore
				}
				break;
			case 'issue_date':
				$value = eaccounting_date( $bill->get_issue_date(), 'Y-m-d' );
				break;
			case 'due_date':
				$value = eaccounting_date( $bill->get_due_date(), 'Y-m-d' );
				break;
			case 'status':
				$value = sprintf( '<div class="ea-document__status %s"><span>%s</span></div>', $bill->get_status(), $bill->get_status_nicename() );
				break;
			default:
				return parent::column_default( $bill, $column_name );
		}

		return apply_filters( 'eaccounting_bill_list_table_' . $column_name, $value, $bill );
	}

	/**
	 * Renders the message to be displayed when there are no items.
	 *
	 * @return void
	 * @since  1.1.0
	 */
	function no_items() {
		_e( 'There is no bills found.', 'wp-ever-accounting' );
	}

	/**
	 * Process the bulk actions
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function process_bulk_action() {
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-bills' ) && ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bill-nonce' ) ) {
			return;
		}

		$ids = isset( $_GET['bill_id'] ) ? $_GET['bill_id'] : false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = array_map( 'absint', $ids );
		$ids = array_filter(  $ids );

		if ( empty( $ids ) ) {
			return;
		}

		$action = $this->current_action();

		foreach ( $ids as $id ) {
			$bill = new Bill( $id );
			switch ( $action ) {
				case 'cancel':
					$bill->delete_payments();
					$bill->set_status( 'cancelled' );
					$bill->save();
					break;
				case 'paid':
					$bill->set_paid();
					$bill->save();
					break;
				case 'received':
					$bill->set_status( 'received' );
					$bill->save();
					break;
				case 'delete':
					eaccounting_delete_bill( $id );
					break;
				default:
					do_action( 'eaccounting_bills_do_bulk_action_' . $this->current_action(), $id );
			}
		}

		if ( isset( $_GET['_wpnonce'] ) ) {
			wp_safe_redirect(
				remove_query_arg(
					array(
						'bill_id',
						'action',
						'_wpnonce',
						'_wp_http_referer',
						'action2',
						'paged',
					)
				)
			);
			exit();
		}
	}

	/**
	 * Retrieve all the data for the table.
	 * Setup the final data for the table
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$page    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$status  = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$search  = isset( $_GET['s'] ) ? $_GET['s'] : '';
		$order   = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'id';

		$per_page = $this->per_page;

		$args = wp_parse_args(
			$this->query_args,
			array(
				'number'   => $per_page,
				'offset'   => $per_page * ( $page - 1 ),
				'per_page' => $per_page,
				'page'     => $page,
				'status'   => $status,
				'search'   => $search,
				'orderby'  => eaccounting_clean( $orderby ),
				'order'    => eaccounting_clean( $order ),
			)
		);

		$args              = apply_filters( 'eaccounting_bill_table_query_args', $args, $this );
		$this->items       = eaccounting_get_bills( $args );
		$this->total_count = eaccounting_get_bills( array_merge( $args, array( 'count_total' => true ) ) );
		$this->set_pagination_args(
			array(
				'total_items' => $this->total_count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->total_count / $per_page ),
			)
		);
	}
}
