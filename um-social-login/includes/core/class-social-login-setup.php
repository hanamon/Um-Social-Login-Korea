<?php
namespace um_ext\um_social_login\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Setup
 * @package um_ext\um_social_login\core
 */
class Social_Login_Setup {
	var $settings_defaults;
	var $core_form_meta;
	var $networks;

	function __construct() {
		//settings defaults
		$this->settings_defaults = array();

		$this->core_form_meta = array(
			'_um_custom_fields'                 => 'a:5:{s:10:"user_login";a:17:{s:6:"in_row";s:9:"_um_row_1";s:10:"in_sub_row";s:1:"0";s:9:"in_column";s:1:"1";s:4:"type";s:4:"text";s:7:"metakey";s:10:"user_login";s:8:"position";s:1:"1";s:5:"title";s:8:"Username";s:9:"min_chars";s:1:"3";s:10:"visibility";s:3:"all";s:5:"label";s:8:"Username";s:6:"public";s:1:"1";s:8:"validate";s:15:"unique_username";s:9:"max_chars";s:2:"24";s:14:"sso_sync_value";s:10:"identifier";s:8:"required";s:1:"1";s:8:"editable";s:1:"0";s:8:"in_group";s:0:"";}s:10:"first_name";a:13:{s:6:"in_row";s:9:"_um_row_1";s:10:"in_sub_row";s:1:"0";s:9:"in_column";s:1:"1";s:4:"type";s:4:"text";s:7:"metakey";s:10:"first_name";s:8:"position";s:1:"2";s:5:"title";s:10:"First Name";s:10:"visibility";s:3:"all";s:5:"label";s:10:"First Name";s:6:"public";s:1:"1";s:14:"sso_sync_value";s:9:"firstName";s:8:"editable";s:1:"1";s:8:"in_group";s:0:"";}s:9:"last_name";a:13:{s:6:"in_row";s:9:"_um_row_1";s:10:"in_sub_row";s:1:"0";s:9:"in_column";s:1:"1";s:4:"type";s:4:"text";s:7:"metakey";s:9:"last_name";s:8:"position";s:1:"3";s:5:"title";s:9:"Last Name";s:10:"visibility";s:3:"all";s:5:"label";s:9:"Last Name";s:6:"public";s:1:"1";s:14:"sso_sync_value";s:8:"lastName";s:8:"editable";s:1:"1";s:8:"in_group";s:0:"";}s:10:"user_email";a:14:{s:6:"in_row";s:9:"_um_row_1";s:10:"in_sub_row";s:1:"0";s:9:"in_column";s:1:"1";s:4:"type";s:4:"text";s:7:"metakey";s:10:"user_email";s:8:"position";s:1:"4";s:5:"title";s:14:"E-mail Address";s:10:"visibility";s:3:"all";s:5:"label";s:14:"E-mail Address";s:6:"public";s:1:"1";s:8:"validate";s:12:"unique_email";s:14:"sso_sync_value";s:5:"email";s:8:"editable";s:1:"1";s:8:"in_group";s:0:"";}s:9:"_um_row_1";a:5:{s:4:"type";s:3:"row";s:2:"id";s:9:"_um_row_1";s:8:"sub_rows";s:1:"1";s:4:"cols";s:1:"1";s:6:"origin";s:9:"_um_row_1";}}',
			'_um_mode'                          => 'register',
			'_um_core'                          => 'social',
			'_um_register_use_custom_settings'  => 0,
			'_um_register_show_social_2steps'	=> 1,
		);

		$networks = apply_filters( 'um_social_login_networks', $this->networks );

		if ( ! empty( $networks ) && is_array( $networks ) ) {
			foreach ( $networks as $network_id => $array ) {

				$this->settings_defaults[ 'enable_' . $network_id ] = 0;

				if ( isset( $array['opts'] ) ) {
					foreach ( $array['opts'] as $opt_id => $title ) {
						$this->settings_defaults[ $opt_id ] = '';
					}
				}
			}

		}
	}


	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 *
	 */
	function run_setup() {
		$this->setup();
		$this->set_default_settings();
	}


	/**
	 * Setup
	 */
	function setup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$um_social_login_form_installed = get_option( 'um_social_login_form_installed' );

		$has_form_installed = get_post_status( $um_social_login_form_installed );

		if ( in_array( $has_form_installed, array( 'publish', 'draft', 'trash' ) ) ) {
			return;
		}

		$user_id = get_current_user_id();

		$form = array(
			'post_type' 	  	=> 'um_form',
			'post_title'		=> __('Social Registration','um-social-login'),
			'post_status'		=> 'publish',
			'post_author'   	=> $user_id,
		);

		$form_id = wp_insert_post( $form );

		foreach ( $this->core_form_meta as $key => $value ) {
			if ( $key == '_um_custom_fields' ) {
				$array = unserialize( $value );
				update_post_meta( $form_id, $key, $array );
			} else {
				update_post_meta($form_id, $key, $value);
			}
		}

		update_post_meta( $form_id, '_um_social_login_form', 1 );
		update_option( 'um_social_login_form_installed', $form_id );
	}
}