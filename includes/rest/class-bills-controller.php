<?php
/**
 * Accounts Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;
use EverAccounting\Abstracts\Controller;

defined( 'ABSPATH' ) || die();

class Bills_Controller extends Controller {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'bills';

	/**
	 * Register our routes.
	 *
	 * @since 1.1.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		$get_item_args = array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the bill.', 'wp-ever-accounting' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $get_item_args,
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check whether a given request has permission to read accounts.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		return true; // current_user_can( 'manage_account' );
	}

	/**
	 * Check if a given request has access create account.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|\WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		return true; // current_user_can( 'manage_account' );
	}

	/**
	 * Check if a given request has access to read a account.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		return true; // current_user_can( 'manage_account' );
	}

	/**
	 * Check if a given request has access update a account.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|\WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		return true; // current_user_can( 'manage_account' );
	}

	/**
	 * Check if a given request has access delete a account.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		return true; // current_user_can( 'manage_account' );
	}

	/**
	 * Check if a given request has access batch create, update and delete items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|\WP_Error
	 */
	public function batch_items_permissions_check( $request ) {
		return true; // current_user_can( 'manage_account' );
	}


	/**
	 * Get all customers.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$args = array(
			'enabled'  => wp_validate_boolean( $request['enabled'] ),
			'include'  => $request['include'],
			'exclude'  => $request['exclude'],
			'search'   => $request['search'],
			'orderby'  => $request['orderby'],
			'order'    => $request['order'],
			'per_page' => $request['per_page'],
			'page'     => $request['page'],
			'offset'   => $request['offset'],
		);

		$results  = \EverAccounting\Accounts\query( $args )->get_results( OBJECT, '\EverAccounting\Accounts\get' );
		$total    = \EverAccounting\Accounts\query( $args )->count();
		$response = array();
		foreach ( $results as $item ) {
			$data       = $this->prepare_item_for_response( $item, $request );
			$response[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $response );

		$per_page = (int) $args['per_page'];

		$response->header( 'X-WP-Total', (int) $total );

		$max_pages = ceil( $total / $per_page );

		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return rest_ensure_response( $response );
	}


	/**
	 * @since 1.0.2
	 * 
	 * @param \WP_REST_Request $request
	 *
	 * @return int|mixed|\WP_Error|\WP_REST_Response|null
	 * 
	 */
	public function create_item( $request ) {
		$request->set_param( 'context', 'edit' );
		$prepared = $this->prepare_item_for_database( $request );

		$item_id = eaccounting_insert_account( (array) $prepared );
		if ( is_wp_error( $item_id ) ) {
			return $item_id;
		}

		$contact = eaccounting_get_account( $item_id );

		$request->set_param( 'context', 'view' );

		$response = $this->prepare_item_for_response( $contact, $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );

		return $response;
	}


	/**
	 * @since 1.0.2
	 * 
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|\WP_Error|\WP_REST_Response
	 * 
	 */
	public function get_item( $request ) {
		$item_id = intval( $request['id'] );
		$request->set_param( 'context', 'view' );
		$item = eaccounting_get_account( $item_id );
		if ( is_null( $item ) ) {
			return new \WP_Error( 'rest_invalid_item_id', __( 'Could not find the account', 'wp-ever-accounting' ) );
		}

		$response = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * @since 1.0.2
	 * 
	 * @param \WP_REST_Request $request
	 *
	 * @return int|mixed|\WP_Error|\WP_REST_Response|null
	 */
	public function update_item( $request ) {
		$request->set_param( 'context', 'edit' );
		$item_id = intval( $request['id'] );
		$item    = eaccounting_get_account( $item_id );
		if ( is_null( $item ) ) {
			return new \WP_Error( 'rest_invalid_item_id', __( 'Could not find the vendor', 'wp-ever-accounting' ) );
		}
		$prepared_args       = $this->prepare_item_for_database( $request );
		$prepared_args['id'] = $item_id;

		if ( ! empty( $prepared_args ) ) {
			$updated = eaccounting_insert_account( (array) $prepared_args );

			if ( is_wp_error( $updated ) ) {
				return $updated;
			}
		}

		$request->set_param( 'context', 'view' );
		$item     = \EverAccounting\Accounts\get( $item_id );
		$response = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * @since 1.0.0
	 * @since 1.0.2
	 * 
	 * @param \WP_REST_Request $request
	 *
	 * @return void|\WP_Error|\WP_REST_Response
	 * 
	 */
	public function delete_item( $request ) {
		$item_id = intval( $request['id'] );
		$item    = eaccounting_get_account( $item_id );
		if ( is_null( $item ) ) {
			return new \WP_Error( 'rest_invalid_item_id', __( 'Could not find the account', 'wp-ever-accounting' ) );
		}

		$request->set_param( 'context', 'view' );
		$previous = $this->prepare_item_for_response( $item, $request );
		$retval   = eaccounting_delete_account( $item_id );
		if ( ! $retval ) {
			return new \WP_Error( 'rest_cannot_delete', __( 'This account cannot be deleted.', 'wp-ever-accounting' ), array( 'status' => 500 ) );
		}

		$response = new \WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $previous->get_data(),
			)
		);

		return $response;
	}

	/**
	 * @since 1.0.2
	 * 
	 * @param \WP_REST_Request                 $request
	 * @param \EverAccounting\Accounts\Account $item
	 *
	 * @return mixed|\WP_Error|\WP_REST_Response
	 * 
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array(
			'id'              => $item->get_id(),
			'name'            => $item->get_name(),
			'number'          => $item->get_number(),
			'opening_balance' => $item->get_opening_balance(),
			'balance'         => $item->get_balance(),
			'currency_code'   => $item->get_currency_code(),
			'bank_name'       => $item->get_bank_name(),
			'bank_phone'      => $item->get_bank_phone(),
			'bank_address'    => $item->get_bank_address(),
			'enabled'         => $item->get_enabled(),
			'creator_id'      => $item->get_creator_id(),
			'created_at'      => eaccounting_rest_date_response( $item->get_date_created() ),
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return $response;
	}


	/**
	 * Retrieves the items's schema, conforming to JSON Schema.
	 * 
	 * @since 1.0.2
	 * @return array Item schema data.
	 * 
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Bill', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'                 => array(
					'description' => __( 'Unique identifier for the bill.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'bill_number'        => array(
					'description' => __( 'Number of bill.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'readonly'    => true,
					'required'    => true,
				),
				'order_number'       => array(
					'description' => __( 'Order Number of bill.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'readonly'    => true,
					'required'    => true,
				),
				'status'             => array(
					'description' => __( 'Status of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'bill_at'            => array(
					'description' => __( 'Date of bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'due_date'             => array(
					'description' => __( 'Due Date of bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'subtotal'           => array(
					'description' => __( 'Subtotal of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'default'     => '0',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'discount'           => array(
					'description' => __( 'Discount of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'default'     => '0',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'tax'                => array(
					'description' => __( 'Tax of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'default'     => '0',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'shipping'           => array(
					'description' => __( 'Shipping of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'default'     => '0',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'total'              => array(
					'description' => __( 'Total of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'default'     => '0',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'currency_code'      => array(
					'description' => __( 'Currency code of the bill.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Currency code ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'code' => array(
							'description' => __( 'Currency code.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),

					),
				),
				'currency_rate'      => array(
					'description' => __( 'Currency rate of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'category_id'        => array(
					'description' => __( 'Category id of the bill.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
					'required'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Category ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'type' => array(
							'description' => __( 'Category Type.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'contact_id'         => array(
					'description' => __( 'Contact id of the bill.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
					'required'    => true,
					'properties'  => array(
						'id' => array(
							'description' => __( 'Contact ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'contact_name'       => array(
					'description' => __( 'Contact name of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'contact_email'      => array(
					'description' => __( 'Contact email of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'contact_tax_number' => array(
					'description' => __( 'Contact tax_number of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'contact_phone'      => array(
					'description' => __( 'Contact phone of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'contact_address'    => array(
					'description' => __( 'Contact address of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
				'note'               => array(
					'description' => __( 'Note for the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
				'footer'             => array(
					'description' => __( 'Footer of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
				'attachment'         => array(
					'description' => __( 'Attachment url of the bill.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'embed', 'view' ),
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Attachment ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'src'  => array(
							'description' => __( 'Attachment src.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Attachment Name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'creator'            => array(
					'description' => __( 'Creator of the bill.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id'    => array(
							'description' => __( 'Creator ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'name'  => array(
							'description' => __( 'Creator name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'email' => array(
							'description' => __( 'Creator Email.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'date_created'       => array(
					'description' => __( 'Created date of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),

			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for the items collection.
	 *
	 * @since 1.1.0
	 * 
	 * @return array Collection parameters.
	 * 
	 */
	public function get_collection_params() {
		$query_params                       = parent::get_collection_params();
		$query_params['context']['default'] = 'view';

		$params['orderby'] = array(
			'description'       => __( 'Sort collection by object attribute.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'default'           => 'id',
			'enum'              => array(
				'name',
				'id',
				'number',
				'opening_balance',
				'bank_name',
				'enabled',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}
}
