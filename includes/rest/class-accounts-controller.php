<?php
/**
 * Accounts Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

use EverAccounting\Abstracts\Entities_Controller;
use EverAccounting\Models\Account;

defined( 'ABSPATH' ) || die();

class Accounts_Controller extends Entities_Controller {
	/**
	 * Route base.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 *
	 */
	protected $rest_base = 'accounts';

	/**
	 * Entity model class.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 */
	protected $entity_model = Account::class;

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
		return eaccounting_get_accounts( $query_args );
	}

	/**
	 * Retrieves the items's schema, conforming to JSON Schema.
	 *
	 * @since 1.1.0
	 *
	 * @return array Item schema data.
	 *
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Account', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'              => array(
					'description' => __( 'Unique identifier for the account.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'name'            => array(
					'description' => __( 'Name of the account.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'number'          => array(
					'description' => __( 'Number of the account.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'opening_balance' => array(
					'description' => __( 'Opening balance of the account', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view' ),
					'default'     => '0',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'currency'        => array(
					'description' => __( 'Currency code of the account', 'wp-ever-accounting' ),
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
				'bank_name'       => array(
					'description' => __( 'Bank name of the account', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'bank_phone'      => array(
					'description' => __( 'Phone number of the bank', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'bank_address'    => array(
					'description' => __( 'Address of the bank', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
				'enabled'         => array(
					'description' => __( 'Status of the item.', 'wp-ever-accounting' ),
					'type'        => 'boolean',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'creator'         => array(
					'description' => __( 'Creator of the account', 'wp-ever-accounting' ),
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
				'date_created'    => array(
					'description' => __( 'Created date of the account.', 'wp-ever-accounting' ),
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
