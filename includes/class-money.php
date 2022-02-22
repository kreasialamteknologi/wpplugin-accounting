<?php
/**
 * Handle the money object
 *
 * @package        EverAccounting
 * @version        1.0.2
 */

namespace EverAccounting;

use EverAccounting\Models\Currency;

defined( 'ABSPATH' ) || exit;

/**
 * Class Money
 *
 * @since   1.0.2
 * @package EverAccounting
 */
class Money {
	const ROUND_HALF_UP   = PHP_ROUND_HALF_UP;
	const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
	const ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;
	const ROUND_HALF_ODD  = PHP_ROUND_HALF_ODD;

	/**
	 * @since 1.0.2
	 *
	 * @var float|int
	 */
	protected $amount;

	/**
	 * @since 1.0.2
	 *
	 * @var \EverAccounting\Models\Currency
	 */
	protected $currency;

	/**
	 * Money constructor.
	 *
	 * @since 1.0.2
	 *
	 * @param string $amount Amount to convert
	 * @param string $code Currency object
	 * @param bool   $convert
	 *
	 * @throws \Exception
	 */
	public function __construct( $amount, $code, $convert = false ) {
		$this->currency = new \EverAccounting\Models\Currency( $code );
		$this->amount   = $this->parseAmount( $amount, $convert );
	}

	/**
	 * parseAmount.
	 *
	 * @since 1.0.2
	 *
	 * @param mixed $amount
	 * @param bool  $convert
	 *
	 * @throws \UnexpectedValueException
	 * @return int|float
	 */
	protected function parseAmount( $amount, $convert = false ) {
		$amount = $this->parseAmountFromString( $this->parseAmountFromCallable( $amount ) );

		if ( is_int( $amount ) ) {
			return (int) $this->convertAmount( $amount, $convert );
		}
		if ( is_float( $amount ) ) {
			return (float) round( $this->convertAmount( $amount, $convert ), $this->currency->get_precision() );
		}

		return 0;
	}

	/**
	 * parseAmountFromCallable.
	 *
	 * @since 1.0.2
	 *
	 * @param mixed $amount
	 *
	 * @return mixed
	 */
	protected function parseAmountFromCallable( $amount ) {
		if ( ! is_callable( $amount ) ) {
			return $amount;
		}

		return $amount();
	}

	/**
	 * parseAmountFromString.
	 *
	 * @since 1.0.2
	 *
	 * @param mixed $amount
	 *
	 * @return int|float|mixed
	 */
	protected function parseAmountFromString( $amount ) {
		if ( ! is_string( $amount ) ) {
			return $amount;
		}

		$thousandsSeparator = $this->currency->get_thousand_separator( 'edit' );
		$decimalMark        = $this->currency->get_decimal_separator( 'edit' );

		$amount = str_replace( $this->currency->get_symbol(), '', $amount );
		$amount = preg_replace( '/[^0-9\\' . $thousandsSeparator . '\\' . $decimalMark . '\-\+]/', '', $amount );
		$amount = str_replace(
			array(
				$thousandsSeparator,
				$decimalMark,
			),
			array( '', '.' ),
			$amount
		);

		if ( preg_match( '/^([\-\+])?\d+$/', $amount ) ) {
			$amount = (int) $amount;
		} elseif ( preg_match( '/^([\-\+])?\d+\.\d+$/', $amount ) ) {
			$amount = (float) $amount;
		}

		return $amount;
	}

	/**
	 * convertAmount.
	 *
	 * @since 1.0.2
	 *
	 * @param int|float $amount
	 * @param bool      $convert
	 *
	 * @return int|float
	 */
	protected function convertAmount( $amount, $convert = false ) {
		if ( ! $convert ) {
			return $amount;
		}

		return $amount * $this->currency->get_subunit();
	}

	/**
	 * __callStatic.
	 *
	 * @since 1.0.2
	 *
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return Money
	 */
	public static function __callStatic( $method, array $arguments ) {
		$convert = ( isset( $arguments[1] ) && is_bool( $arguments[1] ) ) ? (bool) $arguments[1] : false;

		return new static( $arguments[0], new Currency( $method ), $convert );
	}

