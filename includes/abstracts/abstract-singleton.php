<?php

namespace EverAccounting\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Class Singleton
 * @package EverAccounting\Abstracts
 */
abstract class Singleton {
	/**
	 * @var $this []
	 */
	protected static $instance = array();

	/**
	 * @return $this
	 */
	public static function instance() {
		$class = get_called_class();

		if ( ! array_key_exists( $class, self::$instance ) ) {
			self::$instance[ $class ] = new $class();
		}

		return self::$instance[ $class ];
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {
	}

	/**
	 * Prevent unserializing.
	 */
	final public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wp-ever-accounting' ), '1.1.0' );
		die();
	}
}
