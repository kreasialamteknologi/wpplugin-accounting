<?php
/**
 * Options for plugin
 */

namespace EverAccounting;

/**
 * Class Options
 * @package EverAccounting
 */
class Options {
	/**
	 * @var array
	 */
	var $options = array();

	/**
	 * Options constructor.
	 */
	public function __construct() {
		$this->options = (array) get_option( 'eaccounting_settings', array() );
	}


	/**
	 * Get the value of a specific option
	 *
	 * @param mixed $default (optional)
	 *
	 * @param string $key
	 *
	 * @since  1.0.2
	 *
	 * @return mixed
	 */
	function get( $key, $default = false ) {
		$value = ! empty( $this->options[ $key ] ) ? $this->options[ $key ] : $default;

		return apply_filters( "ever_accounting_option__{$key}", $value );
	}

	/**
	 * Update option value
	 *
	 * @param $option_id
	 * @param $value
	 */
	function update( $option_id, $value ) {
		$this->options[ $option_id ] = $value;
		update_option( 'eaccounting_settings', $this->options );
	}


	/**
	 * Delete option
	 *
	 * @param $option_id
	 */
	function remove( $option_id ) {
		if ( ! empty( $this->options[ $option_id ] ) ) {
			unset( $this->options[ $option_id ] );
		}

		update_option( 'eaccounting_settings', $this->options );
	}

	/**
	 * Get all settings
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_all() {
		return $this->options;
	}

	/**
	 * Sets an option (in memory).
	 *
	 * @param bool $save Optional. Whether to trigger saving the option or options. Default false.
	 *
	 * @param array $options An array of `key => value` setting pairs to set.
	 *
	 * @return bool If `$save` is not false, whether the options were saved successfully. True otherwise.
	 * @since  1.0.2
	 * @access public
	 *
	 */
	public function set( $options, $save = false ) {
		foreach ( $options as $option => $value ) {
			$this->options[ $option ] = $value;
		}

		if ( false !== $save ) {
			return update_option( 'eaccounting_settings', $this->options );
		}

		return true;
	}
}
