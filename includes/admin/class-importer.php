<?php
/**
 * Handle import
 *
 * @package     EverAccounting
 * @subpackage  Admin
 * @version     1.0.2
 */

namespace EverAccounting\Admin;

use EverAccounting\Ajax;

defined( 'ABSPATH' ) || exit();

/**
 * Class Importer
 * @package EverAccounting/Admin
 */
class Importer {
	/**
	 * Importer constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_eaccounting_do_ajax_import', array( __CLASS__, 'do_ajax_import' ) );
	}

	/**
	 * Run the ajax import process
	 *
	 * @since 1.0.2
	 */
	public static function do_ajax_import() {
		if ( ! isset( $_REQUEST['type'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Import type must be present.', 'wp-ever-accounting' ),
				)
			);
		}
		$params = array(
			'delimiter'       => ! empty( $_REQUEST['delimiter'] ) ? eaccounting_clean( wp_unslash( $_REQUEST['delimiter'] ) ) : ',',
			'position'        => isset( $_REQUEST['position'] ) ? absint( $_REQUEST['position'] ) : 0,
			'mapping'         => isset( $_REQUEST['mapping'] ) ? (array) wp_unslash( $_REQUEST['mapping'] ) : array(),
			'update_existing' => isset( $_REQUEST['update_existing'] ) ? (bool) $_REQUEST['update_existing'] : false,
			'limit'           => apply_filters( 'eaccounting_import_batch_size', 30 ),
			'parse'           => true,
		);

		$step = isset( $_REQUEST['step'] ) ? eaccounting_clean( $_REQUEST['step'] ) : '';
		$type = sanitize_key( $_REQUEST['type'] );
		$file = ! empty( $_REQUEST['file'] ) ? eaccounting_clean( wp_unslash( $_REQUEST['file'] ) ) : '';

		// verify nonce
		Ajax::verify_nonce( "{$type}_importer_nonce" );

		if ( empty( $type ) || false === $batch = eaccounting()->utils->batch->get( $type ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf( __( '%s is an invalid import type.', 'wp-ever-accounting' ), esc_html( $type ) ),
				)
			);
		}

		$class      = isset( $batch['class'] ) ? $batch['class'] : '';
		$class_file = isset( $batch['file'] ) ? $batch['file'] : '';

		if ( empty( $class_file ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf( __( 'An invalid file path is registered for the %1$s handler.', 'wp-ever-accounting' ), "<code>{$type}</code>" ),
				)
			);
		}

		require_once $class_file;

		if ( empty( $class ) || ! class_exists( $class ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						__( '%1$s is an invalid importer handler for the %2$s . Please try again.', 'wp-ever-accounting' ),
						"<code>{$class}</code>",
						"<code>{$type}</code>"
					),
				)
			);
		}

		if ( empty( $file ) && empty( $_FILES['upload'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing import file. Please provide an import file.', 'wp-ever-accounting' ),
					'request' => $_REQUEST,
				)
			);
		}

		if ( ! empty( $_FILES['upload'] ) ) {
			$accepted_mime_types = array(
				'text/csv',
				'text/comma-separated-values',
				'text/plain',
				'text/anytext',
				'text/*',
				'text/plain',
				'text/anytext',
				'text/*',
				'application/csv',
				'application/excel',
				'application/vnd.ms-excel',
				'application/vnd.msexcel',
			);

			if ( empty( $_FILES['upload']['type'] ) || ! in_array( strtolower( $_FILES['upload']['type'] ), $accepted_mime_types ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'The file you uploaded does not appear to be a CSV file.', 'wp-ever-accounting' ),
						'request' => $_REQUEST,
					)
				);
			}

			if ( ! file_exists( $_FILES['upload']['tmp_name'] ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Something went wrong during the upload process, please try again.', 'wp-ever-accounting' ),
						'error'   => $_FILES,
					)
				);
			}

			// Let WordPress import the file. We will remove it after import is complete
			$import_file = wp_handle_upload( $_FILES['upload'], array( 'test_form' => false ) );
			if ( ! empty( $import_file['error'] ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Something went wrong during the upload process, please try again.', 'wp-ever-accounting' ),
						'error'   => $import_file,
					)
				);
			}

			$file = $import_file['file'];
		}

		if ( empty( $file ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing import file. Please provide an import file.', 'wp-ever-accounting' ),
					'request' => $_REQUEST,
				)
			);
		}

		$importer = new $class( $file, $params );
		if ( ! $importer->can_import() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to import data', 'wp-ever-accounting' ) ) );
		}

		$headers = $importer->get_raw_keys();
		$sample  = current( $importer->get_raw_data() );

		if ( empty( $sample ) ) {
			wp_send_json_error( array( 'message' => __( 'The file is empty or using a different encoding than UTF-8, please try again with a new file.', 'wp-ever-accounting' ) ) );
		}

		if ( $step == 'upload' ) {
			wp_send_json_success(
				array(
					'position' => 0,
					'headers'  => $headers,
					'required' => $importer->get_required(),
					'sample'   => $sample,
					'step'     => $step,
					'file'     => $file,
				)
			);
		}

		// Log failures.
		if ( $params['position'] > 0 ) {
			$imported = (int) get_user_option( "{$type}_import_log_imported" );
			$skipped  = (int) get_user_option( "{$type}_import_log_skipped" );
		} else {
			$skipped  = 0;
			$imported = 0;
		}

		$results          = $importer->import();
		$percent_complete = $importer->get_percent_complete();
		$skipped         += (int) $results['skipped'];
		$imported        += (int) $results['imported'];

		update_user_option( get_current_user_id(), "{$type}_import_log_imported", $imported );
		update_user_option( get_current_user_id(), "{$type}_import_log_skipped", $skipped );

		if ( 100 <= $percent_complete ) {
			delete_user_option( get_current_user_id(), "{$type}_import_log_imported" );
			delete_user_option( get_current_user_id(), "{$type}_import_log_skipped" );
			wp_send_json_success(
				array(
					'position'   => 'done',
					'percentage' => 100,
					'imported'   => (int) $imported,
					'skipped'    => (int) $skipped,
					'file'       => $file,
					'message'    => esc_html__( sprintf( '%d items imported and %d items skipped.', $imported, $skipped ), 'wp-ever-accounting' ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'position'   => $importer->get_position(),
					'percentage' => $percent_complete,
					'imported'   => (int) $imported,
					'skipped'    => (int) $skipped,
					'file'       => $file,
					'step'       => 'import',
					'mapping'    => $params['mapping'],
				)
			);
		}
		exit();
	}
}

return new Importer();
