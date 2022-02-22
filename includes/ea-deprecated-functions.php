<?php
/**
 * Deprecated functions
 *
 * Where functions come to die.
 *
 * @author   EverAccounting
 * @category Core
 * @package  EverAccounting\Functions
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

function eaccounting_get_global_currencies() {
	return eaccounting_get_currency_codes();
}