	/**
	 * assertSameCurrency.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $other
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function assertSameCurrency( self $other ) {
		if ( ! $this->isSameCurrency( $other ) ) {
			throw new \InvalidArgumentException( 'Different currencies "' . $this->currency . '" and "' . $other->currency . '"' );
		}
	}

	/**
	 * assertOperand.
	 *
	 * @since 1.0.2
	 *
	 * @param int|float $operand
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function assertOperand( $operand ) {
		if ( ! is_int( $operand ) && ! is_float( $operand ) ) {
			throw new \InvalidArgumentException( 'Operand "' . $operand . '" should be an integer or a float' );
		}
	}

	/**
	 * assertRoundingMode.
	 *
	 * @since 1.0.2
	 *
	 * @param int $roundingMode
	 *
	 * @throws \OutOfBoundsException
	 */
	protected function assertRoundingMode( $roundingMode ) {
		$roundingModes = array( self::ROUND_HALF_DOWN, self::ROUND_HALF_EVEN, self::ROUND_HALF_ODD, self::ROUND_HALF_UP );

		if ( ! in_array( $roundingMode, $roundingModes ) ) {
			throw new \OutOfBoundsException( 'Rounding mode should be ' . implode( ' | ', $roundingModes ) );
		}
	}

	/**
	 * getAmount.
	 *
	 * @since       1.0.2
	 *
	 * @return int|float
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * getValue.
	 *
	 * @since 1.0.2
	 *
	 * @return float
	 */
	public function getValue() {
		if ( $this->currency->get_subunit() && $this->currency->get_precision() ) {
			return round( $this->amount / $this->currency->get_subunit(), $this->currency->get_precision() );
		}

		return $this->amount;
	}

	/**
	 * getCurrency.
	 *
	 * @since 1.0.2
	 *
	 * @return Currency
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * isSameCurrency.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function isSameCurrency( self $other ) {
		return $this->currency->equals( $other->currency );
	}

	/**
	 * compare.
	 *
	 * @since       1.0.2
	 *
	 * @param Money $other
	 *
	 * @throws \InvalidArgumentException
	 * @return int
	 */
	public function compare( self $other ) {
		$this->assertSameCurrency( $other );

		if ( $this->amount < $other->amount ) {
			return - 1;
		}

		if ( $this->amount > $other->amount ) {
			return 1;
		}

		return 0;
	}

	/**
	 * equals.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function equals( self $other ) {
		return $this->compare( $other ) === 0;
	}

	/**
	 * greaterThan.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function greaterThan( self $other ) {
		return $this->compare( $other ) === 1;
	}

	/**
	 * greaterThanOrEqual.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function greaterThanOrEqual( self $other ) {
		return $this->compare( $other ) >= 0;
	}

	/**
	 * lessThan.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function lessThan( self $other ) {
		return $this->compare( $other ) === - 1;
	}

	/**
	 * lessThanOrEqual.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function lessThanOrEqual( self $other ) {
		return $this->compare( $other ) <= 0;
	}

	/**
	 * convert.
	 *
	 * @since 1.0.2
	 *
	 * @param Currency  $currency
	 * @param int|float $ratio
	 * @param int       $roundingMode
	 *
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 *
	 * @return Money
	 */
	public function convert( Currency $currency, $ratio, $roundingMode = self::ROUND_HALF_UP ) {
		$this->currency = $currency;

		$this->assertOperand( $ratio );
		$this->assertRoundingMode( $roundingMode );

		if ( $ratio < 1 ) {
			return $this->divide( $ratio, $roundingMode );
		}

		return $this->multiply( $ratio, $roundingMode );
	}

	/**
	 * add.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $addend
	 *
	 * @return Money
	 */
	public function add( self $addend ) {
		$this->assertSameCurrency( $addend );

		return new self( $this->amount + $addend->amount, $this->currency );
	}

