<?php
/**
 * Currencies Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

use EverAccounting\Abstracts\Entities_Controller;
use EverAccounting\Models\Currency;

defined( 'ABSPATH' ) || die();

class Currencies_Controller extends Entities_Controller {
	/**
	 * Route base.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 *
	 */
	protected $rest_base = 'currencies';

	/**
	 * Entity model class.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 */
	protected $entity_model = Currency::class;

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
		return eaccounting_get_currencies( $query_args );
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
			'title'      => __( 'Currency', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'                 => array(
					'description' => __( 'Unique identifier for the currency.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'name'               => array(
					'description' => __( 'Name of the currency.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'code'               => array(
					'description' => __( 'Unique code for the currency.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'rate'               => array(
					'description' => __( 'Current rate for the currency.', 'wp-ever-accounting' ),
					'type'        => array( 'string', 'numeric' ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'precision'          => array(
					'description' => __( 'Precision count.', 'wp-ever-accounting' ),
					'type'        => array( 'string', 'numeric' ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'symbol'             => array(
					'description' => __( 'Currency Sumbol.', 'wp-ever-accounting' ),
					'type'        => array( 'string' ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'position'           => array(
					'description' => __( 'Position.', 'wp-ever-accounting' ),
					'type'        => array( 'string' ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'enum'        => array( 'before', 'after' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'decimal_separator'  => array(
					'description' => __( 'Decimal separator count.', 'wp-ever-accounting' ),
					'type'        => array( 'string' ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'thousand_separator' => array(
					'description' => __( 'Thousand separator count.', 'wp-ever-accounting' ),
					'type'        => array( 'string' ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'enabled'            => array(
					'description' => __( 'Status of the item.', 'wp-ever-accounting' ),
					'type'        => 'boolean',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'date_created'       => array(
					'description' => __( 'Created date of the currency.', 'wp-ever-accounting' ),
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
				'code',
				'rate',
				'enabled',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}
}
