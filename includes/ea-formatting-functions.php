<?php
/**
 * EverAccounting Formatting Functions for formatting data.
 *
 * @since   1.0.2
 * @package EverAccounting
 */

defined( 'ABSPATH' ) || exit;

/**
 * Converts a string (e.g. 'yes' or 'no') to a bool.
 *
 * @since 1.0.2
 *
 * @param string|boolean $string String to convert.
 *
 * @return bool
 */
function eaccounting_string_to_bool( $string ) {
	return is_bool( $string ) ? $string : ( in_array( strtolower( $string ), array( 'yes', 'true', 'active', 'enabled' ), true ) || 1 === $string || '1' === $string );
}

/**
 * Converts a bool to a 'yes' or 'no'.
 *
 * @since 1.0.2
 *
 * @param bool $bool String to convert.
 *
 * @return string
 */
function eaccounting_bool_to_string( $bool ) {
	if ( ! is_bool( $bool ) ) {
		$bool = eaccounting_string_to_bool( $bool );
	}

	return true === $bool ? 'yes' : 'no';
}

/**
 * Converts a bool to a 1 or 0.
 *
 * @since 1.1.0
 *
 * @param $bool
 *
 * @return int
 */
function eaccounting_bool_to_number( $bool ) {
	if ( ! is_bool( $bool ) ) {
		$bool = eaccounting_string_to_bool( $bool );
	}

	return true === $bool ? 1 : 0;
}

/**
 * Explode a string into an array by $delimiter and remove empty values.
 *
 * @since 1.0.2
 *
 * @param string|array $string    String to convert.
 *
 * @param string       $delimiter Delimiter, defaults to ','.
 *
 * @return array
 */
function eaccounting_string_to_array( $string, $delimiter = ',' ) {
	return is_array( $string ) ? $string : array_filter( explode( $delimiter, $string ) );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @since 1.0.2
 *
 * @param string|array $var Data to sanitize.
 *
 * @return string|array
 */
function eaccounting_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'eaccounting_clean', $var );
	}

	return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
}


/**
 * Run eaccounting_clean over posted textarea but maintain line breaks.
 *
 * @since  1.0.2
 *
 * @param string $var Data to sanitize.
 *
 * @return string
 */
function eaccounting_sanitize_textarea( $var ) {
	return implode( "\n", array_map( 'eaccounting_clean', explode( "\n", $var ) ) );
}


/**
 * Sanitize a string destined to be a tooltip.
 *
 * @since  1.0.2 Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 *
 * @param string $var Data to sanitize.
 *
 * @return string
 */
function eaccounting_sanitize_tooltip( $var ) {
	return htmlspecialchars(
		wp_kses(
			html_entity_decode( $var ),
			array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			)
		)
	);
}


/**
 * EverAccounting date format - Allows to change date format for everything.
 *
 * @since 1.0.2
 * @return string
 */
function eaccounting_date_format() {
	return apply_filters( 'eaccounting_date_format', eaccounting()->settings->get( 'date_format', 'Y-m-d' ) );
}

/**
 * EverAccounting Time Format - Allows to change time format for everything.
 *
 * @since 1.0.2
 * @return string
 */
function eaccounting_time_format() {
	return apply_filters( 'eaccounting_time_format', eaccounting()->settings->get( 'time_format', 'H:i' ) );
}

/**
 * Format a date for output.
 *
 * @since 1.1.0
 *
 * @param string $format
 * @param        $date
 *
 * @return string
 */
function eaccounting_date( $date, $format = '' ) {

	if ( empty( $date ) || '0000-00-00 00:00:00' === $date || '0000-00-00' === $date ) {
		return '';
	}

	if ( ! $format ) {
		$format = eaccounting_date_format();
	}

	if ( ! is_numeric( $date ) ) {
		$date = strtotime( $date );
	}

	return date_i18n( $format, $date );
}

/**
 * Array merge and sum function.
 *
 * Source:  https://gist.github.com/Nickology/f700e319cbafab5eaedc
 *
 * @since 1.0.2
 * @return array
 */
