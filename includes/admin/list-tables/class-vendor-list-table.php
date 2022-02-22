<?php
/**
 * Vendor Admin List Table.
 *
 * @since       1.0.2
 * @subpackage  EverAccounting\Admin\ListTables
 * @package     EverAccounting
 */

use EverAccounting\Models\Vendor;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( '\EverAccounting_List_Table' ) ) {
	require_once dirname( __FILE__ ) . '/class-list-table.php';
}

/**
 * Class EverAccounting_Vendor_List_Table
 * @since 1.1.0
 */
class EverAccounting_Vendor_List_Table extends EverAccounting_List_Table {
	/**
	 * Default number of items to show per page
	 *
	 * @since 1.0.2
	 * @var string
	 */
	public $per_page = 20;

	/**
	 * Total number of item found
	 *
	 * @since 1.0.2
	 * @var int
	 */
	public $total_count;

	/**
	 * Number of active items found
	 *
	 * @since 1.0
	 * @var string
	 */
	public $active_count;

	/**
	 *  Number of inactive items found
	 *
	 * @since 1.0
	 * @var string
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @param array $args Optional. Arbitrary display and query arguments to pass through the list table. Default empty array.
	 *
	 * @since  1.0.2
	 *
	 * @see WP_List_Table::__construct()
	 */
	public function __construct( $args = array() ) {
		$args = (array) wp_parse_args(
			$args,
			array(
				'singular' => 'vendor',
				'plural'   => 'vendors',
			)
		);

		parent::__construct( $args );
	}

	/**
	 * Check if there is contents in the database.
	 *
	 * @return bool
	 * @since 1.0.2
	 */
	public function is_empty() {
		global $wpdb;

		return ! (int) $wpdb->get_var( "SELECT COUNT(id) from {$wpdb->prefix}ea_contacts WHERE type='vendor'" );
	}

