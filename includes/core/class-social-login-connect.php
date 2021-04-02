<?php 

namespace um_ext\um_social_login\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Connect
 * @package um_ext\um_social_login\core
 */
class Social_Login_Connect{

	var $networks;

	var $form_id;

	var $getUserProfile;

	var $current_provider;

	var $oAuthResponse;

	var $do_action = '';

	var $is_overlay_loaded = 0;


	function __construct() {
		
		$this->is_overlay_loaded = 0;

		if( isset( $_REQUEST['um_form_id'] ) && isset( $_REQUEST['message'] ) )  return;
	
		add_action( 'wp', array( &$this, 'init' ) );
		add_action( 'template_redirect', array( &$this, 'init' ) );
	}


	/**
	 * Init
	 */
	function init() {
        
        if ( isset( $_REQUEST['provider'] ) && ! empty( $_REQUEST['provider'] ) || isset( $_REQUEST['return_provider'] ) && ! empty( $_REQUEST['return_provider'] )  || isset( $_REQUEST['code'] ) ) {


			if ( isset( $_REQUEST['provider'] )  ) {

				$provider = sanitize_key( $_REQUEST['provider'] );

				if( ! defined( "UM_SSO_CHILD_WINDOW") ) define( "UM_SSO_CHILD_WINDOW", $provider );
				if( ! defined( "UM_SSO_WINDOW" ) ) define( "UM_SSO_WINDOW", "child" );
				
			} elseif ( isset( $_REQUEST['return_provider'] ) ) {

				$provider = sanitize_key( $_REQUEST['return_provider'] );

				if( ! defined( "UM_SSO_PARENT_WINDOW") ) define( "UM_SSO_PARENT_WINDOW", $provider );
				if( ! defined( "UM_SSO_WINDOW" ) ) define( "UM_SSO_WINDOW", "parent" );
				
			}else{
				$um_sso_session = UM()->Social_Login_API()->hybridauth()->getSession();
				$provider = $um_sso_session->get("sso_last_auth_provider");
			}

			if( empty( $provider ) ) return;

			if ( is_user_logged_in() ) {
				do_action('um_social_do_redirect_after_login', $provider );
			}

			$this->oAuthResponse = UM()->Social_Login_API()->hybridauth()->connectUser( $provider );
 
			if ( isset( $this->oAuthResponse['has_errors'] ) && defined("UM_SSO_CHILD_WINDOW") ) {
				
				do_action('um_social_oauth_window_process_error', $provider, $this->oAuthResponse["returnUrl"], $this->oAuthResponse );
				do_action("um_social_oauth_window_process_error__{$provider}", $this->oAuthResponse["returnUrl"], $this->oAuthResponse );
			}


			if ( isset( $_REQUEST['oauthWindow'] ) && defined( "UM_SSO_CHILD_WINDOW" ) ) {

				if ( isset( $this->oAuthResponse['userProfile'] ) ) {

					do_action("um_social_doing_oauth_window_process", $provider, $this->oAuthResponse["returnUrl"], $this->oAuthResponse );
					do_action("um_social_doing_oauth_window_process__{$provider}", $this->oAuthResponse["returnUrl"], $this->oAuthResponse );

				}

			} elseif ( isset( $this->oAuthResponse['userProfile'] ) ) {

				$this->getUserProfile = $this->oAuthResponse['userProfile'];

				if ( defined("UM_SSO_CHILD_WINDOW") ) { // Authenticate process in Child Window

					do_action("um_social_do_oauth_window_process__{$provider}",  $this->oAuthResponse["returnUrl"], $this->oAuthResponse );
					do_action("um_social_do_oauth_window_process", $provider, $this->oAuthResponse["returnUrl"], $this->oAuthResponse );

				} elseif( defined("UM_SSO_PARENT_WINDOW") ) {

					do_action("um_social_do_authenticated_process", $provider, $this->oAuthResponse["returnUrl"], $this->oAuthResponse );

				}

				$has_linked = $this->has_account_linked( $provider, $this->oAuthResponse['userProfile'] );

				do_action("um_social_doing_shortcode", $provider, $has_linked, $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );
				do_action("um_social_doing_shortcode__{$provider}", $has_linked, $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );


				if ( ! is_user_logged_in() ) {

					$has_linked = $this->has_account_linked( $provider, $this->oAuthResponse['userProfile'] );
					
					// Login
					if( ( um_is_core_page('login') && empty( $this->do_action ) ) || in_array( $this->do_action, array("login","login_register") ) ){

						if( $has_linked ){

							do_action("um_social_do_login", $provider, $has_linked, $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );
							do_action("um_social_do_login__{$provider}", $has_linked, $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );

						}else{

							do_action("um_social_do_login_error", $provider, $this->getUserProfile, $this->oAuthResponse["returnUrl"] );
							do_action("um_social_do_login_error__{$provider}", $this->getUserProfile, $this->oAuthResponse["returnUrl"] );

						}

					}

					// Register
					else if( ( um_is_core_page('register') && empty( $this->do_action ) ) || in_array( $this->do_action, array("register","login_register") ) ){

						$has_linked = $this->has_account_linked( $provider, $this->oAuthResponse['userProfile'] );

						do_action("um_social_do_register_authenticated_process",$provider, $this->oAuthResponse["returnUrl"],  $this->getUserProfile, $this, $has_linked );

						if( $has_linked ){

							do_action("um_social_do_register_error", $provider, $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );
							do_action("um_social_do_register_error__{$provider}", $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );
						}else{
							do_action("um_social_doing_register", $provider, $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );
							do_action("um_social_doing_register__{$provider}", $this->getUserProfile, $this, $this->oAuthResponse["returnUrl"] );
						}


					}
				} elseif ( is_user_logged_in() || "link_account" == $this->do_action ) { // Social Account or Custom Page

					if ( um_is_core_page("login") || um_is_core_page("register") ) {
						return;
					}

					$has_linked = $this->has_account_linked( $provider, $this->oAuthResponse['userProfile'] );

					if ( ! $has_linked && ! isset( $_REQUEST['err'] ) ) {

						do_action("um_social_do_link_user", $provider, $this->getUserProfile, $this->oAuthResponse["returnUrl"], $has_linked );
						do_action("um_social_do_link_user__{$provider}", $this->getUserProfile, $this->oAuthResponse["returnUrl"], $has_linked );

					} elseif ( $has_linked && ! isset( $_REQUEST['err'] ) ) {

						do_action("um_social_do_link_user_error", $provider, $this->getUserProfile, $this->oAuthResponse["returnUrl"], $has_linked );
						do_action("um_social_do_link_user_error__{$provider}", $this->getUserProfile, $this->oAuthResponse["returnUrl"], $has_linked );

					}
				}

			}
			
		} else {

			if ( isset( $_SESSION['um_social_profile'] ) ) {
				unset( $_SESSION['um_social_profile'] );
			}
		}
	}