function eaccounting_array_merge_recursive() {
	$arrays = func_get_args();

	// If there's only one array, it's already merged.
	if ( 1 === count( $arrays ) ) {
		return $arrays[0];
	}

	// Remove any items in $arrays that are NOT arrays.
	foreach ( $arrays as $key => $array ) {
		if ( ! is_array( $array ) ) {
			unset( $arrays[ $key ] );
		}
	}

	// We start by setting the first array as our final array.
	// We will merge all other arrays with this one.
	$final = array_shift( $arrays );

	foreach ( $arrays as $b ) {
		foreach ( $final as $key => $value ) {
			// If $key does not exist in $b, then it is unique and can be safely merged.
			if ( ! isset( $b[ $key ] ) ) {
				$final[ $key ] = $value;
			} else {
				// If $key is present in $b, then we need to merge and sum numeric values in both.
				if ( is_numeric( $value ) && is_numeric( $b[ $key ] ) ) {
					// If both values for these keys are numeric, we sum them.
					$final[ $key ] = $value + $b[ $key ];
				} elseif ( is_array( $value ) && is_array( $b[ $key ] ) ) {
					// If both values are arrays, we recursively call ourself.
					$final[ $key ] = eaccounting_array_merge_recursive( $value, $b[ $key ] );
				} else {
					// If both keys exist but differ in type, then we cannot merge them.
					// In this scenario, we will $b's value for $key is used.
					$final[ $key ] = $b[ $key ];
				}
			}
		}

		// Finally, we need to merge any keys that exist only in $b.
		foreach ( $b as $key => $value ) {
			if ( ! isset( $final[ $key ] ) ) {
				$final[ $key ] = $value;
			}
		}
	}

	return $final;
}

/**
 * Implode and escape HTML attributes for output.
 *
 * @since 1.0.2
 *
 * @param array $raw_attributes Attribute name value pairs.
 *
 * @return string
 */
function eaccounting_implode_html_attributes( $raw_attributes ) {
	$attributes     = array();
	$raw_attributes = array_filter( $raw_attributes );
	foreach ( $raw_attributes as $name => $value ) {
		$attributes[] = esc_attr( $name ) . '="' . esc_attr( trim( $value ) ) . '"';
	}

	return implode( ' ', $attributes );
}

/**
 * Escape JSON for use on HTML or attribute text nodes.
 *
 * @since 1.0.2
 *
 * @param string $json JSON to escape.
 *
 * @param bool   $html True if escaping for HTML text node, false for attributes. Determines how quotes are handled.
 *
 * @return string Escaped JSON.
 */
function eaccounting_esc_json( $json, $html = false ) {
	return _wp_specialchars(
		$json,
		$html ? ENT_NOQUOTES : ENT_QUOTES, // Escape quotes in attribute nodes only.
		'UTF-8',  // json_encode() outputs UTF-8 (really just ASCII), not the blog's charset.
		true  // Double escape entities: `&amp;` -> `&amp;amp;`.
	);
}

/**
 * Get only numbers from the string.
 *
 * @since 1.0.2
 *
 * @param      $number
 *
 * @param bool $allow_decimal
 *
 * @return int|float|null
 */
function eaccounting_sanitize_number( $number, $allow_decimal = true ) {
	// Convert multiple dots to just one.
	$number = preg_replace( '/\.(?![^.]+$)|[^0-9.-]/', '', eaccounting_clean( $number ) );

	if ( $allow_decimal ) {
		return (float) preg_replace( '/[^0-9.-]/', '', $number );
	}

	return (int) preg_replace( '/[^0-9]/', '', $number );
}

/**
 * Get only numbers from the string.
 *
 * @since 1.0.2
 *
 * @param      $number
 *
 * @param int  $decimals
 * @param bool $trim_zeros
 *
 * @return int|float|null
 */
function eaccounting_format_decimal( $number, $decimals = 4, $trim_zeros = false ) {

	// Convert multiple dots to just one.
	$number = preg_replace( '/\.(?![^.]+$)|[^0-9.-]/', '', eaccounting_clean( $number ) );

	if ( is_numeric( $decimals ) ) {
		$number = number_format( floatval( $number ), $decimals, '.', '' );
	}

	if ( $trim_zeros && strstr( $number, '.' ) ) {
		$number = rtrim( rtrim( $number, '0' ), '.' );
	}

	return $number;
}

/**
 * Convert a date string to a EverAccounting_DateTime.
 *
 * @since  1.0.2
 *
 * @param string $time_string Time string.
 *
 * @throws Exception
 * @return \EverAccounting\DateTime
 */
