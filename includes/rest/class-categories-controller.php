<?php
/**
 * Categories Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

use EverAccounting\Abstracts\Entities_Controller;
use EverAccounting\Models\Category;

defined( 'ABSPATH' ) || die();

class CategoriesController extends Entities_Controller {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'categories';

	/**
	 * Entity model class.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 */
	protected $entity_model = Category::class;

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
		return eaccounting_get_categories( $query_args );
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
			'title'      => __( 'Category', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the category.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'name'         => array(
					'description' => __( 'Name of the category.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'    => true,
				),
				'type'         => array(
					'description' => __( 'Type of the category.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'required'    => true,
					'enum'        => array_keys( eaccounting_get_category_types() ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'color'        => array(
					'description' => __( 'Color of the category.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_hex_color',
					),
				),
				'enabled'      => array(
					'description' => __( 'Status of the item.', 'wp-ever-accounting' ),
					'type'        => 'boolean',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'date_created' => array(
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
				'type',
				'color',
				'enabled',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}
}
