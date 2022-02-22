<?php
/**
 * Admin Settings Page.
 *
 * @since       1.0.2
 * @subpackage  Admin/Settings
 * @package     EverAccounting
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit();

function eaccounting_settings_keys_section(){
	echo 'HELLO';
}

//todo remove main section hook later
add_action( 'eaccounting_settings_tab_advanced_section_keys', 'eaccounting_settings_keys_section');
add_action( 'eaccounting_settings_tab_advanced_section_main', 'eaccounting_settings_keys_section');