function eaccounting_string_to_datetime( $time_string ) {
	// Strings are defined in local WP timezone. Convert to UTC.
	if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $time_string, $date_bits ) ) {
		$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : ( (float) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
	} elseif ( is_numeric( $time_string ) ) {
		$local_time = gmdate( 'Y-m-d H:i:s', $time_string );
		$timezone   = wp_timezone();
		$datetime   = date_create( $local_time, $timezone );
		$timestamp  = $datetime->getTimestamp();
	} else {
		$original_timezone = date_default_timezone_get();
		date_default_timezone_set( 'UTC' );
		$timestamp = strtotime( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $time_string ) ) ) );
		date_default_timezone_set( $original_timezone );
	}
	$datetime = new \EverAccounting\DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );

	return $datetime;
}

/**
 * Convert RGB to HEX.
 *
 * @since 1.1.0
 *
 * @param mixed $color Color.
 *
 * @return array
 */
function eaccounting_rgb_from_hex( $color ) {
	$color = str_replace( '#', '', $color );
	// Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF".
	$color = preg_replace( '~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color );

	$rgb      = array();
	$rgb['R'] = hexdec( $color[0] . $color[1] );
	$rgb['G'] = hexdec( $color[2] . $color[3] );
	$rgb['B'] = hexdec( $color[4] . $color[5] );

	return $rgb;
}

/**
 * Make HEX color darker.
 *
 * @param mixed $color  Color.
 * @param int   $factor Darker factor.
 *                      Defaults to 30.
 *
 * @return string
 */
function eaccounting_hex_darker( $color, $factor = 30 ) {
	$base  = eaccounting_rgb_from_hex( $color );
	$color = '#';

	foreach ( $base as $k => $v ) {
		$amount      = $v / 100;
		$amount      = eaccounting_format_decimal( ($amount * $factor), false );
		$new_decimal = $v - $amount;

		$new_hex_component = dechex( $new_decimal );
		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = '0' . $new_hex_component;
		}
		$color .= $new_hex_component;
	}

	return $color;
}

/**
 * Make HEX color lighter.
 *
 * @param mixed $color  Color.
 * @param int   $factor Lighter factor.
 *                      Defaults to 30.
 *
 * @return string
 */
function eaccounting_hex_lighter( $color, $factor = 30 ) {
	$base  = eaccounting_rgb_from_hex( $color );
	$color = '#';

	foreach ( $base as $k => $v ) {
		$amount      = 255 - $v;
		$amount      = $amount / 100;
		$amount      = eaccounting_format_decimal( ($amount * $factor), false );
		$new_decimal = $v + $amount;

		$new_hex_component = dechex( $new_decimal );
		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = '0' . $new_hex_component;
		}
		$color .= $new_hex_component;
	}

	return $color;
}

/**
 * Determine whether a hex color is light.
 *
 * @param mixed $color Color.
 *
 * @return bool  True if a light color.
 */
function eaccounting_hex_is_light( $color ) {
	$hex = str_replace( '#', '', $color );

	$c_r = hexdec( substr( $hex, 0, 2 ) );
	$c_g = hexdec( substr( $hex, 2, 2 ) );
	$c_b = hexdec( substr( $hex, 4, 2 ) );

	$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

	return $brightness > 155;
}

/**
 * Detect if we should use a light or dark color on a background color.
 *
 * @param mixed  $color Color.
 * @param string $dark  Darkest reference.
 *                      Defaults to '#000000'.
 * @param string $light Lightest reference.
 *                      Defaults to '#FFFFFF'.
 *
 * @return string
 */
function eaccounting_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
	return eaccounting_hex_is_light( $color ) ? $dark : $light;
}

/**
 * Format string as hex.
 *
 * @param string $hex HEX color.
 *
 * @return string|null
 */
function eaccounting_format_hex( $hex ) {
	$hex = trim( str_replace( '#', '', $hex ) );

	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	return $hex ? '#' . $hex : null;
}

/**
 * Dictionary for amount to text converter
 *
 * @since 1.1.0
 * @return array
 */