	/**
	 * Has account linked to provider
	 * @return boolean 
	 */
	function has_account_linked( $provider, $userProfile ){

		global $wpdb;

		if ( isset( $userProfile->identifier ) && ! empty( $userProfile->identifier ) ) {
			$user = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$wpdb->usermeta} 	WHERE meta_key = %s AND  meta_value = %s", 
				"_uid_{$provider}", 
				$userProfile->identifier
			));
		}
		
		$automatic_login_emil_exists = apply_filters('um_sso_automatic_login_with_email', false );
		if( $automatic_login_emil_exists  && ! $user && isset( $userProfile->email ) && ! empty( $userProfile->email  ) ){
			$user = $wpdb->get_row( $wpdb->prepare(
				"SELECT ID as user_id FROM {$wpdb->users} WHERE user_email = %s ", 
				$userProfile->email
			));
		}

		if ( isset( $user->user_id ) ) {
			return $user->user_id;
		}


		return false;
	}

	/**
	 * Is connected
	 *
	 * @param $user_id
	 * @param $provider
	 *
	 * @return bool
	 */
	function is_connected( $user_id, $provider ) {
		$connection = get_user_meta( $user_id, '_uid_' . $provider, true );
		if ( $connection ) {
			return true;
		}
		return false;
	}



	/**
	 * Load overlay assets
	 */
	function load_overlay_assets() {
		wp_enqueue_script( 'um-social-login' );
		wp_enqueue_style( 'um-social-login' );
	}


	/**
	 * Get assigned register form to overlay
	 * @return integer
	 */
	function form_id(){
		
		$assigned_form_id = (int) get_option( 'um_social_login_form_installed' );

		return apply_filters("um_social_login_assigned_form_id", $assigned_form_id );
	}


	/**
	 * Show overlay
	 */
	function show_overlay( ) {

		remove_action( 'um_before_register_fields', 'um_social_login_add_buttons', 10 );

		wp_enqueue_script( 'um-social-login' );
		wp_enqueue_style( 'um-social-login' );

		do_action( 'um_social_login_before_show_overlay' );

		$this->form_id = $this->form_id();

		$step_process = $this->get_enabled_step_process( $this->form_id );

		if ( $step_process == 1 || $step_process == '' ) {
			$tpl = 'form';
		} else {
			$tpl = 'pre-loader-form';
		}
        
		$current_url = UM()->Social_Login_API()->hybridauth()->getCurrentUrl();
		UM()->get_template( "{$tpl}.php", um_social_login_plugin, array( 'current_url' => $current_url, 'form_id' => $this->form_id ), true );
		
	
	
	}


	/**
	 * Sync fields
	 * @param  integer $form_id 
	 */
	function sync_fields( $form_id = null, $provider = ''  ){

		if( ! $form_id ){
			$form_id = $this->form_id();
		}
		
		$fields = UM()->query()->get_attr( 'custom_fields',  $form_id );
		
		$profile = $this->getUserProfile;
		$provider = isset( $_REQUEST['return_provider'] ) ? sanitize_key( $_REQUEST['return_provider'] ) : '';

		foreach ( $fields as $key => $field ) {

			if ( ! empty( $field['sso_sync_value'] ) ) {

				if ( "extend" == $field['sso_sync_value'] ) {

					$_SESSION['um_social_profile'][ $key ] = apply_filters( "um_social_profile__custom_data_{$key}", "", $profile, $form_id, $fields );
				
				} elseif ( isset( $profile->{$field['sso_sync_value']} ) ) {

					$_SESSION['um_social_profile'][ $key ] = $profile->{$field['sso_sync_value']};

					if ( in_array( $field['sso_sync_value'], array( 'identifier', 'photoURL', 'displayName', 'profileURL' ) ) ) {
						if ( 'displayName' == $field['sso_sync_value'] ) {
							$_SESSION['um_social_profile']["_uid_{$provider}"] = $field['sso_sync_value'];
						} elseif ( 'displayName' == $field['sso_sync_value'] ) {
							$_SESSION['um_social_profile']['handle'] = $field['sso_sync_value']; 
						} elseif ( 'profileURL' == $field['sso_sync_value'] ) {
							$_SESSION['um_social_profile']['link'] = $field['sso_sync_value']; 
						} elseif ( 'photoURL' == $field['sso_sync_value'] ) {
							$_SESSION['um_social_profile']['photo_url'] = $field['sso_sync_value']; 
						}

					}

				} else {

					$_SESSION['um_social_profile'][ $key ] = apply_filters("um_sso_returned_raw_data", $key, $profile->{$field['sso_sync_value']}, $field, $profile, $provider );

				}

			}
		}

		if ( isset( $_REQUEST['form_id'] ) ) {
			unset( $_SESSION['um_social_profile'] );
		}

	}


	/**
	 * Get step process option
	 * @param  integer $post_id 
	 * @return integer          
	 */
	function get_enabled_step_process( $post_id = null ){

		return get_post_meta( $post_id, '_um_register_show_social_2steps', true );
	}


	/**
	 *  Get show flash screen option
	 * @param  integer $post_id 
	 * @return integer          
	 */
	function get_show_flash_screen( $post_id = null ){

		return get_post_meta( $post_id, '_um_register_show_flash_screen', true );
	}


	/**
	 * One step matched email
	 * @param  integer $post_id 
	 * @return integer      
	 */
	function get_one_step_matched_email( $post_id = null ){

		return get_post_meta( $post_id, '_um_register_1step_link_matched_email', true );
	
	}


	/**
	 * Link user to provider
	 * @param  integer $user_id
	 */
	function save_user_meta( $user_id = null, $profile = array(), $provider = '' ){

		if ( $user_id == null ) {
			$user_id = get_current_user_id();
		}

		if ( $user_id <= 0 ) {
			return;
		}

		foreach ( $profile as $key => $value ) {
			if ( strstr( $key, '_uid_') ) {
				update_user_meta( $user_id , $key, $value );
			} elseif ( strstr( $key, '_save_') ) {
				$key = str_replace('_save_','',$key);
				if ( $key != 'synced_profile_photo' ) {
					update_user_meta( $user_id , $key, $value );
				}
			} else {
				update_user_meta( $user_id, '_um_sso_'.$provider.'_'.$key, $value );
			}
		}

		update_user_meta( $user_id, '_um_sso_'.$provider.'_date_connected', current_time('mysql') );

		do_action( "um_social_login_after_connect", $provider, $user_id );
		do_action( "um_social_login_after_{$provider}_connect", $user_id );
	}

	/**
	 * Check user status
	 * @param  integer $user_id 
	 * @return integer          
	 */
	function check_user_status( $user_id ) {
		
		um_fetch_user( $user_id );

		$status = um_user( 'account_status' );

		switch( $status ) {

			// If user can't login to site...
			case 'inactive':
			case 'awaiting_admin_review':
			case 'checkmail':
            case 'rejected':
			
				$checkmail_url = um_user( 'checkmail_url' );
				$checkmail_action = um_user( 'checkmail_action' );

				if ( is_user_logged_in() ) {
					wp_logout();
				}

				if ( ! empty( $checkmail_url ) && $checkmail_action == 'redirect_url' ) {
					return array( 'error' => true, 'error_code' => 'checkmail',  'url' => $checkmail_url );
				}

				um_reset_user();
				$error = get_query_var( 'err' );

				if ( empty( $error ) ) {

					return array( 'error' => true, 'error_code' => $status );

				}

			break;

			case 'awaiting_email_confirmation':
			    if ( is_user_logged_in() ) {
					wp_logout();
				}

				return array( 'error' => true, 'error_code' => 'awaiting_email_confirmation' );
				
            break;

			

		}

		return array( 'error' => false );
	}


	/**
	 * Check that user exists but not connected yet
	 *
	 * @param $profile
	 * @param $provider
	 *
	 * @return false|int
	 */
	function email_exists( $profile, $provider ) {
		
		if ( isset( $profile['email_exists'] ) && email_exists( $profile['email_exists'] ) ) {
			return email_exists( $profile['email_exists'] );
		}

		return 0;
	}

	/**
	 * Check if email or username exists
	 * @param  array $profile  
	 * @param  string $provider 
	 * @return bool  
	 */
	function user_exists( $profile, $provider ) {
		if ( isset( $profile['email_exists'] ) && email_exists( $profile['email_exists'] ) ) {
			return email_exists( $profile['email_exists'] );
		}
		if ( isset( $profile['username_exists'] ) && username_exists( $profile['username_exists'] ) ) {
			return username_exists( $profile['username_exists'] );
		}
		return 0;
	}
}