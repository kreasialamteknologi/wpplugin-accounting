<?php
/**
 * EverAccounting  Rewrites Event Handlers.
 *
 * @since       1.1.0
 * @package     EverAccounting
 * @class       Rewrites
 */

namespace EverAccounting;

defined( 'ABSPATH' ) || exit();

class Rewrites {

	/**
	 * EverAccounting_Rewrites constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'register_query_var' ) );
		add_action( 'template_redirect', array( $this, 'rewrite_templates' ) );
	}

	/**
	 * Add the required rewrite rules
	 *
	 * @return void
	 */
	function add_rewrite_rules() {
		$eaccounting_slug = eaccounting_get_parmalink_base();
		add_rewrite_rule( '^' . $eaccounting_slug . '/invoice/([0-9]{1,})/(.*)?/?$', 'index.php?eaccounting=true&ea_page=invoice&id=$matches[1]&key=$matches[2]', 'top' );
		add_rewrite_rule( '^' . $eaccounting_slug . '/bill/([0-9]{1,})/(.*)?/?$', 'index.php?eaccounting=true&ea_page=bill&id=$matches[1]&key=$matches[2]', 'top' );
//		add_rewrite_rule( '^' . $eaccounting_slug . '/?$', 'index.php?eaccounting=true&ea_page=dashboard', 'top' );
//		add_rewrite_rule( '^' . $eaccounting_slug . '/([a-z]+?)/?$', 'index.php?eaccounting=true&ea_page=$matches[1]&page=index', 'top' );
//		add_rewrite_rule( '^' . $eaccounting_slug . '/([a-z]+?)/page/([0-9]{1,5})?/?$', 'index.php?eaccounting=true&ea_page=$matches[1]&page=index&page=$matches[2]', 'top' );
//		add_rewrite_rule( '^' . $eaccounting_slug . '/([a-z]+?)?/([0-9]{1,5})?/?$', 'index.php?eaccounting=true&ea_page=$matches[1]&id=$matches[2]&page=single', 'top' );
//		add_rewrite_rule( '^' . $eaccounting_slug . '/([a-z]+?)?/([0-9]{1,5})?/(.*)?/?$', 'index.php?eaccounting=true&ea_page=$matches[1]&id=$matches[1]&key=$matches[2]&page=single', 'top' );
	}

	/**
	 * Register our query vars
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	function register_query_var( $vars ) {
		$vars[] = 'eaccounting';
		$vars[] = 'ea_page';
		$vars[] = 'id';
		$vars[] = 'key';

		return $vars;
	}

	/**
	 * Load our template on our rewrite rule
	 *
	 * @return void
	 */
	public function rewrite_templates() {
		if ( 'true' === get_query_var( 'eaccounting' ) ) {
			eaccounting_get_template( 'eaccounting.php' );
			exit();
		}
	}

}

new Rewrites();
