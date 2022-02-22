<?php
/**
 * Interface JSONable loader.
 *
 * @since       1.0.2
 * @subpackage  Interfaces
 * @package     EverAccounting\Includes
 */

namespace EverAccounting\Interfaces;

defined('ABSPATH') || exit;

/**
 * Interface for any object that can be casted to JSON.
 */
interface JSONable {
	/**
	 * Returns object as JSON string.
	 *
	 * @since 1.0.2
	 */
	public function __toJSON( $options = 0, $depth = 512);
	/**
	 * Returns object as JSON string.
	 *
	 * @since 1.0.2
	 */
	public function to_JSON( $options = 0, $depth = 512);
}
