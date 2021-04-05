<?php if ( ! defined( 'ABSPATH' ) ) exit;

use Hybridauth\Storage\Session;
	

/**
 * Save extra fields
 *
 * @param $user_id
 * @param $args
 */
function um_social_login_save_extra_fields( $user_id, $args ) {

	if ( ! isset( $_REQUEST['return_provider'] ) ) {
		return;
	}

	$provider = sanitize_key( $_REQUEST['return_provider'] );

	$oAuthResponse = UM()->Social_Login_API()->hybridauth()->connectUser( $provider );

	if ( isset( $oAuthResponse['has_errors'] ) ) {
		return;
	}

	um_fetch_user( $user_id );
	
	do_action( "um_social_do_link_user", $provider, $oAuthResponse['userProfile'], '', false );

}
add_action( 'um_registration_set_extra_data', 'um_social_login_save_extra_fields', 9, 2 );


/**
 * Modal field settings
 *
 * @param $args
 */
function um_social_login_add_buttons( $args ) {

	wp_enqueue_script( 'um-social-login' );
	wp_enqueue_style( 'um-social-login' );

	if ( isset( UM()->Social_Login_API()->profile ) ) {
		return;
	}

	$show_social = ( isset( $args['show_social'] ) ) ? $args['show_social'] : '-1';

	if ( ! $show_social ) {
		return;
	}

	if ( $args['mode'] == 'register' && ! UM()->options()->get('register_show_social') ) {
		return;
	}

	if ( $args['mode'] == 'login' && ! UM()->options()->get('login_show_social') ) {
		return;
	}

	$networks = UM()->Social_Login_API()->available_networks();

	if ( ! $networks ) {
		return;
	}

	$o_networks = $networks; ?>

	<div class="um-field">
		<div class="um-col-alt">
			<?php $i = 0;
			foreach ( $o_networks as $id => $arr ) {
				$i++;
				$class = ( $i % 2 == 0 ) ? 'um-right' : 'um-left'; ?>

				<div class="<?php echo esc_attr( $class ); ?> um-half">
					<a href="<?php echo esc_attr( UM()->Social_Login_API()->hybridauth()->getConnectUrl( $id ) ); ?>" title="<?php echo esc_attr( $arr['button'] ); ?>"
					   data-redirect-url="<?php echo esc_attr( UM()->Social_Login_API()->hybridauth()->getConnectUrl( $id ) ); ?>" class="um-button um-alt um-button-social um-button-<?php echo $id; ?>" onclick="um_social_login_oauth_window( this.href,'authWindow', 'width=600,height=600,scrollbars=yes' );return false;">
						<i class="<?php echo esc_attr( $arr['icon'] ); ?>"></i>
						<span><?php echo $arr['button']; ?></span>
					</a>
				</div>

				<?php if ( $i % 2 == 0 && count( $o_networks ) != $i ) { ?>
					<div class="um-clear"></div>
		</div>
		<div class="um-col-alt um-col-alt-s">
				<?php }

			} ?>

			<div class="um-clear"></div>
		</div>
	</div>

	<style type="text/css">

		.um-<?php echo esc_attr( $args['form_id'] ); ?>.um a.um-button.um-button-social {
			padding-left: 5px !important;
			padding-right: 5px !important;
		}

		<?php foreach( $o_networks as $id => $arr ) { ?>
			.um-<?php echo esc_attr( $args['form_id'] ); ?>.um a.um-button.um-button-<?php echo esc_attr( $id ); ?> {background-color: <?php echo esc_attr( $arr['bg'] ); ?>!important}
			.um-<?php echo esc_attr( $args['form_id'] ); ?>.um a.um-button.um-button-<?php echo esc_attr( $id ); ?>:hover {background-color: <?php echo esc_attr( $arr['bg_hover'] ); ?>!important}
			.um-<?php echo esc_attr( $args['form_id'] ); ?>.um a.um-button.um-button-<?php echo esc_attr( $id ); ?> {color: <?php echo esc_attr( $arr['color'] ); ?>!important}
		<?php } ?>

	</style>

	<?php
}
add_action( 'um_before_register_fields', 'um_social_login_add_buttons', 10, 1 );
add_action( 'um_before_login_fields', 'um_social_login_add_buttons', 10, 1 );

/**
 * @param $args
 */
function um_social_register_hidden_fields( $args ) {

	if ( ! empty( $args['custom_fields'] ) ) {

		if ( isset( $_SESSION['um_social_profile'] ) ) {
			echo "<input type='hidden' name='_um_social_login' value='1' />";
		}

		if ( isset( $_SESSION['um_social_login_redirect_after'] ) ) {
			echo "<input type='hidden' name='_um_social_login_redirect_to' value='" . esc_attr( esc_url_raw( $_SESSION['um_social_login_redirect_after'] ) ) . "' />";
		}

		if ( isset( $_SESSION['um_sso'] ) ) {
			echo "<input type='hidden' name='_um_social_login_is_shortcode' value='" . intval( $_SESSION['um_sso'] ) . "' />";
		}

		$form_id = UM()->Social_Login_API()->user_connect()->form_id();

		$step_process = UM()->Social_Login_API()->user_connect()->get_enabled_step_process( $form_id );
		if( $step_process == 0 && $args['form_id'] == $form_id && isset( $_SESSION['um_social_profile'] ) ){
			echo "<input type='hidden' name='_um_social_login_one_step' value='1' />";
			$flash_screen = UM()->Social_Login_API()->user_connect()->get_show_flash_screen( $form_id );
			echo "<input type='hidden' name='_um_sso_show_flash_screen' value='{$flash_screen}' />";
		}

	
		

	}

}
add_action( 'um_before_register_fields', 'um_social_register_hidden_fields', 10, 1 );


/**
 * Keep user signed in?
 * @return boolean
 */
function um_keep_signed_in() {

  $session = intval( !empty( $_SESSION['um_social_login_rememberme'] ) );
  $cookie = intval( !empty( $_COOKIE['um_social_login_rememberme'] ) );
  
  return $session || $cookie;
}


/**
 * @param $role
 *
 * @return string
 */
function um_remove_user_role( $role ) {
	return '';
}


/**
 * @param $current_url
 * @param $user_id
 */
function um_social_login_redirect_in_message_button( $current_url, $user_id ) {
	$_SESSION['um_social_login_redirect_after'] = $current_url;
}
add_action( "um_messaging_button_in_profile", "um_social_login_redirect_in_message_button", 1, 2 );


/**
 * @param $user_id
 */
function um_social_login_remove_profile_photo( $user_id ) {
	delete_user_meta( $user_id, 'synced_profile_photo' );
}
add_action( 'um_after_remove_profile_photo', 'um_social_login_remove_profile_photo', 10 ,1 );