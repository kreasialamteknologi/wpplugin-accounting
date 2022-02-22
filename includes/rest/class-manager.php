<?php
/**
 * Handle main rest api Class.
 *
 * @since       1.1.0
 *
 * @package     EverAccounting
 */

namespace EverAccounting\Rest;

use EverAccounting\Abstracts\Singleton;

defined( 'ABSPATH' ) || die();

class Manager extends Singleton {
	/**
	 * Manager constructor.
	 */
	public function __construct() {
		if ( ! class_exists( '\WP_REST_Server' ) ) {
			return;
		}
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}

	public function register_rest_routes() {
		$rest_handlers = apply_filters(
			'eaccounting_rest_controllers',
			array(
				'\EverAccounting\Rest\Accounts_Controller',
				'\EverAccounting\Rest\Customers_Controller',
				'\EverAccounting\Rest\Vendors_Controller',
				'\EverAccounting\Rest\Expenses_Controller',
				'\EverAccounting\Rest\Incomes_Controller',
				'\EverAccounting\Rest\Categories_Controller',
				'\EverAccounting\Rest\Currencies_Controller',
				'\EverAccounting\Rest\Transfers_Controller',
				'\EverAccounting\Rest\Codes_Controller',
				'\EverAccounting\Rest\Countries_Controller',
				'\EverAccounting\Rest\Data_Controller',
				'\EverAccounting\Rest\Taxes_Controller',
			)
		);

		foreach ( $rest_handlers as $controller ) {
			if ( class_exists( $controller ) ) {
				$this->$controller = new $controller();
				$this->$controller->register_routes();
			}
		}
	}
}
