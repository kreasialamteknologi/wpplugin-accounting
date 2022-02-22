<?php
/**
 * Handles License for the plugin.
 *
 * @package        EverAccounting
 * @class          License
 * @version        1.0.2
 */

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

class License {
	/**
	 * Holds the update url.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	private $api_url = 'https://wpeveraccounting.com/';

	/**
	 * Plugin file.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	private $file = '';

	/**
	 * Item name.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private $item_name;

	/**
	 * Version number.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	private $version = '';

	/**
	 * Holds the short name.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	private $short_name = '';

	/**
	 * License key.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	private $license_key = '';

	/**
	 * License status.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	private $license_status = '';

	/**
	 * @since 1.1.0
	 * @var string
	 */
	private $cache_key;

	/**
	 * Plugin data.
	 *
	 * @var array
	 */
	private $plugin_data;

	/**
	 * License constructor.
	 *
	 * @param $file
	 * @param $item_name
	 */
	public function __construct( $file, $item_name ) {
		// bail out if it's a local server
		if ( $this->is_local_server() ) {
			return;
		}

		$plugin_data          = get_file_data(
			$file,
			array(
				'name'    => 'Plugin Name',
				'version' => 'Version',
				'author'  => 'Author',
			),
			'plugin'
		);
		$this->file           = $file;
		$this->item_name      = $item_name;
		$this->version        = $plugin_data['version'];
		$short_name           = basename( $file, '.php' );
		$short_name           = preg_replace( '/[^a-zA-Z0-9\s]/', '', $short_name );
		$short_name           = str_replace( 'wp_ever_accounting', '', $short_name );
		$short_name           = str_replace( 'ever_accounting', '', $short_name );
		$short_name           = str_replace( 'eaccounting', '', $short_name );
		$short_name           = str_replace( 'eaccounting_', '', $short_name );
		$this->short_name     = "eaccounting_{$short_name}";
		$this->license_key    = trim( eaccounting_get_option( $this->short_name . '_license_key' ) );
		$this->license_status = get_option( $this->short_name . '_license_status' );
		$this->cache_key      = 'eccounting_' . md5( serialize( $plugin_data ) );
		$this->plugin_data    = $plugin_data;

		$this->init();
	}

	/**
	 * Init Method
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function init() {
		// Register settings
		add_filter( 'eaccounting_settings_licenses', array( $this, 'settings' ), 1 );
		add_action( 'admin_notices', array( $this, 'activation_notice' ) );
		add_action( 'eaccounting_weekly_scheduled_events', array( $this, 'scheduled_license_check' ) );
		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );
		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

		require_once EACCOUNTING_ABSPATH . '/includes/libraries/EDD_SL_Plugin_Updater.php';
		// Setup the updater
		$edd_updater = new \EDD_SL_Plugin_Updater(
			$this->api_url,
			$this->file,
			[
				'version'   => $this->version,
				'license'   => $this->license_key,
				'author'    => $this->plugin_data['author'],
				'item_name' => $this->item_name,
				'url'       => home_url(),
			]
		);
	}


	/**
	 * Check if the current server is localhost
	 *
	 * @return bool
	 */
	private function is_local_server() {
		$addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

		$is_local = ( in_array( $addr, [ '127.0.0.1', '::1' ] ) || substr( $host, - 4 ) == '.dev' );

		return apply_filters( 'eaccounting_license_is_local_server', $is_local );
	}

	/**
	 * Add license field to settings
	 *
	 * @param array $settings
	 *
	 * @return  array
	 */
	public function settings( $fields ) {
		$fields[] = 	array(
			'id'             => $this->short_name . '_license_key',
			'title'           => sprintf( __( '%1$s', 'wp-ever-accounting' ), $this->plugin_data['name'] ),
			'license_status' => $this->license_status,
			'desc'           => '',
			'type'           => 'license',
			'size'           => 'regular',
		);
		return $fields;
	}

