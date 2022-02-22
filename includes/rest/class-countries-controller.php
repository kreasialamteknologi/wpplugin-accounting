<?php
/**
 * Countries Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

defined( 'ABSPATH' ) || die();

class Countries_Controller extends Data_Controller {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'data/countries';

	/**
	 * Register routes.
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
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Return the list of all countries.
	 *
	 * @since  1.1.0
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$countries = eaccounting_get_data( 'countries' );
		$data      = array();

		foreach ( $countries as $country_code => $country_name ) {
			$country  = array(
				'code' => $country_code,
				'name' => $country_name,
			);
			$response = $this->prepare_item_for_response( (object) $country, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Prepare the data object for response.
	 *
	 * @since  1.1.0
	 *
	 * @param object           $item    Data object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data     = $this->add_additional_fields_to_object( (array) $item, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Get the location schema, conforming to JSON Schema.
	 *
	 * @since  1.1.0
	 * 
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'data_countries',
			'type'       => 'object',
			'properties' => array(
				'code' => array(
					'type'        => 'string',
					'description' => __( 'ISO3166 alpha-2 country code.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name' => array(
					'type'        => 'string',
					'description' => __( 'Full name of country.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
