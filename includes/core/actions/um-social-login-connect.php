<?php 

/**
 * Link account to user
 * 
 * @param  string  $provider           
 * @param  object  $getUserProfile     
 * @param  string  $returnUrl          
 * @param  boolean $has_linked_account 
 *
 * @since  2.2
 */
function um_social_link_user_to_social( $provider, $getUserProfile, $returnUrl, $has_linked_account = false ){

	$arr_profile = array(
			"_uid_{$provider}" 					=> $getUserProfile->identifier,
			"_save_{$provider}_handle" 			=> $getUserProfile->displayName,
			"_save_{$provider}_photo_url_dyn" 	=> $getUserProfile->photoURL,
			"_save_{$provider}_photo_url" 		=> $getUserProfile->photoURL,
			"_save_{$provider}_link" 			=> $getUserProfile->profileURL,
			"_save_synced_profile_photo" 		=> $getUserProfile->photoURL,
			"username_exists"					=> $getUserProfile->email,
			"email_exists"						=> $getUserProfile->email,
			"_save_{$provider}_raw_data"		=> (array)$getUserProfile,
	);

	if( false == UM()->Social_Login_API()->user( um_user('ID') )->has_avatar_linked( $provider ) ){

		update_user_meta( um_user('ID'), "_um_social_login_avatar_provider", $provider );

		$getUserProfile->photoURL = str_replace( "width=150", "width=200", $getUserProfile->photoURL );

		$getUserProfile->photoURL = str_replace( "height=150", "", $getUserProfile->photoURL );
		
		update_user_meta( um_user('ID'), "synced_profile_photo", $getUserProfile->photoURL );
	}
   
	UM()->Social_Login_API()->user_connect()->save_user_meta( um_user('ID'), $arr_profile, $provider );
}
add_action("um_social_do_link_user","um_social_link_user_to_social", 10, 4 );


/**
 *  Link account to user error
 *  
 * @param  string  $provider           
 * @param  object  $getUserProfile     
 * @param  string  $returnUrl          
 * @param  boolean $has_linked_account 
 *
 * @since  2.2
 */
function um_social_link_user_to_social_error( $provider, $getUserProfile, $returnUrl, $has_linked_account = false ){

	if( defined("UM_SSO_PARENT_WINDOW") && UM_SSO_PARENT_WINDOW == $provider ){
		
		if( $has_linked_account == get_current_user_id() ) return;

		if( um_is_core_page("account") ){
			$returnUrl = UM()->account()->tab_link( 'social' );
		}

		$returnUrl = remove_query_arg( "return_provider", $returnUrl );

		$returnUrl = add_query_arg( "err","{$provider}_exist", $returnUrl );

		exit( wp_redirect( $returnUrl ) );
		
	}
}
add_action("um_social_do_link_user_error","um_social_link_user_to_social_error", 10, 4 );


/**
 * Show registration overlay via regular register form
 * 
 * @param  string $provider       
 * @param  object $getUserProfile 
 * @param  object $connectClass   
 * @param  string $returnUrl      
 *
 * @since  2.2
 */
function um_social_show_register_overlay( $provider, $getUserProfile, $connectClass, $returnUrl ){
	
		$connectClass->sync_fields( null, $provider );
		$connectClass->is_overlay_loaded++;
		if( $connectClass->is_overlay_loaded == 2 ){
			$connectClass->show_overlay();
		}
		
	add_action( 'um_before_register_fields', array( &$connectClass, 'load_overlay_assets'), 10, 1 );
}
add_action("um_social_doing_register","um_social_show_register_overlay", 10, 4 );


/**
 * Show registration overlay via shortcode
 * 
 * @param  string  $provider       
 * @param  boolean $has_linked     
 * @param  object  $getUserProfile 
 * @param  object  $connectClass   
 * @param  string  $returnUrl      
 *
 * @since  2.2             
 */
