<?php

/**
 * Set Login callback url for Single-callback network/provider
 */
function um_social_login_callback_url( $callback_url, $provider ){

	$networks = array_keys( UM()->Social_Login_API()->get_single_callback_networks() );
	if( ! in_array( $provider , $networks ) ) return $callback_url;

	$callback_url = um_get_core_page("login");

	$callback_url = add_query_arg( "provider", $provider, $callback_url );

	return $callback_url;
}
add_filter("um_social_login_callback_url","um_social_login_callback_url", 10, 2 );


/**
 * Set Login Return Url for Single-callback network/provider
 */
function um_social_login_return_url( $return_url, $provider ){

	$networks = array_keys( UM()->Social_Login_API()->get_single_callback_networks() );
	if( ! in_array( $provider , $networks ) ) return $return_url;
	
	$return_url = $_SESSION["um_sso_{$provider}_return_url"];

	$return_url = add_query_arg( "return_provider", $provider, $return_url );

	return $return_url;
}
add_filter("um_social_login_window_process_error_return_url","um_social_login_return_url", 10, 2 );
add_filter("um_social_login_return_url","um_social_login_return_url", 10, 2 );


/**
 * Set Connect Url for Single-callback network/provider
 */
function um_social_login_connect_url( $connect_url, $provider ){

	$networks = array_keys( UM()->Social_Login_API()->get_single_callback_networks() );
	if( ! in_array( $provider , $networks ) ) return $connect_url;
	
	$return_url = UM()->Social_Login_API()->hybridauth()->getCurrentUrl();

	$_SESSION["um_sso_{$provider}_return_url"] = $return_url;

	return $connect_url;
}
add_filter("um_social_login_connect_url","um_social_login_connect_url", 10, 2 );


/**
 * Make register fields hidden for One-step process
 */
function um_sso_make_fields_hidden( $fields = array() ){

	if ( empty( $fields ) ) {
		return $fields;
	}

	if ( isset( $_REQUEST['return_provider'] ) ) {
		
		$mode = ( isset( UM()->fields()->set_mode ) ) ? UM()->fields()->set_mode : null;

		if ( "login" == $mode ) {
			return $fields;
		}

		$form_id = UM()->Social_Login_API()->user_connect()->form_id();
		$current_form_id = (isset ( UM()->fields()->set_id ) ) ? UM()->fields()->set_id : null;
		
		$step_process = UM()->Social_Login_API()->user_connect()->get_enabled_step_process( $form_id );

		if ( $step_process == 1 || $step_process == '' ) {
			return $fields;
		}

		// Remove submit button
		remove_action( 'um_after_register_fields', 'um_add_submit_button_to_register', 1000 );

		$profile = UM()->Social_Login_API()->user_connect()->getUserProfile;

		foreach ( $fields as $field_key => $field_value ) {

			if ( strpos( $field_key, "_um_row" ) !== false || strpos( $field_key, "um_block" ) !== false  ) {

				$fields[ $field_key ] = $field_value;

			} else {
				$field_value['type'] = 'hidden';

				$fields[ $field_key ] = $field_value;
			}

			if ( isset( $field_value['sso_sync_value'] ) ) {
				// Email Address
				if( 'user_email' == $field_key && isset( $profile->{$field_value['sso_sync_value']} ) ){

					if( email_exists( $profile->{$field_value['sso_sync_value']} ) ){
						unset( $fields['user_email'] );
					}

				}

				// Username
				if ( 'user_login' == $field_key && isset( $profile->{$field_value['sso_sync_value']} ) ) {

					if ( username_exists( $profile->{$field_value['sso_sync_value']} ) ) {
						$profile->{$field_value['sso_sync_value']} = UM()->Social_Login_API()->generate_unique_username( $profile->{$field_value['sso_sync_value']} );
						$_SESSION['um_social_profile'][ $field_key ] = $profile->{$field_value['sso_sync_value']};
					}

				}
			}

			// User login/email
			if ( ! in_array( $field_key , array( 'user_login', 'user_email' ) ) ) {
				unset( $fields[ $field_key ]['required'] );
				unset( $fields[ $field_key ]['validate'] );
			}


		}

	}


	return $fields;
}
add_filter( 'um_get_form_fields', 'um_sso_make_fields_hidden', 100, 1 );


/**
 * Add fallback for first and last name
 */
function um_sso_returned_raw_data( $key, $sso_sync_value, $field, $profile, $provider ){

	if ( 'first_name' == $key ) {

		if ( empty( $sso_sync_value ) ) {
			$displayName = $profile->displayName;
			list( $first_name, $last_name ) = explode(" ", $displayName );
			$sso_sync_value = $first_name;
		}

		if( empty( $sso_sync_value ) ){
			$sso_sync_value = $profile->identifier;
		}
	}

	if( "last_name" == $key ){

		if( empty( $sso_sync_value ) ){
			$displayName = $profile->displayName;
			list( $first_name, $last_name ) = explode(" ", $displayName );
			$sso_sync_value = $last_name;
		}

		if( empty( $sso_sync_value ) ){
			$sso_sync_value = $provider;
		}
	}

	if( "user_email" == $key ){

		if( empty( $sso_sync_value ) ){
			
			$unique_userID = UM()->query()->count_users() + 1;
			$site_url = @$_SERVER['SERVER_NAME'];
			
			$email_prefix = apply_filters("um_sso_generate_email_address", "nobody{$unique_userID}", $unique_userID, $site_url );
			$email_domain = apply_filters("um_sso_generate_email_domain", $site_url );
			
			$sso_sync_value = $email_prefix. '@' . $email_domain;
			
		}

	}

	return $sso_sync_value;
}
add_filter( "um_sso_returned_raw_data", "um_sso_returned_raw_data", 10, 5 );

/**
 * Disable SSO fields when users cannot edit the field
 * @param  string $disabled 
 * @param  array $data     
 * @return string           
 */
function um_sso_disable_fields( $disabled, $data ){

    if( isset( $data['editable'] ) && 0 == $data['editable'] && isset( $_REQUEST['return_provider'] ) && ! isset( $_REQUEST['err'] ) ){
    	$disabled = " disabled='disabled' ";
    }
	return $disabled;
}
add_filter("um_is_field_disabled","um_sso_disable_fields", 10, 2 );
