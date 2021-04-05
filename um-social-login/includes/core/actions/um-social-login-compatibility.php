<?php 

/**
 * WP Mail SMTP Compatible
 */
add_action("wp_mail_smtp_mailcatcher_smtp_send_before","um_sso_compatibility_wp_mail_smtp", 10, 1 );
function um_sso_compatibility_wp_mail_smtp(){
	
	remove_action( 'template_redirect', array( 'Social_Login_Connect', 'init' ) );	
}

/**
 * Super Socializer Compatible
 */

if( isset( $_REQUEST['provider'] ) ){
	remove_action('parse_request', 'the_champ_connect');
	remove_action('init', 'the_champ_init');
}