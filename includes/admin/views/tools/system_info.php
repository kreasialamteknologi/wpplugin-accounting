<?php
/**
 * Admin System Info Page.
 * Page: Tools
 * Tab: System Info
 *
 *
 * @package     EverAccounting
 * @subpackage  Admin/View/Tools
 * @since       1.0.2
 *
 */

defined( 'ABSPATH' ) || exit();

if ( ! current_user_can( 'manage_eaccounting' ) ) {
	return;
}

$action_url = eaccounting_admin_url( array( 'tab' => 'system_info' ) );
?>
	<form action="<?php echo esc_url( $action_url ); ?>" method="post" dir="ltr">
		<textarea readonly="readonly"
				  onclick="this.focus(); this.select()"
				  id="ea-system-info-textarea"
				  name="ea-sysinfo"
				  title="<?php esc_attr_e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'wp-ever-accounting' ); ?>">
			<?php echo eaccounting_tools_system_info_report(); ?>
		</textarea>
	</form>
<?php