function um_social_doing_shortcode_process( $provider, $has_linked, $getUserProfile, $connectClass, $returnUrl ){
	
	$sso_session = UM()->Social_Login_API()->hybridauth()->getSession();
	$sso_session->get('um_sso_has_dynamic_return_url');
	$shortcode_id = $sso_session->get('um_sso');
	
	if( $shortcode_id ){

		$integration_type = get_post_meta( $shortcode_id, "_um_integration_type", true );
		
		switch ( $integration_type ) {
			case 'register':
				
				$connectClass->do_action = "register";

				break;

			case 'login':

				$connectClass->do_action = "login";

				break;

			case 'link_account':

				$connectClass->do_action = "link_account";
				
				break;

			default: // login_register
				
				$connectClass->do_action = "login_register";

				if( ! $has_linked ){
					$connectClass->do_action = "register";
				}else if( $has_linked ){
					$connectClass->do_action = "login";
				}
				
				break;
			
		}

		if ( is_user_logged_in() && isset( $_REQUEST['um_dynamic_sso'] ) && ! um_is_core_page("account") && ! isset( $_REQUEST['err'] ) && in_array( $connectClass->do_action, array("register","login","login_register") ) ) {

			$sso_current_url = $sso_session->get('um_sso_current_url');
			//$sso_current_url = UM()->Social_Login_API()->hybridauth()->getCurrentUrl();

			$sso_current_url = add_query_arg( "provider", $provider, $sso_current_url );

			exit( wp_redirect( $sso_current_url ) );
		}



	}
}
add_action("um_social_doing_shortcode","um_social_doing_shortcode_process", 10, 5);


/**
 * Validate email address registration
 * 
 * @param  string $provider       
 * @param  string $returnUrl      
 * @param  object $getUserProfile 
 * @param  object $classConnect   
 * @param  boolean $has_linked     
 *
 * @since  2.2               
 */
function um_social_one_step_process_matched_email( $provider, $returnUrl,  $getUserProfile, $classConnect, $has_linked ) {
	
	$form_id = UM()->Social_Login_API()->user_connect()->form_id();
	$enabled_step_process = UM()->Social_Login_API()->user_connect()->get_enabled_step_process( $form_id );

	if( $enabled_step_process == 0 ){ // One-step process

		$matched_email_process = UM()->Social_Login_API()->user_connect()->get_one_step_matched_email( $form_id );

		switch ( $matched_email_process ) {

			case 1: // Link Accounts & Login immediately
					
					if( ! isset( $_REQUEST['return_provider'] ) ) return;

					$profile = array('email_exists' => $getUserProfile->email );

					$email_exists = UM()->Social_Login_API()->user_connect()->email_exists( $profile, $provider );

					if( $user_id = $email_exists ){

						if( ! $has_linked ){
							UM()->Social_Login_API()->user_connect()->save_user_meta( 
								$user_id, 
								array("_uid_{$provider}" => $getUserProfile->identifier ), 
								$provider 
							);
						}

						if ( function_exists('um_keep_signed_in') ) {
							if ( um_keep_signed_in() ) {
								$_REQUEST['rememberme'] = 1;
							}
						}

						do_action("um_social_do_login", $provider, $user_id, $getUserProfile, $classConnect, $returnUrl );
					}
					
				break;

			case 2: // Link Accounts & Redirect to Login page
					
					if( ! isset( $_REQUEST['return_provider'] ) || ( isset( $_REQUEST['um_form_id'] ) && isset( $_REQUEST['message'] ) ) ) return;
	

					$profile = array('email_exists' => $getUserProfile->email );

					$email_exists = UM()->Social_Login_API()->user_connect()->email_exists( $profile, $provider );

					if( $email_exists ){

						if( ! $has_linked ){
							UM()->Social_Login_API()->user_connect()->save_user_meta( 
								$email_exists, 
								array("_uid_{$provider}" => $getUserProfile->identifier ), 
								$provider 
							);
						}

						$returnUrl = um_get_core_page("login");

						$returnUrl = add_query_arg( "err","um_sso_already_linked", $returnUrl );

						exit( wp_redirect( $returnUrl ) );
					}
					
				break;

			case 3: // Allow new account creation with a generated Email
					// default
				break;

			case 4: // Do not link accounts and prevent from account creation
					if( ! isset( $_REQUEST['return_provider'] ) ) return;

					$profile = array('email_exists' => $getUserProfile->email );

					$email_exists = UM()->Social_Login_API()->user_connect()->email_exists( $profile, $provider );
					
					if( $email_exists ){
						$returnUrl = remove_query_arg("return_provider", $returnUrl );

						$returnUrl = add_query_arg("err","um_sso_email_already_linked", $returnUrl );

						exit( wp_redirect( $returnUrl ) );
					}

				break;
					
		}

	}
}
add_action("um_social_do_register_authenticated_process","um_social_one_step_process_matched_email", 10, 5 );


