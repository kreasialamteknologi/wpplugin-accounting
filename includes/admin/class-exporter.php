<?php
/**
 * Handle export
 *
 * @package     EverAccounting
 * @subpackage  Admin
 * @version     1.0.2
 */

namespace EverAccounting\Admin;

use EverAccounting\Ajax;

defined( 'ABSPATH' ) || exit();

/**
 * Class Exporter
 * @package EverAccounting/Admin
*/
class Exporter {

	/**
	 * Exporter constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_eaccounting_do_ajax_export', array( __CLASS__, 'do_ajax_export' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_csv_download' ) );
	}

	/**
	 * Run the ajax export process
	 *
	 * @since 1.0.2
	 */
	public static function do_ajax_export() {
		if ( ! isset( $_REQUEST['type'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Export type must be present.', 'wp-ever-accounting' ),
				)
			);
		}

		$type = sanitize_key( $_REQUEST['type'] );

		Ajax::verify_nonce( "{$type}_exporter_nonce" );

		if ( empty( $type ) || false === $batch = eaccounting()->utils->batch->get( $type ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf( __( '%s is an invalid export type.', 'wp-ever-accounting' ), esc_html( $type ) ),
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
		} else {
			require_once $class_file;
		}

		if ( empty( $class ) || ! class_exists( $class ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						__( '%1$s is an invalid exporter handler for the %2$s . Please try again.', 'wp-ever-accounting' ),
						"<code>{$class}</code>",
						"<code>{$type}</code>"
					),
				)
			);
		}

		/**
		 * @var $exporter \EverAccounting\Abstracts\CSV_Exporter
		 */
		$exporter = new $class();

		if ( ! $exporter->can_export() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have enough privileges to export this.', 'wp-ever-accounting' ),
				)
			);
		}

		$step = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;

		$exporter->process_step( $step );

		$query_args = apply_filters(
			'eaccounting_export_get_ajax_query_args',
			array(
				'nonce'    => wp_create_nonce( 'ea-download-file' ),
				'action'   => 'eaccounting_download_export_file',
				'filename' => $exporter->get_filename(),
				'page'     => 'ea-tools',
				'export'   => $type,
				'tab'      => 'export',
			)
		);

		if ( 100 <= $exporter->get_percent_complete() ) {
			$total = $exporter->get_total_exported();
			wp_send_json_success(
				array(
					'step'       => 'done',
					'percentage' => 100,
					'message'    => sprintf( __( 'Total %d items exported', 'wp-ever-accounting' ), $total ),
					'url'        => add_query_arg(
						$query_args,
						eaccounting_admin_url(
							array(
								'page' => 'ea-tools',
								'tab'  => 'export',
							)
						)
					),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'step'       => ++ $step,
					'percentage' => $exporter->get_percent_complete(),
				)
			);
		}

	}

	/**
	 * Handle CSV file download.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public static function handle_csv_download() {
		if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['nonce'] ), 'ea-download-file' ) && 'eaccounting_download_export_file' === wp_unslash( $_GET['action'] ) ) {

			if ( empty( $_REQUEST['export'] ) || false === $batch = eaccounting()->utils->batch->get( $_REQUEST['export'] ) ) {
				wp_die(
					__( 'Invalid export type.', 'wp-ever-accounting' ),
					__( 'Error', 'wp-ever-accounting' ),
					array( 'response' => 403 )
				);
			}

			require_once $batch['file'];

			if ( empty( $batch['class'] ) || ( ! empty( $batch['class'] ) && ! class_exists( $batch['class'] ) ) ) {
				wp_die(
					__( 'Invalid export class.', 'wp-ever-accounting' ),
					__( 'Error', 'wp-ever-accounting' ),
					array( 'response' => 403 )
				);
			}

			$class = $batch['class'];
			/**
			 * @var $class \EverAccounting\Abstracts\CSV_Exporter
			 */
			$exporter = new $class();

			if ( ! $exporter->can_export() ) {
				wp_die(
					__( 'You do not have enough privileges to export this.', 'wp-ever-accounting' ),
					__( 'Error', 'wp-ever-accounting' ),
					array( 'response' => 403 )
				);
			}
			if ( ! empty( $_GET['filename'] ) ) {
				$exporter->set_filename( wp_unslash( $_GET['filename'] ) );
			}

			$exporter->export();
		}
	}
}

return new Exporter();
