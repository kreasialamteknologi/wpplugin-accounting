<?php
/**
 * Admin Report.
 *
 * Extended by reports to show charts and stats in admin.
 *
 * @author      EverAccounting
 * @category    Admin
 * @package     EverAccounting\Admin
 * @version     1.1.0
 */

namespace EverAccounting\Admin\Report;

use DatePeriod;

defined( 'ABSPATH' ) || exit();

/**
 * Report Class
 * @package EverAccounting\Admin\Report
*/

class Report {
	/**
	 * @param array $args
	 * @since 1.1.0
	 *
	 * @return array|mixed|void
	 */
	public function get_report( $args = array() ) {}

	/**
	 * Output report.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function output() {}

	/**
	 * Get start date.
	 *
	 * @since 1.1.0
	 *
	 * @param $year
	 *
	 * @return string
	 */
	public function get_start_date( $year = null ) {
		if ( null === $year ) {
			$year = date_i18n( 'Y' );
		}

		return eaccounting_get_financial_start( intval( $year ) );
	}

	/**
	 * Get end date.
	 *
	 * @since 1.1.0
	 *
	 * @param $year
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function get_end_date( $year = null ) {
		if ( null === $year ) {
			$year = date_i18n( 'Y' );
		}

		return eaccounting_get_financial_end( intval( $year ) );
	}


	/**
	 * Get months in the financial period.
	 *
	 * @since 1.1.0
	 *
	 * @param        $start_date
	 *
	 * @param        $end_date
	 * @param string $period
	 * @param string $date_key
	 * @param string $date_value
	 *
	 * @return array
	 */
	public function get_dates_in_period( $start_date, $end_date, $interval = 'M', $date_key = 'Y-m', $date_value = 'M y' ) {
		$dates  = array();
		$period = new DatePeriod(
			new \DateTime( $start_date ),
			new \DateInterval( "P1{$interval}" ),
			new \DateTime( $end_date )
		);
		foreach ( $period as $key => $value ) {
			$dates[ $value->format( $date_key ) ] = $value->format( $date_value );
		}

		return $dates;
	}

	/**
	 * Get range sql.
	 *
	 * @since 1.1.0
	 *
	 * @param null $start_date
	 * @param null $end_date
	 * @param      $column
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function get_range_sql( $column, $start_date = null, $end_date = null ) {
		global $wpdb;
		$start_date = empty( $start_date ) ? $this->get_start_date() : $start_date;
		$end_date   = empty( $end_date ) ? $this->get_end_date() : $end_date;
		$start      = strtotime( $start_date );
		$end        = strtotime( $end_date );
		$date       = 'CAST(`' . $column . '` AS DATE)';

		$period = 0;
		while ( ( $start = strtotime( '+1 MONTH', $start ) ) <= $end ) { //phpcs:ignore
			$period ++;
		}

		$sql = array();
		switch ( $period ) {
			case $period < 24:
				$sql = array(
					"DATE_FORMAT(`$column`, '%Y-%m')",
					$wpdb->prepare( "$date BETWEEN %s AND %s", $start_date, $end_date ),
				);
				break;
		}

		return $sql;
	}


	/**
	 * Clear cache and redirect .
	 *
	 * @param $key
	 * @since 1.1.0
	 */
	public function maybe_clear_cache( $key ) {
		if ( ! empty( $_GET['refresh_report'] )
		     && ! empty( $_GET['_wpnonce'] )
		     && wp_verify_nonce( $_GET['_wpnonce'], 'refresh_report' ) ) {
			$this->delete_cache( $key );
			wp_redirect( remove_query_arg( array( 'refresh_report', '_wpnonce' ) ) );
			exit();
		}
	}

	/**
	 * Generate cache key.
	 *
	 * @since 1.1.0
	 *
	 * @param $key
	 *
	 * @return string
	 */
	public function generate_cache_key( $key ) {
		if ( ! is_string( $key ) ) {
			$key = serialize( $key ). get_called_class();
		}

		return 'eaccounting_cache_report_' . $key;
	}

	/**
	 * Add cache key.
	 *
	 * @since 1.1.0
	 *
	 * @param     $value
	 * @param int $expire
	 * @param     $key
	 *
	 * @return bool
	 */
	public function set_cache( $key, $value, $minute = 5 ) {
		if ( ! is_string( $key ) ) {
			$key = $this->generate_cache_key( $key );
		}

		return set_transient( $key, $value, MINUTE_IN_SECONDS * $minute );
	}

	/**
	 * Get cache.
	 *
	 * @since 1.1.0
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get_cache( $key ) {
		if ( ! is_string( $key ) ) {
			$key = $this->generate_cache_key( $key );
		}

		return get_transient( $key );
	}

	/**
	 * Delete report cache.
	 *
	 * @since 1.1.0
	 *
	 * @param $key
	 *
	 * @return bool|void
	 */
	public function delete_cache( $key ) {
		if ( ! is_string( $key ) ) {
			$key = $this->generate_cache_key( $key );
		}

		return delete_transient( $key );
	}
}
