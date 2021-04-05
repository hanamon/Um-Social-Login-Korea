<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param $notification_tpl
 * @param $hook
 * @param $hook_data
 * @param $um_hooks
 *
 * @return mixed
 */
function um_social_login_mycred_notification_tpl_default( $notification_tpl, $hook, $hook_data, $um_hooks ) {
	$networks = UM()->Social_Login_API()->available_networks();

	if ( ! empty( $um_hooks[ $hook ]['deduct'] ) ) {
		if ( ! empty( $networks[ $hook ]['deduct_notification_tpl'] ) ) {
			$notification_tpl = $networks[ $hook ]['deduct_notification_tpl'];
		}
	} else {
		if ( ! empty( $networks[ $hook ]['notification_tpl'] ) ) {
			$notification_tpl = $networks[ $hook ]['notification_tpl'];
		}
	}

	return $notification_tpl;
}
add_filter( 'um_mycred_notification_tpl_default', 'um_social_login_mycred_notification_tpl_default', 10, 4 );