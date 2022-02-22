<?php
/**
 * EverAccounting File Functions.
 *
 * File related functions.
 *
 * @since   1.0.2
 * @package EverAccounting
 */

defined( 'ABSPATH' ) || exit();

/**
 * Set upload directory for Accounting
 *
 * @since 1.0.2
 * @return array
 */
function eaccounting_get_upload_dir() {
	$upload            = wp_upload_dir();
	$upload['basedir'] = $upload['basedir'] . '/eaccounting';
	$upload['path']    = $upload['basedir'] . $upload['subdir'];
	$upload['baseurl'] = $upload['baseurl'] . '/eaccounting';
	$upload['url']     = $upload['baseurl'] . $upload['subdir'];

	return $upload;
}

/**
 * Scan folders
 *
 * @since 1.0.2
 *
 * @param array  $return
 *
 * @param string $path
 *
 * @return array
 */
function eaccounting_scan_folders( $path = '', $return = array() ) {
	$path  = $path == '' ? dirname( __FILE__ ) : $path;
	$lists = @scandir( $path );

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $f ) {
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != '.' && $f != '..' ) {
				if ( ! in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) ) {
					$return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );
				}

				eaccounting_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return );
			}
		}
	}

	return $return;
}

/**
 * Protect accounting files
 *
 * @since 1.0.2
 *
 * @param bool $force
 */
function eaccounting_protect_files( $force = false ) {

	if ( false === get_transient( 'eaccounting_check_protection_files' ) || $force ) {
		$upload_dir = eaccounting_get_upload_dir();
		if ( ! is_dir( $upload_dir['path'] ) ) {
			wp_mkdir_p( $upload_dir['path'] );
		}

		$base_dir = $upload_dir['basedir'];

		$htaccess = trailingslashit( $base_dir ) . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			$rule = "Options -Indexes\n";
			$rule .= "deny from all\n";
			$rule .= "<FilesMatch '\.(jpg|jpeg|png|pdf|doc|docx|xls)$'>\n";
			$rule .= "Order Allow,Deny\n";
			$rule .= "Allow from all\n";
			$rule .= "</FilesMatch>\n";
			@file_put_contents( $htaccess, $rule );
		}

		// Top level blank index.php
		if ( ! file_exists( $base_dir . '/index.php' ) && wp_is_writable( $base_dir ) ) {
			@file_put_contents( $base_dir . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		$folders = eaccounting_scan_folders( $base_dir );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist
			if ( ! file_exists( $folder . 'index.php' ) && wp_is_writable( $folder ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}

		// Check for the files once per day
		set_transient( 'eaccounting_check_protection_files', true, 3600 * 24 );
	}
}

/**
 * Conditionally change upload folder if accounting assets.
 *
 * @since 1.1.0
 *
 * @param $pathdata
 *
 * @return mixed
 */
function eaccounting_handle_upload_folder( $pathdata ) {
	if ( isset( $_POST['type'] ) && 'eaccounting_file' === $_POST['type'] ) { // WPCS: CSRF ok, input var ok.
		if ( empty( $pathdata['subdir'] ) ) {
			$pathdata['path']   = $pathdata['path'] . '/eaccounting';
			$pathdata['url']    = $pathdata['url'] . '/eaccounting';
			$pathdata['subdir'] = '/eaccounting';
		} else {
			$new_subdir = '/eaccounting' . $pathdata['subdir'];

			$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
			$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
			$pathdata['subdir'] = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['subdir'] );
		}
	}

	return $pathdata;
}

add_filter( 'upload_dir', 'eaccounting_handle_upload_folder' );


function eaccounting_handle_assets_name( $full_filename, $ext, $dir ) {
	if ( ! isset( $_POST['type'] ) || ! 'eaccounting_file' === $_POST['type'] ) { // WPCS: CSRF ok, input var ok.
		return $full_filename;
	}

	if ( ! strpos( $dir, 'eaccounting_uploads' ) ) {
		return $full_filename;
	}

	$ideal_random_char_length = 6;   // Not going with a larger length because then downloaded filename will not be pretty.
	$max_filename_length      = 255; // Max file name length for most file systems.
	$length_to_prepend        = min( $ideal_random_char_length, $max_filename_length - strlen( $full_filename ) - 1 );

	if ( 1 > $length_to_prepend ) {
		return $full_filename;
	}

	$suffix   = strtolower( wp_generate_password( $length_to_prepend, false, false ) );
	$filename = $full_filename;

	if ( strlen( $ext ) > 0 ) {
		$filename = substr( $filename, 0, strlen( $filename ) - strlen( $ext ) );
	}

	$full_filename = str_replace(
		$filename,
		"$filename-$suffix",
		$full_filename
	);

	return $full_filename;
}

add_filter( 'wp_unique_filename', 'eaccounting_handle_assets_name', 10, 3 );