	/**
	 * Render blank state.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	protected function render_blank_state() {
		?>
		<div class="ea-empty-table">
			<p class="ea-empty-table__message">
				<?php echo esc_html__( 'Create vendors to assign payments, and later you can filter the transactions you made with them. You can store the name, address, email, phone number, etc. of a vendor.', 'wp-ever-accounting' ); ?>
			</p>
			<a href="<?php echo esc_url( eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'vendors', 'action' => 'edit', ) ) ); //phpcs:ignore?>" class="button-primary ea-empty-table__cta"><?php _e( 'Add Vendors', 'wp-ever-accounting' ); ?></a>
			<a href="https://wpeveraccounting.com/docs/general/add-vendors/?utm_source=listtable&utm_medium=link&utm_campaign=admin" class="button-secondary ea-empty-table__cta" target="_blank"><?php _e( 'Learn More', 'wp-ever-accounting' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @return array
	 * @since 1.0.2
	 */
	public function define_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'thumb'   => '<span class="ea-thumb">&nbsp;</span>',
			'name'    => __( 'Name', 'wp-ever-accounting' ),
			'email'   => __( 'Contact', 'wp-ever-accounting' ),
			'street'  => __( 'Address', 'wp-ever-accounting' ),
			'paid'    => __( 'Paid', 'wp-ever-accounting' ),
			'due'     => __( 'Payable', 'wp-ever-accounting' ),
			'enabled' => __( 'Enabled', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array
	 * @since 1.0.2
	 */
	protected function define_sortable_columns() {
		return array(
			'name'    => array( 'name', false ),
			'email'   => array( 'email', false ),
			'street'  => array( 'street', false ),
			'enabled' => array( 'enabled', false ),
		);
	}

	/**
	 * Define bulk actions
	 *
	 * @return array
	 * @since 1.0.2
	 */
	public function define_bulk_actions() {
		return array(
			'enable'  => __( 'Enable', 'wp-ever-accounting' ),
			'disable' => __( 'Disable', 'wp-ever-accounting' ),
			'delete'  => __( 'Delete', 'wp-ever-accounting' ),
		);
	}


	/**
	 * Define primary column.
	 *
	 * @return string
	 * @since 1.0.2
	 */
	public function get_primary_column_name() {
		return 'name';
	}


	/**
	 * Renders the checkbox column in the currencies list table.
	 *
	 * @param Vendor $vendor The current object.
	 *
	 * @return string Displays a checkbox.
	 * @since  1.0.2
	 */
	function column_cb( $vendor ) {
		return sprintf( '<input type="checkbox" name="vendor_id[]" value="%d"/>', $vendor->get_id() );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param string $column_name The name of the column
	 *
	 * @param Vendor $vendor
	 *
	 * @return string The column value.
	 * @since 1.0.2
	 *
	 */
	function column_default( $vendor, $column_name ) {
		$vendor_id = $vendor->get_id();

		switch ( $column_name ) {
			case 'thumb':
				$view_url = eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'vendors', 'action' => 'view', 'vendor_id' => $vendor_id, ) );// phpcs:ignore
				$value    = '<a href="' . esc_url( $view_url ) . '"><img src="' . $vendor->get_avatar_url() . '" height="36" width="36" alt="' . $vendor->get_name() . '"></a>';
				break;
			case 'name':
				$view_url = eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'vendors', 'action' => 'view', 'vendor_id' => $vendor_id, ) );// phpcs:ignore
				$edit_url = eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'vendors', 'action' => 'edit', 'vendor_id' => $vendor_id, ) );// phpcs:ignore
				$del_url  = eaccounting_admin_url( array( 'page' => 'ea-expenses', 'tab' => 'vendors', 'action' => 'delete', 'vendor_id' => $vendor_id, '_wpnonce' => wp_create_nonce( 'vendor-nonce' ), ) );// phpcs:ignore
				$actions  = array(
					'view'   => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $view_url ), __( 'View', 'wp-ever-accounting' ) ),
					'edit'   => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $edit_url ), __( 'Edit', 'wp-ever-accounting' ) ),
					'delete' => sprintf( '<a href="%1$s" class="del">%2$s</a>', esc_url( $del_url ), __( 'Delete', 'wp-ever-accounting' ) ),
				);
				$value    = '<a href="' . esc_url( $view_url ) . '"><strong>' . $vendor->get_name() . '</strong></a>';
				$value    .= '<br>';
				$value    .= '<small class=meta>' . $vendor->get_company() . '</small>';
				$value    .= $this->row_actions( $actions );
				break;
			case 'email':
				if ( ! empty( $vendor->get_email() ) || ! empty( $vendor->get_phone() ) ) {
					$value = ! empty( $vendor->get_email() ) ? '<a href="mailto:' . sanitize_email( $vendor->get_email() ) . '">' . sanitize_email( $vendor->get_email() ) . '</a><br>' : '';
					$value .= ! empty( $vendor->get_phone() ) ? '<span class="contact_phone">' . $vendor->get_phone() . '</span>' : '';
				}
				if ( empty( $vendor->get_email() ) && empty( $vendor->get_phone() ) ) {
					$value = '&mdash;';
				}
				break;
			case 'street':
				$value = eaccounting_format_address(
					array(
						'city'    => $vendor->get_city(),
						'state'   => $vendor->get_state(),
						'country' => $vendor->get_country_nicename(),
					),
					','
				);
				$value = ( $value != '' ) ? $value : '&mdash;';
				break;
			case 'enabled':
				$value = '<label class="ea-toggle">';
				$value .= '<input type="checkbox" class="vendor-status" style="" value="true" data-id="' . $vendor->get_id() . '" ' . checked( $vendor->is_enabled(), true, false ) . '>';
				$value .= '<span data-label-off="' . __( 'No', 'wp-ever-accounting' ) . '" data-label-on="' . __( 'Yes', 'wp-ever-accounting' ) . '" class="ea-toggle-slider"></span>';
				$value .= '</label>';
				break;
			case 'actions':
				$edit_url = eaccounting_admin_url(
					array(
						'tab'       => 'vendors',
						'action'    => 'edit',
						'vendor_id' => $vendor_id,
					)
				);
				$del_url  = eaccounting_admin_url(
					array(
						'tab'       => 'vendors',
						'action'    => 'delete',
						'vendor_id' => $vendor_id,
						'_wpnonce'  => wp_create_nonce( 'vendor-nonce' ),
					)
				);
				$actions  = array(
					'edit'   => sprintf( '<a href="%s" class="dashicons dashicons-edit"></a>', esc_url( $edit_url ) ),
					'delete' => sprintf( '<a href="%s" class="dashicons dashicons-trash del"></a>', esc_url( $del_url ) ),
				);
				$value    = $this->row_actions( $actions );
				break;
			case 'due':
				$value = eaccounting_format_price( $vendor->get_total_due() );
				break;
			case 'paid':
				$value = eaccounting_format_price( $vendor->get_total_paid() );
				break;
			default:
				return parent::column_default( $vendor, $column_name );
		}

		return apply_filters( 'eaccounting_vendor_list_table_' . $column_name, $value, $vendor );
	}

	/**
	 * Renders the message to be displayed when there are no items.
	 *
	 * @return void
	 * @since  1.0.2
	 */
	function no_items() {
		_e( 'There is no vendors found.', 'wp-ever-accounting' );
	}

	/**
	 * Process the bulk actions
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public function process_bulk_action() {
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-vendors' ) && ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'vendor-nonce' ) ) {
			return;
		}

		$ids = isset( $_GET['vendor_id'] ) ? $_GET['vendor_id'] : false;

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
			switch ( $action ) {
				case 'enable':
					eaccounting_insert_vendor(
						array(
							'id'      => $id,
							'enabled' => '1',
						)
					);
					break;
				case 'disable':
					eaccounting_insert_vendor(
						array(
							'id'      => $id,
							'enabled' => '0',
						)
					);
					break;
				case 'delete':
					eaccounting_delete_vendor( $id );
					break;
				default:
					do_action( 'eaccounting_vendors_do_bulk_action_' . $this->current_action(), $id );
			}
		}

		if ( isset( $_GET['_wpnonce'] ) ) {
			wp_safe_redirect(
				remove_query_arg(
					array(
						'vendor_id',
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
	 * Retrieve the view types
	 *
	 * @access public
	 * @return array $views All the views available
	 * @since 1.1.0
	 */
	public function get_views() {
		$base           = eaccounting_admin_url( array( 'tab' => 'vendors' ) );
		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';

		$views = array(
			'all'      => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'status', $base ) ), $current === 'all' || $current === '' ? ' class="current"' : '', __( 'All', 'wp-ever-accounting' ) . $total_count ),
			'active'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'active', $base ) ), $current === 'active' ? ' class="current"' : '', __( 'Active', 'wp-ever-accounting' ) . $active_count ),
			'inactive' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'inactive', $base ) ), $current === 'inactive' ? ' class="current"' : '', __( 'Inactive', 'wp-ever-accounting' ) . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Retrieve all the data for the table.
	 * Setup the final data for the table
	 *
	 * @return void
	 * @since 1.0.2
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
				'search'   => $search,
				'status'   => $status,
				'orderby'  => eaccounting_clean( $orderby ),
				'order'    => eaccounting_clean( $order ),
				'type'     => 'customer',
			)
		);

		$args = apply_filters( 'eaccounting_vendor_table_query_args', $args, $this );

		$this->items = eaccounting_get_vendors( $args );

		$this->active_count = eaccounting_get_vendors(
			array_merge(
				$args,
				array(
					'status'      => 'active',
					'count_total' => true,
				)
			)
		);

		$this->inactive_count = eaccounting_get_vendors(
			array_merge(
				$args,
				array(
					'status'      => 'inactive',
					'count_total' => true,
				)
			)
		);

		$this->total_count = $this->active_count + $this->inactive_count;

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch ( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}
}
