<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Creates the form metaboxes
 *
 * @param $action
 */
function um_social_login_add_register_metabox() {
	add_meta_box( "um-admin-form-register{" . um_social_login_path . "}", __( 'Social Login', 'um-social-login' ), array( UM()->metabox(), 'load_metabox_form' ), 'um_form', 'side', 'low' );
	add_meta_box( "um-admin-form-login{" . um_social_login_path . "}", __( 'Social Login', 'um-social-login' ), array( UM()->metabox(), 'load_metabox_form' ), 'um_form', 'side', 'low' );
}
add_action( 'um_admin_custom_register_metaboxes', 'um_social_login_add_register_metabox' );