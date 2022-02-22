<?php
/**
 * EverAccounting REST Functions
 *
 * Functions for REST specific things.
 *
 * @package EverAccounting\Functions
 * @version 1.1.0
 */
defined( 'ABSPATH' ) || die();

/**
 * Parses and formats a date for ISO8601/RFC3339.
 *
 * Required WP 4.4 or later.
 * See https://developer.wordpress.org/reference/functions/mysql_to_rfc3339/
 *
 * @since  1.1.0
 *
 * @param string|null|\EverAccounting\DateTime $date Date.
 * @param bool                                 $utc  Send false to get local/offset time.
 *
 * @return string|null ISO8601/RFC3339 formatted datetime.
 */
function eaccounting_rest_date_response( $date, $utc = true ) {
	if ( is_numeric( $date ) ) {
		$date = new \EverAccounting\DateTime( "@$date", new \DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( eaccounting_timezone_string() ) );
	} elseif ( is_string( $date ) ) {
		$date = new \EverAccounting\DateTime( $date, new \DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( eaccounting_timezone_string() ) );
	}

	if ( ! is_a( $date, '\EverAccounting\DateTime' ) ) {
		return null;
	}

	// Get timestamp before changing timezone to UTC.
	return gmdate( 'Y-m-d\TH:i:s', $utc ? $date->getTimestamp() : $date->getOffsetTimestamp() );
}


/**
 * Makes internal API request for usages within PHP
 *
 * @since 1.0.2
 *
 * @param        $endpoint
 *
 * @param array    $args
 * @param string   $method
 *
 * @param string   $namespace
 *
 * @return array
 */
function eaccounting_rest_request( $endpoint, $args = array(), $method = 'GET', $namespace = '/ea/v1/' ) {
	$endpoint = $namespace . untrailingslashit( ltrim( $endpoint, '/' ) );
	if ( ! empty( $args['id'] ) ) {
		$endpoint .= '/' . intval( $args['id'] );
		unset( $args['id'] );
	}

	$request = new \WP_REST_Request( $method, $endpoint );
	$request->set_query_params( $args );
	$response = rest_do_request( $request );
	$server   = rest_get_server();
	$result   = $server->response_to_data( $response, false );

	return $result;
}

/**
 * If currency exist then return code or create if all properties exist.
 *
 * @since 1.1.0
 *
 * @param $request
 *
 * @return int|null
 */
function eaccounting_rest_get_currency_code( $request ) {
	$currency = null;
	if ( ! empty( $request['id'] ) ) {
		$currency = eaccounting_get_currency( absint( $request['id'] ) );
	}

	return $currency && $currency->get_id() && $currency->exists() ? $currency->get_code() : null;
}


function eaccounting_rest_get_account_id( $request ) {
	$account = null;
	if ( ! empty( $request['id'] ) ) {
		$account = eaccounting_get_account( absint( $request['id'] ) );
	}

	return $account && $account->get_id() && $account->exists() ? $account->get_id() : null;
}

function eaccounting_rest_get_category_id( $request ) {
	$category = null;
	if ( ! empty( $request['id'] ) && ! empty( $request['type'] ) ) {
		$category = eaccounting_get_category( absint( $request['id'] ) );
	}
	$type = $request['type'];

	return $category && $category->get_id() && $category->exists() && $type === $category->get_type() ? $category->get_id() : null;
}

function eaccounting_rest_get_customer_id( $request ) {
	$customer = null;
	if ( ! empty( $request['id'] ) && ! empty( $request['type'] ) ) {
		$customer = eaccounting_get_customer( absint( $request['id'] ) );
	}

	return $customer && $customer->get_id() && $customer->exists() && 'customer' === $customer->get_type() ? $customer->get_id() : null;
}

function eaccounting_rest_get_vendor_id( $request ) {
	$vendor = null;
	if ( ! empty( $request['id'] ) && ! empty( $request['type'] ) ) {
		$vendor = eaccounting_get_vendor( absint( $request['id'] ) );
	}

	return $vendor && $vendor->get_id() && $vendor->exists() && 'vendor' === $vendor->get_type() ? $vendor->get_id() : null;
}

function eaccounting_rest_get_tax_id( $request ) {
}

function eaccounting_rest_get_item_id( $request ) {
}
