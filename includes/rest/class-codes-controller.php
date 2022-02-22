<?php
/**
 * Currency codes Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

defined( 'ABSPATH' ) || die();

class CodesController extends Data_Controller {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'data/currencies';

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 1.1.0
	 *
	 * @see   register_rest_route()
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
			)
		);

		$get_item_args = array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<currency>[a-z]+)',
			array(
				'args'   => array(
					'currency' => array(
						'description' => __( 'Unique identifier for the entity.', 'wp-ever-accounting' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $get_item_args,
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Return the list of all currencies.
	 *
	 * @since  1.1.0
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$currencies = eaccounting_get_data( 'currencies' );
		$data       = array();

		foreach ( $currencies as $code => $currency ) {
			$response = $this->prepare_item_for_response( (object) $currency, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Return information for a specific currency.
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
		$currencies = eaccounting_get_data( 'currencies' );
		$code       = strtoupper( $request['currency'] );
		if ( ! array_key_exists( $code, $currencies ) ) {
			return new \WP_Error( 'eaccounting_rest_data_invalid_currency', __( 'There are no currencies matching these parameters.', 'wp-ever-accounting' ), array( 'status' => 404 ) );
		}
		$data = $currencies[ $code ];

		return $this->prepare_item_for_response( $data, $request );
	}


	/**
	 * Prepare links for the request.
	 *
	 * @param object $item Data object.
	 *
	 * @return array Links for the given currency.
	 */
	protected function prepare_links( $item ) {
		$code  = strtoupper( $item->code );
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%s', $this->namespace, $this->rest_base, $code ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		return $links;
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
		$response->add_links( $this->prepare_links( $item ) );

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
			'title'      => 'data_currencies',
			'type'       => 'object',
			'properties' => array(
				'code'               => array(
					'type'        => 'string',
					'description' => __( 'ISO4217 currency code.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'               => array(
					'type'        => 'string',
					'description' => __( 'Full name of currency.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'symbol'             => array(
					'type'        => 'string',
					'description' => __( 'Currency symbol.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'precision'          => array(
					'type'        => 'integer',
					'description' => __( 'Precision count.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'subunit'            => array(
					'type'        => 'integer',
					'description' => __( 'Subunit count.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'position'           => array(
					'type'        => 'string',
					'description' => __( 'Currency position.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'decimal_separator'  => array(
					'type'        => 'string',
					'description' => __( 'Decimal separator.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'thousand_separator' => array(
					'type'        => 'string',
					'description' => __( 'Thousand separator.', 'wp-ever-accounting' ),
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

}