function eaccounting_number_dictionary() {
	return apply_filters(
		'eaccounting_number_dictionary',
		array(
			0                   => __( 'Zero', 'wp-ever-accounting' ),
			1                   => __( 'One', 'wp-ever-accounting' ),
			2                   => __( 'Two', 'wp-ever-accounting' ),
			3                   => __( 'Three', 'wp-ever-accounting' ),
			4                   => __( 'Four', 'wp-ever-accounting' ),
			5                   => __( 'Five', 'wp-ever-accounting' ),
			6                   => __( 'Six', 'wp-ever-accounting' ),
			7                   => __( 'Seven', 'wp-ever-accounting' ),
			8                   => __( 'Eight', 'wp-ever-accounting' ),
			9                   => __( 'Nine', 'wp-ever-accounting' ),
			10                  => __( 'Ten', 'wp-ever-accounting' ),
			11                  => __( 'Eleven', 'wp-ever-accounting' ),
			12                  => __( 'Twelve', 'wp-ever-accounting' ),
			13                  => __( 'Thirteen', 'wp-ever-accounting' ),
			14                  => __( 'Fourteen', 'wp-ever-accounting' ),
			15                  => __( 'Fifteen', 'wp-ever-accounting' ),
			16                  => __( 'Sixteen', 'wp-ever-accounting' ),
			17                  => __( 'Seventeen', 'wp-ever-accounting' ),
			18                  => __( 'Eighteen', 'wp-ever-accounting' ),
			19                  => __( 'Nineteen', 'wp-ever-accounting' ),
			20                  => __( 'Twenty', 'wp-ever-accounting' ),
			30                  => __( 'Thirty', 'wp-ever-accounting' ),
			40                  => __( 'Fourty', 'wp-ever-accounting' ),
			50                  => __( 'Fifty', 'wp-ever-accounting' ),
			60                  => __( 'Sixty', 'wp-ever-accounting' ),
			70                  => __( 'Seventy', 'wp-ever-accounting' ),
			80                  => __( 'Eighty', 'wp-ever-accounting' ),
			90                  => __( 'Ninety', 'wp-ever-accounting' ),
			100                 => __( 'Hundred', 'wp-ever-accounting' ),
			1000                => __( 'Thousand', 'wp-ever-accounting' ),
			1000000             => __( 'Million', 'wp-ever-accounting' ),
			1000000000          => __( 'Billion', 'wp-ever-accounting' ),
			1000000000000       => __( 'Trillion', 'wp-ever-accounting' ),
			1000000000000000    => __( 'Quadrillion', 'wp-ever-accounting' ),
			1000000000000000000 => __( 'Quintillion', 'wp-ever-accounting' ),
		)
	);
}

/**
 * Convert Number to words
 *
 * @since 1.1.0
 *
 * @param $amount
 *
 * @return string|null
 *
 */
function eaccounting_numbers_to_words( $number ) {
	$hyphen      = '-';
	$conjunction = ' and ';
	$separator   = ', ';
	$negative    = 'negative ';
	$decimal     = ' point ';

	if ( ! is_numeric( $number ) ) {
		return false;
	}
	$string = '';
	switch ( true ) {
		case $number < 21:
			$string = eaccounting_number_dictionary()[ $number ];
			break;
		case $number < 100:
			$tens   = ( (int) ( $number / 10 ) ) * 10;
			$units  = $number % 10;
			$string = eaccounting_number_dictionary()[ $tens ];
			if ( $units ) {
				$string .= ' ' . eaccounting_number_dictionary()[ $units ];
			}
			break;
		case $number < 1000:
			$hundreds  = $number / 100;
			$remainder = $number % 100;
			$string    = eaccounting_number_dictionary()[ $hundreds ] . ' ' . eaccounting_number_dictionary()[100];
			if ( $remainder ) {
				$string .= $conjunction . eaccounting_numbers_to_words( $remainder );
			}
			break;
		default:
			$baseUnit     = pow( 1000, floor( log( $number, 1000 ) ) );
			$numBaseUnits = (int) ( $number / $baseUnit );
			$remainder    = $number % $baseUnit;
			$string       = eaccounting_numbers_to_words( $numBaseUnits ) . ' ' . eaccounting_number_dictionary()[ $baseUnit ];
			if ( $remainder ) {
				$string .= $remainder < 100 ? $conjunction : $separator;
				$string .= eaccounting_numbers_to_words( $remainder );
			}
			break;
	}

	return $string;

}

function eaccounting_format_address( $address, $break = '<br>' ) {
	$address   = wp_parse_args(
		$address,
		array(
			'street'   => '',
			'city'     => '',
			'state'    => '',
			'postcode' => '',
			'country'  => '',
		)
	);
	$countries = eaccounting_get_countries();
	if ( ! empty( $address['country'] ) && isset( $countries[ $address['country'] ] ) ) {
		$address['country'] = $countries[ $address['country'] ];
	}

	$line_1       = $address['street'];
	$line_2       = implode( ' ', array_filter( array( $address['city'], $address['state'], $address['postcode'] ) ) );
	$line_3       = $address['country'];
	$full_address = array_filter( array( $line_1, $line_2, $line_3 ) );

	return implode( $break, $full_address );
}

