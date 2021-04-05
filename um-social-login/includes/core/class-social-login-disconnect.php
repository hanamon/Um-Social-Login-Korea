<?php 
namespace um_ext\um_social_login\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Disconnect
 * @package um_ext\um_social_login\core
 */
class Social_Login_Disconnect{


	/**
	 * @var
	 */
	var $networks;


	/**
	 * Social_Login_Disconnect constructor.
	 */
	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );

	}

	/**
	 * Handle disconnect
	 */
	function init() {
		if ( ! isset( $_REQUEST['disconnect'] )  || ! is_user_logged_in() ) {
			return;
		}

		$uid = null;

		$uid = get_current_user_id();

		if ( current_user_can( 'manage_options' ) ) {

			if ( isset( $_REQUEST['uid'] ) &&  $_REQUEST['uid'] !== get_current_user_id() ) {
				$uid = absint( $_REQUEST['uid'] );
			}

		} elseif ( isset( $_REQUEST['uid'] ) && $_REQUEST['uid'] !== get_current_user_id() ) {
			wp_die( __( 'Ehh! hacking?', 'um-social-login' ) );
		}

		$provider = sanitize_key( $_REQUEST['disconnect'] );

		$networks = UM()->Social_Login_API()->available_networks();

		foreach ( $networks[ $provider ]['sync'] as $k => $v ) {
			delete_user_meta( $uid, $k );
		}

		delete_user_meta( $uid, "_uid_{$provider}");

		do_action( 'um_social_login_after_disconnect', $provider, $uid );
		do_action( "um_social_login_after_{$provider}_disconnect", $uid );


		exit( wp_redirect( UM()->account()->tab_link( 'social' ) ) );

	}

}