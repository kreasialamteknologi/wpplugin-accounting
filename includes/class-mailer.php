<?php

namespace EverAccounting;

defined('ABSPATH') || exit;

class Mailer {
	/**
	 * Holds the from address
	 *
	 * @since 1.1.0
	 */
	private $mail_from;

	/**
	 * Holds the from name
	 *
	 * @since 1.1.0
	 */
	private $from_name;

	/**
	 * Holds the email content type
	 *
	 * @since 1.1.0
	 */
	private $content_type;

	/**
	 * Holds the email headers
	 *
	 * @since 1.1.0
	 */
	private $headers;

	/**
	 * Whether to send email in HTML
	 *
	 * @since 1.1.0
	 */
	private $html = true;

	/**
	 * The header text for the email
	 *
	 * @since  1.1.0
	 */
	private $heading = '';

	/**
	 * Strings to find/replace in subjects/headings.
	 *
	 * @var array
	 */
	private $placeholders = array();

	/**
	 * Set a property
	 *
	 * @since 1.1.0
	 *
	 * @param $key
	 * @param $value
	 */
	public function set_prop( $key, $value ) {
		$this->$key = $value;

		return $this;
	}

	/**
	 * Add new placeholders.
	 *
	 * @since 1.1.0
	 *
	 * @param $placeholders
	 *
	 */
	public function add_placeholders( $placeholders ) {
		$this->placeholders = array_merge( $this->placeholders, $placeholders );
		return $this;
	}

	/**
	 * Get the email from address
	 *
	 * @since 2.1
	 */
	public function get_mail_from() {
		if ( ! $this->mail_from ) {
			$this->mail_from = eaccounting()->settings->get( 'mail_from' );
		}

		if ( empty( $this->mail_from ) || ! is_email( $this->mail_from ) ) {
			$this->mail_from = get_option( 'admin_email' );
		}

		return apply_filters( 'eaccounting_email_mail_from', $this->mail_from, $this );
	}

	/**
	 * Get the email from name
	 *
	 * @since 1.1.0
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = eaccounting()->settings->get( 'email_from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'eaccounting_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}

	/**
	 * Get the email content type
	 *
	 * @since 1.1.0
	 */
	public function get_content_type() {
		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'eaccounting_email_default_content_type', 'text/html', $this );
		} elseif ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'eaccounting_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get the email headers
	 *
	 * @since 1.1.0
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers  = "From: {$this->get_from_name()} <{$this->get_mail_from()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_mail_from()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'eaccounting_email_headers', $this->headers, $this );
	}

	/**
	 * Get the header text for the email
	 *
	 * @since 1.1.0
	 */
	public function get_heading() {
		return apply_filters( 'eaccounting_email_heading', $this->heading );
	}


	/**
	 * Replace placeholder text in strings.
	 *
	 * @since  1.0.2
	 *
	 * @param string $string Email footer text.
	 *
	 * @return string         Email footer text with any replacements done.
	 */
	public function replace_placeholders( $string ) {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );

		$placeholders = array_merge(
			$this->placeholders,
			array(
				'{site_title}'     => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
				'{site_address}'   => $domain,
				'{site_url}'       => $domain,
				'{everaccounting}' => '<a href="https://wpeveraccounting.com">EverAccounting</a>',
				'{eaccounting}'    => '<a href="https://wpeveraccounting.com">EverAccounting</a>',
				'{EverAccounting}' => '<a href="https://wpeveraccounting.com">EverAccounting</a>',
			)
		);

		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $string );
	}

	/**
	 * Apply inline styles to dynamic content.
	 *
	 * We only inline CSS for html emails, and to do so we use Emogrifier library (if supported).
	 *
	 * @version 4.0.0
	 *
	 * @param string|null $content Content that will receive inline styles.
	 *
	 * @return string
	 *
	 */
	public function style_inline( $content ) {
		ob_start();
		eaccounting_get_template( 'emails/email-styles.php' );
		$css     = apply_filters( 'eaccounting_email_styles', ob_get_clean(), $this );
		$content = '<style type="text/css">' . $css . '</style>' . $content;

		return $content;
	}

	/**
	 * Build the final email
	 *
	 * @since 2.1
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	public function build_email( $message ) {
		if ( false === $this->html ) {
			return wp_strip_all_tags( $message );
		}

		ob_start();

		eaccounting_get_template( 'emails/email-header.php', array( 'email_heading' => $this->get_heading() ) );

		echo wpautop( wptexturize( make_clickable( $message ) ) );

		eaccounting_get_template( 'emails/email-footer.php' );

		$body = ob_get_clean();

		return $this->style_inline( $this->replace_placeholders( $body ) );
	}

	/**
	 * Send an email.
	 *
	 * @since 1.0.2
	 *
	 * @param string $to          Email to.
	 * @param string $subject     Email subject.
	 * @param string $message     Email message.
	 * @param array  $attachments Attachments (default: "").
	 *
	 * @return bool success
	 */
	public function send( $to, $subject, $message, $attachments = array() ) {
		add_filter( 'wp_mail_from', array( $this, 'get_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$recipients = array_map( 'trim', explode( ',', $to ) );
		$recipients = array_filter( $recipients, 'is_email' );
		$recipients = implode( ', ', $recipients );

		$headers = $this->get_headers();
		$subject = $this->replace_placeholders( $subject );
		$message = apply_filters( 'eaccounting_mail_content', $this->build_email( $message ) );

		$return = wp_mail( $recipients, $subject, $message, $headers, $attachments );

		remove_filter( 'wp_mail_from', array( $this, 'get_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		return $return;
	}
}