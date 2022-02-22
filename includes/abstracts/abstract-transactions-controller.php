<?php
/**
 * Transaction Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Abstracts
 * @package     EverAccounting
 */

namespace EverAccounting\Abstracts;

use EverAccounting\Repositories\Transactions;

defined( 'ABSPATH' ) || die();

/**
 * Class TransactionController
 *
 * @since   1.1.0
 * @package EverAccounting\Abstracts
 *
 */
abstract class Transactions_Controller extends Entities_Controller {
	/**
	 * Route base.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 *
	 */
	protected $rest_base = 'transactions';

	/**
	 * Entity model class.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 */
	protected $entity_model = Transactions::class;

	/**
	 * Get objects.
	 *
	 * @since  1.1.0
	 *
	 * @param array            $query_args Query args.
	 * @param \WP_REST_Request $request    Full details about the request.
	 *
	 * @return array|int|\WP_Error
	 */
	protected function get_objects( $query_args, $request ) {
		$query_args['account_id']     = $request['account_id'];
		$query_args['category_id']    = $request['category_id'];
		$query_args['currency_code']  = $request['currency_code'];
		$query_args['vendor_id']      = $request['vendor_id'];
		$query_args['payment_method'] = $request['payment_method'];

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$args['payment_date'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$args['payment_date'][0]['after'] = $request['after'];
		}
		return eaccounting_get_transactions( $query_args );
	}

	/**
	 * Retrieves the items's schema, conforming to JSON Schema.
	 *
	 * @since   1.1.0
	 * 
	 * @return array Item schema data.
	 *
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Transaction', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description' => __( 'Unique identifier for the transaction.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'payment_date'          => array(
					'description' => __( 'Payment Date of the transaction', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date',
					'context'     => array( 'embed', 'view' ),
					'required'    => true,
				),
				'amount'           => array(
					'description' => __( 'Amount of the transaction', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'required'    => true,
				),
				'formatted_amount' => array(
					'description' => __( 'Amount of the transaction', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'readonly'    => true,
				),
				'currency'         => array(
					'description' => __( 'Currency code for transaction.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Currency Code ID.', 'wp-ever-accounting' ),
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
				'currency_rate'    => array(
					'description' => __( 'Currency rate of the transaction.', 'wp-ever-accounting' ),
					'type'        => 'double',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'doubleval',
					),
					'readonly'    => true,
				),
				'account'          => array(
					'description' => __( 'Account id of the transaction.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
					'required'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Account ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'name' => array(
							'description' => __( 'Account name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),

					),
				),
				'document_id'       => array(
					'description' => __( 'Invoice id of the transaction', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'category'         => array(
					'description' => __( 'Category of the transaction', 'wp-ever-accounting' ),
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
				'description'      => array(
					'description' => __( 'Description of the transaction', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
				'payment_method'   => array(
					'description' => __( 'Method of the payment', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_key',
					),
					'required'    => true,
				),
				'reference'        => array(
					'description' => __( 'Reference of the transaction', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'attachment'       => array(
					'description' => __( 'Attachment url of the transaction', 'wp-ever-accounting' ),
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
				'reconciled'       => array(
					'description' => __( 'Reconciliation of the transaction', 'wp-ever-accounting' ),
					'type'        => 'boolean',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'creator'          => array(
					'description' => __( 'Creator of the transactions', 'wp-ever-accounting' ),
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
				'date_created'     => array(
					'description' => __( 'Created date of the transaction.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		$contact = array(
			'description' => __( 'Contact id of the transaction', 'wp-ever-accounting' ),
			'type'        => 'object',
			'context'     => array( 'embed', 'view', 'edit' ),
			'arg_options' => array(
				'sanitize_callback' => 'sanitize_text_field',
			),
			'properties'  => array(
				'id'   => array(
					'description' => __( 'Contact ID.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name' => array(
					'description' => __( 'Contact name.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),

			),
		);

		// check rest base for the contacts and push proper contacts to the schema
		if ( 'expenses' === $this->rest_base ) {
			$schema['properties']['vendor'] = $contact;
		} elseif ( 'incomes' === $this->rest_base ) {
			$schema['properties']['customer'] = $contact;
		}

		return $this->add_additional_fields_schema( $schema );
	}


	/**
	 * Retrieves the query params for the items collection.
	 *
	 * @since   1.1.0
	 * 
	 * @return array Collection parameters.
	 *
	 */
	public function get_collection_params() {
		$query_params = array_merge(
			parent::get_collection_params(),
			array(
				'orderby'    => array(
					'description' => __( 'Sort collection by transaction attribute.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'default'     => 'payment_date',
					'enum'        => array(
						'payment_date',
						'amount',
						'account_id',
						'type',
						'category_id',
						'reference',
					),
				),
				'account_id' => array(
					'description'       => __( 'Limit results to those matching accounts.', 'wp-ever-accounting' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
			)
		);

		return $query_params;
	}
}
