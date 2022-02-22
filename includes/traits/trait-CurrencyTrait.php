<?php
/**
 * Currency Trait
 */

namespace EverAccounting\Traits;

use EverAccounting\Models\Currency;

defined( 'ABSPATH' ) || exit;

/**
 * Trait CurrencyTrait
 * @package EverAccounting\Traits
 */
trait CurrencyTrait {
	/**
	 * Get currency object.
	 *
	 * @since 1.1.0
	 * @return Currency
	 */
	public function get_currency() {
		$currency = false;
		if ( array_key_exists( 'currency_code', $this->data ) ) {
			$code     = $this->get_currency_code() ? $this->get_currency_code() : null;
			$currency = eaccounting_get_currency( $code );
		}

		return $currency;
	}

	/**
	 * Get currency rate.
	 *
	 * @since 1.1.0
	 *
	 * @param string $context
	 *
	 * @return int|string
	 */
	public function get_currency_rate( $context = 'edit' ) {
		if ( $this->get_currency() ) {
			return $this->get_currency()->get_rate( $context );
		}

		return 1;
	}

	/**
	 * Get currency rate.
	 *
	 * @since 1.1.0
	 * @return int|string
	 */
	public function get_currency_precision( $context = 'edit' ) {
		if ( $this->get_currency() ) {
			return $this->get_currency()->get_precision( $context );
		}

		return 2;
	}

	/**
	 * @since 1.1.0
	 *
	 * @param string $context
	 *
	 * @return string
	 */
	public function get_currency_symbol( $context = 'edit' ) {
		if ( $this->get_currency() ) {
			return $this->get_currency()->get_symbol( $context );
		}

		return '$';
	}

	/**
	 * @since 1.1.0
	 * @return string
	 */
	public function get_currency_subunit( $context = 'edit' ) {
		if ( $this->get_currency() ) {
			return $this->get_currency()->get_subunit( $context );
		}

		return 2;
	}

	/**
	 * @since 1.1.0
	 *
	 * @param string $context
	 *
	 * @return string
	 */
	public function get_currency_position( $context = 'edit' ) {
		if ( $this->get_currency() ) {
			return $this->get_currency()->get_position( $context );
		}

		return 'before';
	}

	/**
	 * Get currency rate.
	 *
	 * @since 1.1.0
	 *
	 * @param string $context
	 *
	 * @return int|string
	 */
	public function get_currency_decimal_separator( $context = 'edit' ) {
		if ( $this->get_currency() ) {
			return $this->get_currency()->get_decimal_separator( $context );
		}

		return '.';
	}

	/**
	 * Get currency rate.
	 *
	 * @since 1.1.0
	 *
	 * @param string $context
	 *
	 * @return int|string
	 */
	public function get_currency_thousand_separator( $context = 'edit' ) {
		if ( $this->get_currency() ) {
			return $this->get_currency()->get_thousand_separator( $context );
		}

		return ',';
	}

	/**
	 * Format amount.
	 *
	 * @since 1.1.0
	 *
	 * @param $amount
	 *
	 * @return string
	 */
	public function format_amount( $amount ) {
		return eaccounting_price( $amount, $this->get_currency_code() );
	}

	/**
	 * Get converted amount.
	 *
	 * @since 1.1.0
	 *
	 * @param      $code
	 * @param null $rate
	 * @param      $amount
	 */
	public function get_converted_amount( $amount, $code, $rate = null ) {
		//todo complete
	}

}
