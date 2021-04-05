<?php
namespace um_ext\um_social_login\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Enqueue
 * @package um_ext\um_social_login\core
 */
class Social_Login_Enqueue {


	/**
	 * @var string
	 */
	var $suffix;


	/**
	 * Social_Login_Enqueue constructor.
	 */
	function __construct() {
		$this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 9 );
	
	}


	/**
	 * Frontend enqueue scripts
	 */
	function wp_enqueue_scripts() {
		wp_register_script( 'um-social-login', um_social_login_url . 'assets/js/um-social-connect' . $this->suffix . '.js', array(), um_social_login_version, true );
		wp_register_style( 'um-social-login', um_social_login_url . 'assets/css/um-social-connect' . $this->suffix . '.css', array(), um_social_login_version );

	}

}