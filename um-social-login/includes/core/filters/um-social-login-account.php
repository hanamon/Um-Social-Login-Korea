<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Custom error
 *
 * @param $msg
 * @param $err_t
 *
 * @return string
 */
function um_social_login_custom_error( $msg, $err_t ) {
	$providers = UM()->Social_Login_API()->available_networks();

	foreach ( $providers as $key => $info ) {
		if ( strstr( $err_t, $key ) && $err_t == $key . '_exist' ) {
			$msg = sprintf( __(' This %s account is already linked to another user.', 'um-social-login' ), $info['name'] );
		}
	}

	return $msg;
}
add_filter( 'um_custom_error_message_handler', 'um_social_login_custom_error', 10, 2 );


/**
 * Sync user profile photo
 *
 * @param $url
 * @param $user_id
 *
 * @return mixed
 */
function um_social_login_synced_profile_photo( $url, $user_id ) {
	if ( $url2 = get_user_meta( $user_id, 'synced_profile_photo', true ) ) {
		$url = $url2;
		// ssl enabled?
		if ( is_ssl() && ! strstr( $url, 'vk.me' ) ) {
			$url = str_replace( 'http://','https://', $url );
		}
	}
	return $url;
}
add_filter( 'um_user_avatar_url_filter', 'um_social_login_synced_profile_photo', 100, 2 );


/**
 * Add tab to account page
 *
 * @param array $tabs
 *
 * @return array
 */
function um_social_login_account_tab( $tabs ) {
	$tabs[450]['social']['icon'] = 'um-faicon-sign-in';
	$tabs[450]['social']['title'] = __('Social Connect','um-social-login');
	$tabs[450]['social']['show_button'] = false;

	return $tabs;
}
add_filter( 'um_account_page_default_tabs_hook', 'um_social_login_account_tab', 100 );


/**
 * Add content to account tab
 *
 * @param $output
 *
 * @return string
 */
function um_account_content_hook_social( $output ) {
	// important to only show available networks
	$providers = UM()->Social_Login_API()->available_networks();

	if ( empty( $providers ) ) {
		return $output;
	}

	wp_enqueue_script( 'um-social-login' );
	wp_enqueue_style( 'um-social-login' );

	$user_id = get_current_user_id();

	ob_start(); ?>

	<div class="um-field" data-key="">
	
		<?php foreach( $providers as $provider => $array ) { ?>
			
			<div class="um-provider">

				<div class="um-provider-title">
					<?php printf( __( 'Connect to %s', 'um-social-login' ), $array['name'] ); ?>
					<?php do_action( 'um_social_login_after_provider_title', $provider, $array ); ?>
				</div>

				<div class="um-provider-conn">

					<?php if ( UM()->Social_Login_API()->user_connect()->is_connected( $user_id, $provider ) ) { ?>

						<div class="um-provider-user-photo"><a href="<?php echo esc_url( UM()->Social_Login_API()->user( $user_id )->getProfileUrl( $provider ) ); ?>" target="_blank" title="<?php echo esc_attr( UM()->Social_Login_API()->user( $user_id )->getDisplayName( $provider ) ); ?>"><img src="<?php echo esc_url( UM()->Social_Login_API()->user( $user_id )->getProfilePhoto( $provider ) ); ?>" class="um-provider-photo small" /></a></div>

						<div class="um-provider-user-handle"><a href="<?php echo esc_url( UM()->Social_Login_API()->user( $user_id )->getProfileUrl( $provider ) ); ?>" target="_blank"><?php echo UM()->Social_Login_API()->user( $user_id )->getDisplayName( $provider ); ?></a></div>

						<div class="um-provider-disconnect">(<a href="<?php echo esc_url( UM()->Social_Login_API()->hybridauth()->getDisconnectUrl( $provider ) ); ?>"><?php _e( 'Disconnect', 'um-social-login' ) ?></a>)</div>

						<span class="um-provider-date-linked">
							<?php $date_linked = UM()->Social_Login_API()->user( $user_id )->getDateLinked( $provider ); ?>
							<?php echo human_time_diff( strtotime( $date_linked ) );?> <?php _e("ago","um-social-login");?>
						</span>

					<?php } else { ?>

						<a title="<?php printf( esc_attr__('Connect to %s','um-social-login'), $array['name'] ); ?>" href="<?php echo esc_url( UM()->Social_Login_API()->hybridauth()->getConnectUrl( $provider ) ); ?>" class="um-social-btn um-social-btn-<?php echo esc_attr( $provider ); ?>" onclick="um_social_login_oauth_window( this.href,'authWindow', 'width=600,height=600,scrollbars=yes' );return false;">
							<i class="<?php echo esc_attr( $array['icon'] ); ?>"></i>
							<span> <?php printf( __( 'Connect to %s', 'um-social-login' ), $array['name'] ); ?></span>
						</a>

					<?php } ?>

				</div>

				<div class="um-clear"></div>

			</div>
			
		<?php } ?>

		<style type="text/css">
			<?php foreach ( $providers as $provider => $arr ) { ?>
				.um-social-btn.um-social-btn-<?php echo esc_attr( $provider ); ?> {background-color: <?php echo esc_attr( $arr['bg'] ); ?>!important}
				.um-social-btn.um-social-btn-<?php echo esc_attr( $provider ); ?>:hover {background-color: <?php echo esc_attr( $arr['bg_hover'] ); ?>!important}
				.um-social-btn.um-social-btn-<?php echo esc_attr( $provider ); ?> {color: <?php echo esc_attr( $arr['color'] ); ?>!important}
			<?php } ?>
		</style>

	</div>

	<?php $output .= ob_get_clean();
	return $output;
}
add_filter( 'um_account_content_hook_social', 'um_account_content_hook_social' );