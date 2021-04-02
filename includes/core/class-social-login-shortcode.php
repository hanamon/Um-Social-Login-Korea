<?php
namespace um_ext\um_social_login\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Shortcode
 * @package um_ext\um_social_login\core
 */
class Social_Login_Shortcode {


	/**
	 * Social_Login_Shortcode constructor.
	 */
	function __construct() {

		add_shortcode( 'ultimatemember_social_login', array( &$this, 'ultimatemember_social_login' ) );
		add_filter( 'um_registration_user_role', array( &$this, 'change_registration_role' ), 10, 2 );

	}


	/**
	 * Social Login Shortcode
	 *
	 * @param array $args
	 * @return string
	 */
	function ultimatemember_social_login( $args = array() ) {
		wp_enqueue_script( 'um-social-login' );
		wp_enqueue_style( 'um-social-login' );

		$key = wp_generate_password( 5 , false );

		$sso_session = UM()->Social_Login_API()->hybridauth()->getSession();
	
		if ( ! um_is_core_page( 'login' ) || ! $sso_session->get('_um_shortcode_id') ) {
			$sso_session->set('_um_shortcode_id',$key );
			foreach ( $_SESSION as $k => $value ) {
				if ( strpos( $k, '_um_social_login_key_' ) === 0 ) {
					$sso_session->set( $k , null );
				}
			}
		}
			$sso_session->set( '_um_social_login_key_' . $key, $args['id'] );

		$redirect_url = UM()->permalinks()->get_current_url();
		$redirect_url = remove_query_arg( array( 'code' , 'state' ), $redirect_url );
		$sso_session->set('um_social_login_redirect',$redirect_url );
		$sso_session->set('um_social_login_rememberme',get_post_meta( $args['id'], '_um_keep_signed_in', true ) );

		UM()->Social_Login_API()->shortcode_id = $key;

		return $this->load( $args );
	}


	/**
	 * Get shortcode post meta
	 *
	 * @param $id
	 * @return mixed
	 */
	function get_meta( $id ) {
		$array = array();

		$meta = get_post_custom( $id );
		if ( $meta && is_array( $meta ) ) {
			foreach ( $meta as $k => $v ) {
				$k = str_replace( '_um_', '', $k );
				$array[ $k ] = $v[0];
			}
		}

		return $array;
	}


	/**
	 * Load a module with global function
	 *
	 * @param $args
	 * @return string
	 */
	function load( $args ) {
		$networks = apply_filters( 'um_social_login_networks_output', UM()->Social_Login_API()->available_networks() );
		$postmeta = $this->get_meta( $args['id'] );

		foreach ( $networks as $provider => $arr ) {
			if ( isset( $postmeta[ 'enable_'.$provider ][0] ) && $postmeta[ 'enable_'.$provider ][0] != 1 ) {
				unset( $networks[ $provider ] );
			}
		}

		if ( ! $networks ) {
			return '';
		}

		$defaults = array();

		$args = wp_parse_args( $args, $defaults );
		$args = array_merge( $args, $postmeta );

		/**
		 * @var $show_for_members
		 */
		extract( $args, EXTR_SKIP );

		if ( ! $show_for_members && is_user_logged_in() ) {
			return '';
		}

		$t_args = array_merge( $args, array( 'o_networks' => $networks ) );
		$output = UM()->get_template( 'buttons.php', um_social_login_plugin, $t_args );

		return $output;
	}


	/**
	 * Additional arguments for user registration
	 *
	 * @param string $role
	 * @param array $args
	 * @return string
	 */
	function change_registration_role( $role, $args ) {

		$sso_session = UM()->Social_Login_API()->hybridauth()->getSession();
		$shortcode_id = sanitize_key( $sso_session->get("um_sso") );
		if ( $shortcode_id && isset( $_REQUEST['return_provider'] ) ) {
			$assigned_role = get_post_meta( intval( $shortcode_id ), '_um_assigned_role', true );
			
			if ( ! empty( $assigned_role ) ) {
				$role = $assigned_role;
			}
			
		}
		
		return $role;
	}
}