/**
 * Register Error
 * 
 * @param  string $provider        
 * @param  object $getUserProfile  
 * @param  object $hybridAuthClass 
 * @param  string $returnUrl       
 *
 * @since  2.2    
 */
function um_social_do_register_error( $provider, $getUserProfile, $hybridAuthClass, $returnUrl ){

	if( ! isset( $_REQUEST['return_provider'] ) || ( isset( $_REQUEST['um_form_id'] ) && isset( $_REQUEST['message'] ) ) ) return;
	
	$returnUrl = remove_query_arg("return_provider", $returnUrl );

	$returnUrl = add_query_arg("err","um_sso_already_linked", $returnUrl );

	exit( wp_redirect( $returnUrl ) );
}
add_action("um_social_do_register_error","um_social_do_register_error", 10, 4 );


/**
 * Authenticated User - oAuth Window Close
 * 
 * @param  string $provider    
 * @param  string $returnUrl   
 * @param  object $connectUser 
 *
 * @since  2.2    
 */
function um_social_login_do_close_oauth_window( $provider, $returnUrl, $connectUser  ){

	$returnUrl = apply_filters("um_social_login_return_url", $returnUrl, $provider );
	
	$returnUrl = apply_filters("um_social_login_return_url__{$provider}", $returnUrl );

	$returnUrl = add_query_arg("ref", 4, $returnUrl );

	if ( defined("UM_SSO_CHILD_WINDOW") ) {
		echo "<script type=\"text/javascript\">window.close();window.opener.location.href='" . esc_url_raw( $returnUrl ) . "';</script>";
	}
}
add_action("um_social_do_oauth_window_process","um_social_login_do_close_oauth_window", 10, 3 );


/**
 * Authenticate user - OAuth Window close
 * 
 * @param  string $provider    
 * @param  string $returnUrl   
 * @param  object $connectUser             
 *
 * @since  2.2   
 */
function um_social_login_doing_close_oauth_window( $provider, $returnUrl, $connectUser  ){

	$returnUrl = apply_filters("um_social_login_return_url", $returnUrl, $provider );
	
	$returnUrl = apply_filters("um_social_login_return_url__{$provider}", $returnUrl );

	$returnUrl = add_query_arg("ref", 3, $returnUrl );

	echo "<script type=\"text/javascript\">if(window.opener != null && !window.opener.closed){ window.opener.location.href='" . esc_url_raw( $returnUrl ) . "';window.close();}else{window.location.href='" . esc_url_raw( $returnUrl ) . "';}</script>";

}
add_action( 'um_social_doing_oauth_window_process', 'um_social_login_doing_close_oauth_window', 10, 3 );


/**
 * Do login process
 * 
 * @param  string $provider       
 * @param  integer $user_id        
 * @param  object $getUserProfile 
 * @param  object $classConnect   
 * @param  string $returnUrl      
 *
 * @since  2.2                 
 */
