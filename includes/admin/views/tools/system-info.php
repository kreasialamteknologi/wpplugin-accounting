<?php
/**
 * Displays the system info report.
 *
 * @return string The compiled system info report.
 * @since 1.0.2
 *
 */
defined( 'ABSPATH' ) || exit();

function eaccounting_tools_system_info_report() {

	global $wpdb;

	// Get theme info
	$theme_data = wp_get_theme();
	$theme      = $theme_data->Name . ' ' . $theme_data->Version;

	$return = '### Begin System Info ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$locale = get_locale();

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( empty( $locale ) ? 'en_US' : $locale ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if ( get_option( 'show_on_front' ) === 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id  = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	$return .= 'ABSPATH:                  ' . ABSPATH . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Statuses: ' . implode( ', ', get_post_stati() ) . "\n";

	//
	// EverAccounting
	//

	$settings     = eaccounting()->settings;
	$db_version   = get_option( 'eaccounting_version' );
	$install_date = get_option( 'eaccounting_install_date' );
	global $wpdb;
	$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}ea_%'" );
	$tables = preg_replace( "/^{$wpdb->prefix}/", '', $tables );

	// Configariotn settings.
	$return .= "\n" . '-- EverAccounting Configuration' . "\n\n";
	$return .= 'Version:                          ' . eaccounting()->get_version() . "\n";
	$return .= 'DB Version:                       ' . ( $db_version ? "$db_version\n" : "Unset\n" );
	$return .= 'Install Date:                     ' . ( $install_date ? date_i18n( 'Y-m-d H:i:s', $install_date ) . "\n" : "Unset\n" );
	$return .= 'Debug Mode:                       ' . ( $settings->get( 'debug_mode', false ) ? 'True' . "\n" : "False\n" );
	$return .= 'Transactions Table:               ' . ( in_array( 'ea_transactions', $tables, true ) ? 'True' . "\n" : "False\n" );
	$return .= 'Contacts Table:                   ' . ( in_array( 'ea_contacts', $tables, true ) ? 'True' . "\n" : "False\n" );
	$return .= 'Contactmeta Table:                ' . ( in_array( 'ea_contactmeta', $tables, true ) ? 'True' . "\n" : "False\n" );
	$return .= 'Transfers Table:                  ' . ( in_array( 'ea_transfers', $tables, true ) ? 'True' . "\n" : "False\n" );
	$return .= 'Categories Table:                 ' . ( in_array( 'ea_categories', $tables, true ) ? 'True' . "\n" : "False\n" );
	$return .= 'Documents Table:                  ' . ( in_array( 'ea_documents', $tables, true ) ? 'True' . "\n" : "False\n" );
	$return .= 'Document Item Table:              ' . ( in_array( 'ea_document_items', $tables, true ) ? 'True' . "\n" : "False\n" );
	$return .= 'Items Table:                      ' . ( in_array( 'ea_items', $tables, true ) ? 'True' . "\n" : "False\n" );

	// Misc Settings
	$currency_code = eaccounting_get_option( 'default_currency' );
	$currency      = eaccounting_get_currency( $currency_code );
	$return        .= "\n" . '-- EverAccounting Settings' . "\n\n";

	$return .= 'Default currency:                  ' . $currency_code . "\n";
	$return .= 'Default currency rate:             ' . ( ! empty( $currency ) ? $currency->get_rate() : "" ) . "\n";
	$return .= 'Default payment method:            ' . eaccounting_get_option( 'default_payment_method' ) . "\n";
	$return .= 'Default Account:                   ' . eaccounting_get_option( 'default_account' ) . "\n";

	// Object counts.
	$return .= "\n" . '-- EverAccounting Object Counts' . "\n\n";
	$return .= 'Items:                            ' . number_format( eaccounting_get_items( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Transactions:                     ' . number_format( eaccounting_get_transactions( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Accounts:                         ' . number_format( eaccounting_get_accounts( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Customers:                        ' . number_format( eaccounting_get_customers( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Vendors:                          ' . number_format( eaccounting_get_vendors( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Currencies:                       ' . number_format( eaccounting_get_currencies( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Categories:                       ' . number_format( eaccounting_get_categories( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Transfers:                        ' . number_format( eaccounting_get_transfers( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Invoices:                         ' . number_format( eaccounting_get_invoices( array( 'count_total' => true ) ) ) . "\n";
	$return .= 'Bills:                            ' . number_format( eaccounting_get_bills( array( 'count_total' => true ) ) ) . "\n";

	// Get plugins that have an update
	$updates = get_plugin_updates();

	// Must-use plugins
	// NOTE: MU plugins can't show updates!
	$muplugins = get_mu_plugins();
	if ( count( $muplugins ) > 0 ) {
		$return .= "\n" . '-- Must-Use Plugins' . "\n\n";

		foreach ( $muplugins as $plugin => $plugin_data ) {
			$return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
		}
	}

	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

	$plugins        = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( ! in_array( $plugin_path, $active_plugins ) ) {
			continue;
		}

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( in_array( $plugin_path, $active_plugins ) ) {
			continue;
		}

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
	}

	if ( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins        = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach ( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
				continue;
			}

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$plugin = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";
	$return .= 'SSL Configured:           ' . ( is_ssl() ? 'Yes' : 'No' ) . "\n";

	// PHP configuration
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return .= "\n" . '### End System Info ###';

	return $return;
}
