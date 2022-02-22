<?php
/**
 * Abstract Rest Entities Controller
 * This controller will automatically handles
 * entity listing, insert, update, get, bulk actions.
 *
 * @since       1.1.0
 * @subpackage  Abstracts
 * @package     EverAccounting
 */

namespace EverAccounting\Abstracts;

use EverAccounting\Abstracts\Resource_Model;

use EverAccounting\Repositories\Meta_Data;

defined( 'ABSPATH' ) || die();

/**
 * Class EntitiesController
 *
 * @since   1.1.0
 *
 * @package EverAccounting\Abstracts
 */
abstract class Entities_Controller extends Controller {
	/**
	 * entity type.
	 *
	 * @var string
	 */
	protected $entity_type = '';

	/**
	 * Contains the current entity object.
	 *
	 * @since 1.1.0
	 *
	 * @var Resource_Model
	 */
	protected $entity_model;


	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 1.1.0
	 *
	 * @see register_rest_route()
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
						'description' => __( 'Unique identifier for the entity.', 'wp-ever-accounting' ),
						'type'        => 'integer',
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
	 * Check if a given request has access to read items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( "ea_manage_{$this->entity_type}" ) ) {
			return new \WP_Error( 'eaccounting_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'wp-ever-accounting' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( "ea_manage_{$this->entity_type}" ) ) {
			return new \WP_Error( 'eaccounting_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'wp-ever-accounting' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! current_user_can( "ea_manage_{$this->entity_type}" ) ) {
			return new \WP_Error( 'eaccounting_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'wp-ever-accounting' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! current_user_can( "ea_manage_{$this->entity_type}" ) ) {
			return new \WP_Error( 'eaccounting_rest_cannot_edit', __( 'Sorry, you are not allowed to edit this resource.', 'wp-ever-accounting' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! current_user_can( "ea_manage_{$this->entity_type}" ) ) {
			return new \WP_Error( 'eaccounting_rest_cannot_delete', __( 'Sorry, you are not allowed to delete this resource.', 'wp-ever-accounting' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access batch create, update and delete items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return boolean|\WP_Error
	 */
	public function batch_items_permissions_check( $request ) {
		if ( ! current_user_can( "ea_manage_{$this->entity_type}" ) ) {
			return new \WP_Error( 'eaccounting_rest_cannot_batch', __( 'Sorry, you are not allowed to batch manipulate this resource.', 'wp-ever-accounting' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get object.
	 *
	 * @param int $id Object ID.
	 *
	 * @return Resource_Model|\WP_Error Resource_Model object or WP_Error object.
	 */
	protected function get_object( $id ) {
		try {
			if ( empty( $this->entity_model ) || ! class_exists( $this->entity_model ) ) {
				throw new \Exception( __( 'You need to specify a entity model class for this controller', 'wp-ever-accounting' ), 400 );
			}
			$object = new $this->entity_model( $id );
			if ( ! $object->exists() ) {
				throw new \Exception(  __( 'Invalid ID.', 'wp-ever-accounting' ) );
			}

			return $object;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'get_object', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}

	/**
	 * Get a single object.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {

		// Fetch the item.
		$object = $this->get_object( $request['id'] );

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		// Generate a response.
		return rest_ensure_response( $this->prepare_item_for_response( $object, $request ) );
	}

	/**
	 * Create a single object.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_item( $request ) {
		try {
			if ( empty( $this->entity_model ) || ! class_exists( $this->entity_model ) ) {
				throw new \Exception(  __( 'You need to specify a entity model class for this controller', 'wp-ever-accounting' ), 400 );
			}
			if ( ! empty( $request['id'] ) ) {
				throw new \Exception( __( 'Cannot create existing resource.', 'wp-ever-accounting' ), 400 );
			}
			$object = new $this->entity_model();
			$object = $this->prepare_object_for_database( $object, $request );
			$object->save();
			$this->update_additional_fields_for_object( (array) $object, $request );

			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_item_for_response( $object, $request );
			$response = rest_ensure_response( $response );
			$response->set_status( 201 );
			$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->get_id() ) ) );

			return $response;

		} catch ( \Exception $e ) {
			return new \WP_Error( 'create_ite', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}

	}

	/**
	 * Update a single object.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {
		try {
			if ( empty( $this->entity_model ) || ! class_exists( $this->entity_model ) ) {
				throw new \Exception( 'no_entity_model_class', __( 'You need to specify a entity model class for this controller', 'wp-ever-accounting' ), 400 );
			}

			$id     = (int) $request['id'];
			$object = new $this->entity_model( $id );
			if ( ! $object->exists() ) {
				throw new \Exception( 'eaccounting_rest_invalid_id', __( 'Invalid resource ID.', 'wp-ever-accounting' ), 400 );
			}

			$object = $this->prepare_object_for_database( $object, $request );
			$object->save();
			$this->update_additional_fields_for_object( $object, $request );

			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_item_for_response( $object, $request );
			$response = rest_ensure_response( $response );

			return $response;

		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}

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
		// translators: %s: Class method name.
		return new \WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass.", 'wp-ever-accounting' ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Get a collection of posts.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$args             = array();
		$args['offset']   = $request['offset'];
		$args['order']    = $request['order'];
		$args['orderby']  = $request['orderby'];
		$args['paged']    = $request['page'];
		$args['include']  = $request['include'];
		$args['per_page'] = $request['per_page'];
		$args['search']   = $request['search'];

		$args['date_query'] = array();

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

		// Filter the query arguments for a request.
		$args    = apply_filters( "eaccounting_rest_{$this->entity_type}_query", $args, $request );
		$results = $this->get_objects( $args, $request );
		$total   = (int) $this->get_objects( array_merge( $args, array( 'count_total' => true ) ), $request );

		if ( is_wp_error( $results ) || is_wp_error( $total ) ) {
			return $results;
		}

		$items = array();
		foreach ( $results as $result ) {
			$data    = $this->prepare_item_for_response( $this->get_object( $result ), $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$max_pages = ceil( $total / (int) $args['per_page'] );
		$response  = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Delete a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		// Fetch the item.
		$item = $this->get_object( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $item, $request );

		if ( ! $item->delete() ) {
			/* translators: %s: post type */
			return new \WP_Error( 'rest_cannot_delete', sprintf( __( 'The %s cannot be deleted.', 'wp-ever-accounting' ), $this->entity_type ), array( 'status' => 500 ) );
		}

		$response = new \WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $this->prepare_response_for_collection( $data ),
			)
		);

		return $response;
	}

	/**
	 * Prepare a single object for create or update.
	 *
	 * @since 1.1.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return Resource_Model|\WP_Error Data object or WP_Error.
	 */
	protected function prepare_object_for_database( &$object, $request ) {
		$schema    = $this->get_item_schema();
		$data_keys = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );
		// Handle all writable props.
		foreach ( $data_keys as $key ) {
			$value = $request[ $key ];

			if ( ! is_null( $value ) ) {
				switch ( $key ) {
					case 'meta_data':
						if ( is_array( $value ) ) {
							foreach ( $value as $meta ) {
								$object->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
							}
						}
						break;

					default:
						if ( is_callable( array( $object, "set_{$key}" ) ) ) {
							$object->{"set_{$key}"}( $value );
						}
						break;
				}
			}

			if( is_object( $value ) && isset( $value['id'] ) && is_callable( array( $object, "set_{$key}_id" ) ) ){
				$object->{"set_{$key}_id"}( $value['id'] );
			}
		}

		return $object;
	}

	/**
	 * Retrieves data from a Model class.
	 *
	 * @since  1.1.0
	 *
	 * @param Resource_Model $object  model object.
	 * @param array         $fields  Fields to include.
	 * @param string        $context either view or edit.
	 *
	 * @return array
	 */
	protected function prepare_object_for_response( $object, $fields, $context = 'view' ) {

		$data = array();

		// Handle all writable props.
		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {

			// Abort if it is not included.
			if ( ! empty( $fields ) && ! $this->is_field_included( $key, $fields ) ) {
				continue;
			}

			// Or this current object does not support the field.
			if ( ! $this->object_supports_field( $object, $key ) ) {
				continue;
			}

			// Handle meta data.
			if ( 'meta_data' === $key ) {
				$data['meta_data'] = $this->prepare_object_meta_data( $object->get_meta_data() );
				continue;
			}

			// Booleans.
			if ( is_callable( array( $object, $key ) ) ) {
				$data[ $key ] = $object->$key( $context );
				continue;
			}

			// Get object value.
			if ( is_callable( array( $object, "get_{$key}" ) ) ) {
				$value = $object->{"get_{$key}"}( $context );
				// If the value is an instance of entity_model...
				if ( is_object( $value ) && is_callable( array( $value, 'get_data' ) ) ) {
					$value = $value->get_data();
				}

				// For objects, retrieves it's properties.
				$data[ $key ] = is_object( $value ) ? get_object_vars( $value ) : $value;
				continue;
			}
		}

		return $data;
	}

	/**
	 * Checks if a key should be included in a response.
	 *
	 * @since  1.1.0
	 *
	 * @param Resource_Model $object    Data object.
	 * @param string        $field_key The key to check for.
	 *
	 * @return bool
	 */
	public function object_supports_field( $object, $field_key ) {
		return apply_filters( 'eaccounting_rest_object_supports_key', true, $object, $field_key );
	}

	/**
	 * Retrieves data from a Model class.
	 *
	 * @since  1.1.0
	 *
	 * @param Meta_Data[] $meta_data meta data objects.
	 *
	 * @return array
	 */
	protected function prepare_object_meta_data( $meta_data ) {
		$meta = array();

		foreach ( $meta_data as $object ) {
			$meta[] = $object->get_data();
		}

		return $meta;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param Resource_Model    $object  Object data.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array                   Links for the given post.
	 */
	protected function prepare_links( $object, $request ) {
		return array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since  1.1.0
	 *
	 * @param Resource_Model    $object  Data object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function prepare_item_for_response( $object, $request ) {
		// Fetch the fields to include in this response.
		$fields = $this->get_fields_for_response( $request );
		// Prepare object data.
		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->prepare_object_for_response( $object, $fields, $context );
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->limit_object_to_requested_fields( $data, $fields );
		$data    = $this->filter_response_by_context( $data, $context );

		// Prepare the response.
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $object, $request ) );

		// Filter item response.
		return apply_filters( "eaccounting_rest_prepare_{$this->entity_type}_object", $response, $object, $request );
	}

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params                       = array();
		$params['context']            = $this->get_context_param();
		$params['context']['default'] = 'view';

		$params['page']     = array(
			'description'       => __( 'Current page of the collection.', 'wp-ever-accounting' ),
			'type'              => 'integer',
			'default'           => 1,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
			'minimum'           => 1,
		);
		$params['per_page'] = array(
			'description'       => __( 'Maximum number of items to be returned in result set.', 'wp-ever-accounting' ),
			'type'              => 'integer',
			'default'           => 10,
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['search']   = array(
			'description'       => __( 'Limit results to those matching a string.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['after']    = array(
			'description'       => __( 'Limit response to resources published after a given ISO8601 compliant date.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['before']   = array(
			'description'       => __( 'Limit response to resources published before a given ISO8601 compliant date.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['include']  = array(
			'description'       => __( 'Limit result set to specific ids.', 'wp-ever-accounting' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['offset']   = array(
			'description'       => __( 'Offset the result set by a specific number of items.', 'wp-ever-accounting' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']    = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby']  = array(
			'description'       => __( 'Sort collection by object attribute.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'default'           => 'date_created',
			'enum'              => array(
				'date_created',
				'id',
				'include',
				'name',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		/**
		 * Filter collection parameters for the entities controller.
		 *
		 * The dynamic part of the filter `$this->entity_type`.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter. Use the
		 * `rest_{$this->entity_type}_query` filter to set query parameters.
		 *
		 * @param array  $query_params JSON Schema-formatted collection parameters.
		 * @param string $entity_type  Post type object.
		 */
		return apply_filters( "rest_{$this->entity_type}_collection_params", $params, $this->entity_type );
	}
}