function um_social_do_login( $provider, $user_id, $getUserProfile, $classConnect, $returnUrl ){
	
	if ( defined("UM_SSO_CHILD_WINDOW") ) {
          return;
	}
	um_fetch_user( $user_id );
	
	$after = um_user('after_login');

	switch( $after ) {
		
		case 'redirect_admin':
			$redirect_to = admin_url();
		break;

		case 'redirect_profile':
			$redirect_to = um_user_profile_url();
		break;

		case 'redirect_url':
			$redirect_to = um_user('login_redirect_url');
		break;

		case 'refresh':
			
			if ( ! isset( $_REQUEST['redirect_to'] ) || empty( $_REQUEST['redirect_to'] ) ){
				
				$redirect = UM()->Social_Login_API()->redirect();
				
				if ( $redirect['has_redirect'] == true || $redirect['is_shortcode'] == 1 ) {
					$redirect_to = $redirect['redirect_to'];
				}
			} elseif ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ){
			
				$redirect_to = esc_url_raw( $_REQUEST['redirect_to'] );
			}

			if ( ! isset( $redirect_to ) || empty( $redirect_to ) ) {
				$redirect_to = um_get_core_page( 'login' );
			}

			unset( $_SESSION['um_social_login_redirect'] );

		break;

	}

	$user_status = $classConnect->check_user_status( $user_id );

	if( isset( $user_status['error'] ) && $user_status['error'] == true ){

		switch ( $user_status['error_code'] ) {
			case 'checkmail':
				 $returnUrl = $user_status['url'];
				break;
			case 'awaiting_email_confirmation':
			case 'awaiting_admin_review':
			case 'inactive':
			case 'rejected':
				$returnUrl = add_query_arg("err", $user_status['error_code'], $returnUrl );
				break;
			default:
				$returnUrl = add_query_arg("err", $user_status['error_code'], $returnUrl );
				break;
		}

		do_action("um_social_do_login_error", $provider, $getUserProfile, $returnUrl );

			
	}else{

		if ( function_exists('um_keep_signed_in') ) {
			if ( um_keep_signed_in() ) {
					$_REQUEST['rememberme'] = 1;
			}
		}

		$url = UM()->Social_Login_API()->hybridauth()->getCurrentUrl();
		
		$args = array();
		$args['rememberme'] = true;
		
		$parts = parse_url( $url );
		if( isset( $parts['query'] ) ){
			parse_str($parts['query'], $query);
		}

		if( isset( $query['redirect_to'] ) ){
			$returnUrl = $query['redirect_to'];
		}else{
			$returnUrl = $url;
		}

		if( ! empty( $returnUrl ) ){
			$args['redirect_to'] = $returnUrl;
		}
		
		$sso_session = UM()->Social_Login_API()->hybridauth()->getSession();
		if( $redirect_to = $sso_session->get('um_social_login_redirect') ){
			$returnUrl = $redirect_to;
			$args['redirect_to'] = $returnUrl;
		}

		do_action( 'um_user_login', $args );
		
	}

}
add_action("um_social_do_login","um_social_do_login", 10, 5 );


/**
 * oAuth Window close and redirect to return Url with errors
 * 
 * @param  string $provider       
 * @param  object $getUserProfile 
 * @param  string $returnUrl      
 *
 * @since  2.2                 
 */
function um_social_do_login_error_not_linked( $provider, $getUserProfile, $returnUrl ){
	
	if ( ! isset( $_REQUEST['return_provider'] ) /*|| isset( $_REQUEST['um_dynamic_sso'] )*/ ) return;
    
    if( strpos( $returnUrl, "err" ) === FALSE ){
		$returnUrl = add_query_arg("err","um_sso_not_linked", $returnUrl );
	}
	
	$returnUrl = remove_query_arg("return_provider", $returnUrl );

	$returnUrl = apply_filters("um_social_login_do_login_error_return_url", $returnUrl, $provider );
	
	$returnUrl = apply_filters("um_social_login_do_login_error_return_url__{$provider}", $returnUrl );

 	exit( wp_redirect( $returnUrl ) );
}
add_action("um_social_do_login_error","um_social_do_login_error_not_linked", 5, 3 );


