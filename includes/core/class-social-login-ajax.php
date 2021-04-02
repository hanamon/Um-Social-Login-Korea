<?php
namespace um_ext\um_social_login\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Ajax
 *
 * @package um_ext\um_social_login\core
 */
class Social_Login_Ajax {


	/**
	 * Change User Avatar via AJAX
	 */
	function ajax_change_photo() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['user_id'] ) || ! is_numeric( $_POST['user_id'] ) || ! is_user_logged_in() ) {
			wp_send_json_error( 'user_id' );
		}
		if ( ! isset( $_POST['provider'] ) ) {
			wp_send_json_error( 'provider' );
		}

		$user_id = absint( $_POST['user_id'] );
		$provider = sanitize_key( $_POST['provider'] );
		$output['source'] = "";
		
		if ( $avatar = UM()->Social_Login_API()->user( $user_id )->getProfilePhoto( $provider ) ) {

			$output['source'] = $avatar;

		} else if( $provider == "core" ) {

			$profile_photo = get_user_meta( $user_id, 'profile_photo', true );

			$baseurl = UM()->uploader()->get_upload_base_url();
			if ( ! file_exists( UM()->uploader()->get_upload_base_dir() . $user_id . DIRECTORY_SEPARATOR . $profile_photo ) ) {
				if ( is_multisite() ) {
					//multisite fix for old customers
					$baseurl = str_replace( '/sites/' . get_current_blog_id() . '/', '/', $baseurl );
				}
			}

			$avatar = $baseurl . $user_id . '/' . $profile_photo;

			$output['source'] = $avatar;

		}

		update_user_meta( $user_id, 'synced_profile_photo', $avatar );
		update_user_meta( $user_id, '_um_social_login_avatar_provider', $provider );

		wp_send_json_success( $output );
	}
}