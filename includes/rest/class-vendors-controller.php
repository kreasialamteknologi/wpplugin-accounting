<?php
/**
 * Contacts Rest Controller Class.
 *
 * @since       1.1.0
 * @subpackage  Rest
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

use EverAccounting\Abstracts\Contacts_Controller;
use EverAccounting\Models\Vendor;

defined( 'ABSPATH' ) || die();

/**
 * Class Vendors_Controller
 *
 * @since   1.1.0
 *
 * @package EverAccounting\Rest
 */
class Vendors_Controller extends Contacts_Controller {
	/**
	 * Route base.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 *
	 */
	protected $rest_base = 'vendors';

	/**
	 * Entity model class.
	 *
	 * @since 1.1.0
	 * 
	 * @var string
	 */
	protected $entity_model = Vendor::class;

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
		return eaccounting_get_vendors( $query_args );
	}
}