/**
 * oAuth Window close and redirect to return Url with errors
 * 
 * @param  string $provider    
 * @param  string $returnUrl   
 * @param  object $connectUser 
 *
 * @since  2.2              
 */
function um_social_oauth_window_error_user_denied( $provider, $returnUrl, $connectUser ){

	$returnUrl = add_query_arg("err", 'um_social_user_denied' , $returnUrl );

	$returnUrl = apply_filters("um_social_login_window_process_error_return_url", $returnUrl, $provider );
	
	$returnUrl = apply_filters("um_social_login_window_process_error_return_url__{$provider}", $returnUrl );

	$returnUrl = add_query_arg("ref", 1, $returnUrl );

	echo "<script type=\"text/javascript\">if(window.opener != null && !window.opener.closed){ window.opener.location.href='" . esc_url_raw( $returnUrl ) . "';window.close();}else{window.location.href='" . esc_url_raw( $returnUrl ) . "';}</script>";
	exit;
}
add_action( 'um_social_oauth_window_process_error', 'um_social_oauth_window_error_user_denied', 10, 3 );


/**
 * Redirect users after login with custom redirect_to url
 * 
 * @param  string $provider 
 *
 * @since  2.2          
 */
function um_social_do_redirect_after_login( $provider ){

	if( ! isset( $_REQUEST['um_sso_logged_in'] ) || ! isset( $_REQUEST['redirect_to'] ) ) return;

	$sso_session = UM()->Social_Login_API()->hybridauth()->getSession();
	$returnUrl = esc_url_raw( $_REQUEST['redirect_to'] );
	if( $redirect_to = $sso_session->get('um_social_login_redirect') ){
		$returnUrl = $redirect_to;
		$sso_session->set('um_social_login_redirect', null );
	}

	echo "<script type=\"text/javascript\">if(window.opener != null && !window.opener.closed){ window.opener.location.href='" . esc_url_raw( $returnUrl ) . "';window.close();}else{window.location.href='" . esc_url_raw( $returnUrl ) . "';}</script>";
	exit;
}
add_action( 'um_social_do_redirect_after_login', 'um_social_do_redirect_after_login', 10, 1 );


/**
 * Make all fields hidden for one-step process
 * 
 * @param  string $output 
 * @param  array $data   
 *
 * @since  2.2   
 */
function um_sso_edit_field_register_hidden( $output, $data ){

	$field_key = $data['metakey'];

	$field_name = $field_key. UM()->form()->form_suffix;

	$field_value = htmlspecialchars( UM()->fields()->field_value( $field_key,'', $data ) );

	echo '<input  type="hidden" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $field_value ) . '"  />';
}
add_action("um_edit_field_register_hidden","um_sso_edit_field_register_hidden", 10, 2 );


/**
 * Auto approves users after registration complete.
 *
 * Overrides the Registration Options set in User Roles settings
 * 
 * @param  integer $user_id 
 * @param  array $args    
 *
 * @since 2.2
 */
function um_sso_register_auto_approved( $user_id, $args ){

	if( ! isset( $args['submitted']['_um_social_login'] ) ) return;

	if( 'checkmail' == um_user('status') ) return;
	if( 'pending' == um_user('status') ) return;

	add_action( 'um_post_registration_checkmail_hook', 'um_post_registration_checkmail_hook', 10, 2 );
	add_action('um_post_registration_pending_hook', 'um_post_registration_pending_hook', 10, 2);

	
	do_action( 'um_post_registration_approved_hook', $user_id, $args  );
	remove_action( 'um_post_registration_approved_hook', 'um_post_registration_approved_hook', 10, 2 );


}
add_action("um_registration_complete","um_sso_register_auto_approved", 10, 2 );