	/**
	 * subtract.
	 *
	 * @since 1.0.2
	 *
	 * @param Money $subtrahend
	 *
	 * @throws \InvalidArgumentException
	 * @return Money
	 */
	public function subtract( self $subtrahend ) {
		$this->assertSameCurrency( $subtrahend );

		return new self( $this->amount - $subtrahend->amount, $this->currency );
	}

	/**
	 * multiply.
	 *
	 * @since 1.0.2
	 *
	 * @param int|float $multiplier
	 * @param int       $roundingMode
	 *
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 *
	 * @return Money
	 */
	public function multiply( $multiplier, $roundingMode = self::ROUND_HALF_UP ) {
		return new self( round( $this->amount * $multiplier, $this->currency->get_precision(), $roundingMode ), $this->currency );
	}

	/**
	 * divide.
	 *
	 * @since 1.0.2
	 *
	 * @param int|float $divisor
	 * @param int       $roundingMode
	 *
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 *
	 * @return Money
	 */
	public function divide( $divisor, $roundingMode = self::ROUND_HALF_UP ) {
		$this->assertOperand( $divisor );
		$this->assertRoundingMode( $roundingMode );
		if ( empty( $divisor ) ) {
			/* translators: %s amount %s currency */
			eaccounting_doing_it_wrong( __METHOD__, sprintf( __( 'Division by zero is not permitted amount %1$s currency %2$s','wp-ever-accounting' ), $this->amount, $this->currency ), null );
			$divisor = 1;
		}

		return new self( round( $this->amount / $divisor, $this->currency->get_precision(), $roundingMode ), $this->currency );
	}

	/**
	 * allocate.
	 *
	 * @since  1.0.2
	 *
	 * @param array $ratios
	 *
	 * @return array
	 */
	public function allocate( array $ratios ) {
		$remainder = $this->amount;
		$results   = array();
		$total     = array_sum( $ratios );

		foreach ( $ratios as $ratio ) {
			$share      = floor( $this->amount * $ratio / $total );
			$results[]  = new self( $share, $this->currency );
			$remainder -= $share;
		}

		for ( $i = 0; $remainder > 0; $i ++ ) {
			$results[ $i ]->amount ++;
			$remainder --;
		}

		return $results;
	}

	/**
	 * isZero.
	 *
	 * @since 1.0.2
	 *
	 * @return bool
	 */
	public function isZero() {
		return $this->amount === 0;
	}

	/**
	 * isPositive.
	 *
	 * @since 1.0.2
	 *
	 * @return bool
	 */
	public function isPositive() {
		return $this->amount > 0;
	}

	/**
	 * isNegative.
	 *
	 * @since 1.0.2
	 *
	 * @return bool
	 */
	public function isNegative() {
		return $this->amount < 0;
	}

	/**
	 * formatSimple.
	 *
	 * @since 1.0.2
	 *
	 * @return string
	 */
	public function format_simple() {
		return number_format(
			$this->getValue(),
			$this->currency->get_precision(),
			$this->currency->get_decimal_separator( 'edit' ),
			$this->currency->get_thousand_separator( 'edit' )
		);
	}

	/**
	 * format.
	 *
	 * @since 1.0.2
	 *
	 * @return string
	 */
	public function format() {
		$negative  = $this->isNegative();
		$value     = $this->getValue();
		$amount    = $negative ? - $value : $value;
		$thousands = $this->currency->get_thousand_separator( 'edit' );
		$decimals  = $this->currency->get_decimal_separator( 'edit' );
		$prefix    = $this->currency->get_prefix();
		$suffix    = $this->currency->get_suffix();
		$value     = number_format( $amount, $this->currency->get_precision(), $decimals, $thousands );

		return ( $negative ? '-' : '' ) . $prefix . $value . $suffix;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @since  1.0.2
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'amount'   => $this->amount,
			'value'    => $this->getValue(),
			'currency' => $this->currency,
		);
	}

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param int $options
	 *
	 * @since 1.0.2
	 *
	 * @return string
	 */
	public function toJson( $options = 0 ) {
		return json_encode( $this->toArray(), $options );
	}

	/**
	 * jsonSerialize.
	 *
	 * @since  1.0.2
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * __toString.
	 *
	 * @since 1.0.2
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->format();
	}
}