	/**
	 * Show license activation notice
	 *
	 * @return void
	 */
	public function activation_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $this->license_key ) || ( is_object( $this->license_status ) && 'valid' !== $this->license_status->license ) ) {
			$notice = sprintf(__('You are not receiving critical updates and new features for %s. ', 'wp-ever-accounting'), '<strong>'.$this->plugin_data['name'].'</strong>');
			$notice .= sprintf(__('Please <a href="%s">activate your license</a> to receive updates and priority support', 'wp-ever-accounting'), admin_url('admin.php?page=ea-settings&tab=licenses'));
			echo wp_kses_post( '<div class="error"><p>'.$notice.'</p></div>' );
		}
	}

	/**
	 * Check license status periodically every week.
	 * @return void
	 */
	public function scheduled_license_check() {
		if ( empty( $this->license_key ) ) {
			return;
		}

		$response = $this->remote_request(
			'activate_license',
			array(
				'license'   => $this->license_key,
				'item_name' => $this->item_name,
			)
		);
		if ( ! $response ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );
		update_option( $this->short_name . '_license_status', $response );
	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @since   1.1.0
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {
		if ( ( ! is_object( $this->license_status ) || 'valid' !== $this->license_status->license ) ) {
			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=ea-settings&tab=licenses' ) ) . '">' . __( 'Enter valid license key for automatic updates.', 'wp-ever-accounting' ) . '</a></strong>';
		}

	}

	/**
	 * Activate the license key
	 *
	 * @return  void
	 */
	public function activate_license() {
		if ( ! isset( $_POST['eaccounting_settings'] ) ) {
			return;
		}
		if ( ! isset( $_REQUEST[ $this->short_name . '_license_key-nonce' ] ) || ! wp_verify_nonce( $_REQUEST[ $this->short_name . '_license_key-nonce' ], $this->short_name . '_license_key-nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_POST['eaccounting_settings'][ $this->short_name . '_license_key' ] ) ) {
			delete_option( $this->short_name . '_license_status' );

			return;
		}

		foreach ( $_POST as $key => $value ) {
			if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}
		$details = get_option( $this->short_name . '_license_active' );

		if ( is_object( $details ) && 'valid' === $details->license ) {
			return;
		}

		$license = sanitize_text_field( $_POST['eaccounting_settings'][ $this->short_name . '_license_key' ] );

		if ( empty( $license ) ) {
			return;
		}

		$response = $this->remote_request(
			'activate_license',
			array(
				'license' => $license,
				'item_name' => $this->item_name,
			)
		);
		if ( ! $response ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );
		update_option( $this->short_name . '_license_status', $response );
	}


	/**
	 * Deactivate the license key
	 *
	 * @return  void
	 */
	public function deactivate_license() {
		if ( ! isset( $_POST['eaccounting_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['eaccounting_settings'][ $this->short_name . '_license_key' ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST[ $this->short_name . '_license_key-nonce' ], $this->short_name . '_license_key-nonce' ) ) {

			wp_die( __( 'Nonce verification failed', 'wp-ever-accounting' ), __( 'Error', 'wp-ever-accounting' ), array( 'response' => 403 ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Run on deactivate button press
		if ( isset( $_POST[ $this->short_name . '_license_key_deactivate' ] ) ) {

			$response = $this->remote_request(
				'deactivate_license',
				array(
					'license' => $this->license_key,
					'item_name' => $this->item_name,
				)
			);
			if ( ! $response ) {
				return;
			}

			// Tell WordPress to look for updates
			delete_option( $this->short_name . '_license_status' );
		}
	}


	/**
	 * Queries the remote URL via wp_remote_post and returns a json decoded response.
	 *
	 * @param string $action The name of the $_POST action var.
	 * @param array $body The content to retrieve from the remote URL.
	 *
	 * @since 1.1.0
	 *
	 * @return string|bool          Json decoded response on success, false on failure.
	 */
	public function remote_request( $action = 'get_version', $body = array() ) {
		$verify_ssl = $this->verify_ssl();
		$api_params = array(
			'edd_action' => $action,
			'license'    => ! empty( $body['license'] ) ? $body['license'] : '',
			'item_name'  => isset( $body['item_name'] ) ? $body['item_name'] : false,
			'item_id'    => isset( $body['item_id'] ) ? $body['item_id'] : false,
			'version'    => isset( $body['version'] ) ? $body['version'] : false,
			'slug'       => isset( $body['slug'] ) ? $body['slug'] : '',
			'url'        => home_url(),
			'beta'       => ! empty( $body['beta'] ),
		);

		$request = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => $verify_ssl,
				'body'      => $api_params,
			)
		);

		// Bail out early if there are any errors.
		if ( 200 !== wp_remote_retrieve_response_code( $request ) || is_wp_error( $request ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		return $response;
	}

	/**
	 * Filter for vertify SSL.
	 *
	 * @since 1.1.0
	 */
	public function verify_ssl() {
		return (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this );
	}
}
