<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Social_Login_API
 */
class UM_Social_Login_API {


	/**
	 * @var
	 *
	 * @since  2.0
	 */
	private static $instance;


	/**
	 * @var array
 *
	 * @since  2.0
	 */
	var $networks;

	/**
	 * @var array
	 *
	 * @since  2.2
	 */
	var $api_response_fields;


	/**
	 * @return UM_Social_Login_API
	 *
	 * @since  2.0
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Social_Login_API constructor
	 *
	 * @since  2.0
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_social_login'] = $this;

		add_filter( 'um_call_object_Social_Login_API', array( &$this, 'get_this' ) );

		$this->init_networks();

		$this->api_response_fields();

		$this->show_overlay = false;

		$this->shortcode_id = false;

		$this->user_connect();

		$this->user_disconnect();
		
		$this->admin();

		$this->enqueue();

		$this->shortcode();

		$this->ajax();
		
		$this->hybridauth();

		add_action( 'plugins_loaded', array( &$this, 'init' ) );
 
		add_filter( 'query_vars', array( &$this, 'query_vars' ), 10, 1 );

		add_action( 'init',  array( &$this, 'create_taxonomies' ), 2 );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		add_action( 'um_mycred_load_hooks', array( &$this, 'um_mycred_social_login_hooks' ) );

		add_action( 'wp_logout', array( &$this, 'um_clear_session_after_logout' ) );

		add_action( 'wp_ajax_um_social_login_change_photo', array( $this->ajax(), 'ajax_change_photo' ) );

		add_filter( 'um_custom_error_message_handler', array( &$this, 'error_message_handler' ), 99999, 2 );
	}


	/**
	 * Social network members data
	 * @return array
	 *
	 * @since  2.2
	 */
	function api_response_fields(){

		$this->api_response_fields = array(
			'identifier' => 'ID',
			'profileURL' => 'Profile URL',
			'webSiteURL' => 'Website URL',
			'photoURL' => 'Photo URL',
			'displayName' => 'Display Name',
			'description' => 'Description',
			'firstName' => 'First Name',
			'lastName' => 'Last Name',
			'gender' => 'Gender',
			'language' => 'Language',
			'age' => 'Age',
			'birthDay' => 'Birth Day',
			'birthMonth' => 'Birth Month',
			'birthYear' => 'Birth Year',
			'email' => 'Email Address',
			'emailVerified' => 'Email Verified',
			'phone' => 'Phone Number',
			'address' => 'Address',
			'country' => 'Country',
			'region' => 'Region',
			'city' => 'City',
			'zip' => 'Zip',
			'extend' => 'Extended Data'
			
		);
	}

	/**
	 * @param $action
	 *
	 *
	 * @since  2.0
	 */
	function um_mycred_social_login_hooks( $action ) {
		$this->init_networks();
		require_once um_social_login_path . 'includes/core/hooks/um-mycred-social-login.php';
	}


	/**
	 * Init network filter
	 *
	 * @since  2.0
	 */
	function init_networks() {

		require_once um_social_login_path . 'includes/core/filters/um-social-login-providers.php';
		
		$this->networks = apply_filters( 'um_social_login_networks', $this->networks );
	}


	/**
	 * Default settings
	 * 
	 * @param $defaults
	 *
	 * @return array
	 *
	 * @since  2.0
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * Create a post type
	 *
	 * @since  2.0
	 */
	function create_taxonomies() {

		register_post_type( 'um_social_login', array(
				'labels' => array(
					'name' => __( 'Social Login Shortcodes' ),
					'singular_name' => __( 'Social Login Shortcode' ),
					'add_new' => __( 'Add New' ),
					'add_new_item' => __('Add New Social Login Shortcode' ),
					'edit_item' => __('Edit'),
					'not_found' => __('You did not create any social login shortcodes yet'),
					'not_found_in_trash' => __('Nothing found in Trash'),
					'search_items' => __('Search social login shortcodes')
				),
				'show_ui' => true,
				'show_in_menu' => false,
				'public' => false,
				'supports' => array('title'),
				'capability_type' => 'page'
			)
		);

	}


	/**
	 * @return $this
	 *
	 * @since  2.0
	 */
	function get_this() {
		return $this;
	}

	/**
	 * @return um_ext\um_social_login\core\Social_Login_Setup()
	 *
	 * @since  2.0
	 */
	function setup() {
		if ( empty( UM()->classes['um_social_login_setup'] ) ) {
			UM()->classes['um_social_login_setup'] = new um_ext\um_social_login\core\Social_Login_Setup();
		}
		return UM()->classes['um_social_login_setup'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Admin()
	 *
	 * @since  2.0
	 */
	function admin() {
		if ( empty( UM()->classes['um_social_login_admin'] ) ) {
			UM()->classes['um_social_login_admin'] = new um_ext\um_social_login\core\Social_Login_Admin();
		}
		return UM()->classes['um_social_login_admin'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_social_login_enqueue'] ) ) {
			UM()->classes['um_social_login_enqueue'] = new um_ext\um_social_login\core\Social_Login_Enqueue();
		}
		return UM()->classes['um_social_login_enqueue'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Shortcode()
	 *
	 * @since  2.0
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_social_login_shortcode'] ) ) {
			UM()->classes['um_social_login_shortcode'] = new um_ext\um_social_login\core\Social_Login_Shortcode();
		}
		return UM()->classes['um_social_login_shortcode'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Ajax()
	 *
	 * @since  2.0
	 */
	function ajax() {
		if ( empty( UM()->classes['um_social_login_ajax'] ) ) {
			UM()->classes['um_social_login_ajax'] = new um_ext\um_social_login\core\Social_Login_Ajax();
		}
		return UM()->classes['um_social_login_ajax'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Hybridauth()
	 *
	 * @since  2.2
	 */
	function hybridauth() {
		if ( empty( UM()->classes['um_social_login_hybridauth'] ) ) {
			UM()->classes['um_social_login_hybridauth'] = new um_ext\um_social_login\core\Social_Login_Hybridauth();
		}
		return UM()->classes['um_social_login_hybridauth'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Connect()
	 *
	 * @since  2.2
	 */
	function user_connect() {
		if ( empty( UM()->classes['um_social_login_connect'] ) ) {
			UM()->classes['um_social_login_connect'] = new um_ext\um_social_login\core\Social_Login_Connect();
		}
		return UM()->classes['um_social_login_connect'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Disconnect()
	 *
	 * @since  2.2
	 */
	function user_disconnect() {
		if ( empty( UM()->classes['um_social_login_disconnect'] ) ) {
			UM()->classes['um_social_login_disconnect'] = new um_ext\um_social_login\core\Social_Login_Disconnect();
		}
		return UM()->classes['um_social_login_disconnect'];
	}


	/**
	 * @return um_ext\um_social_login\core\Social_Login_Users()
	 *
	 * @since  2.2
	 */
	function user( $user_id = null ) {
		if ( empty( UM()->classes['um_social_login_users'] ) ) {
			UM()->classes['um_social_login_users'] = new um_ext\um_social_login\core\Social_Login_Users( $user_id );
		}
		return UM()->classes['um_social_login_users'];
	}


	/**
	 * Session start
	 * 
	 * @uses um_is_session_started
	 *
	 * @since  2.0
	 */
	function session_start() {

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		if ( um_is_session_started() === false ) {
			@session_start();
		}
	}


	/**
	 * Init
	 *
	 * @uses session_start
	 *
	 * @since  2.0
	 */
	function init() {


		// // Actions
		require_once um_social_login_path . 'includes/core/actions/um-social-login-form.php';
		require_once um_social_login_path . 'includes/core/actions/um-social-login-admin.php';
		require_once um_social_login_path . 'includes/core/actions/um-social-login-connect.php';
		require_once um_social_login_path . 'includes/core/actions/um-social-login-compatibility.php';

		// // Filters
		require_once um_social_login_path . 'includes/core/filters/um-social-login-settings.php';
		require_once um_social_login_path . 'includes/core/filters/um-social-login-account.php';
		require_once um_social_login_path . 'includes/core/filters/um-social-login-profile.php';
		require_once um_social_login_path . 'includes/core/filters/um-social-login-connect.php';
		require_once um_social_login_path . 'includes/core/filters/um-social-login-notifications.php';


		
	}


	/**
	 * Available networks
	 *
	 * @return mixed
	 *
	 * @since  2.0
	 */
	function available_networks() {

		$networks = apply_filters( 'um_social_login_networks', $this->networks );
		foreach ( $networks as $id => $arr ) {

			if ( 0 == UM()->options()->get( 'enable_' . $id ) ) {
				unset( $networks[ $id ] );
			}
		}

		$this->networks = $networks;
		
		return $networks;
	}


	/**
	 * Network Icons
	 * @param  string $provider 
	 *
	 * @since  2.2
	 */
	function get_network_icon( $provider = '' ){

		$networks = $this->available_networks();

		if( isset( $networks[ $provider ]['icon'] ) ){

			return "<i class=\"".$networks[ $provider ]['icon']."\" style=\"float: left;margin-left: -40px;margin-top: 30px;\"></i>";

		}

	}

	/**
	 * Available networks with single callback urls
	 * @return array
	 *
	 * @since  2.2
	 */
	function get_single_callback_networks(){

		$networks = $this->available_networks();
		$arr_networks = array();

		foreach( $networks as $provider => $options ){
			if( isset( $options['has_single_callback'] ) ){
				$arr_networks[ $provider ] = $options;
			}
		}

		return $arr_networks;
	}


	/**
	 * Number of connected users
	 *
	 * @param $id
	 *
	 * @return int
	 *
	 * @since  2.0
	 */
	function count_users( $id ) {
		$args = array( 'fields' => 'ID', 'number' => 0 );

		$args['meta_query'][] = array(
			array(
				'key' => '_uid_' . $id,
				'value' => '',
				'compare' => '!='
			)
		);
		$users = new WP_User_Query( $args );

		return count( $users->results );
	}


	/**
	 * Set Redirect from sessions
	 * 
	 * @return array
	 *
	 * @since  2.0
	 */
	public function redirect() {
		if ( isset( $_SESSION['um_social_login_redirect'] ) ) {

			$redirect_to = $_SESSION['um_social_login_redirect'];
			$is_shortcode = $_SESSION['um_sso'];

			unset( $_SESSION['um_social_login_redirect'] );
			//unset( $_SESSION['um_sso'] );

			return array( 'has_redirect' => true, 'redirect_to' => $redirect_to, 'is_shortcode' => $is_shortcode );
		}

		return array( 'has_redirect' => false, 'redirect_to' => '', 'is_shortcode' => false );
	}

	/**
	 * Get submit button on form
	 *
	 * @since  2.0
	 */
	function show_submit_button() {
		?>

		<div class="um-col-alt">

			<input type="hidden" name="_social_login_form" id="_social_login_form" value="true" />

			<div class="um-center"><input type="submit" value="<?php _e('Complete Registration','um-social-login'); ?>" class="um-button" /></div>

			<div class="um-clear"></div>

		</div>

		<?php
	}


	/**
	 * Get form id
	 *
	 * @return int
	 *
	 * @since  2.0
	 */
	function form_id() {
		return get_option( 'um_social_login_form_installed' );
	}


	/**
	 * Modify global query vars
	 *
	 * @param $public_query_vars
	 *
	 * @return array
	 *
	 * @since  2.0
	 */
	function query_vars( $public_query_vars ) {
		$public_query_vars[] = 'state';
		$public_query_vars[] = 'code';
		$public_query_vars[] = 'provider';

		return $public_query_vars;
	}


	/**
	 *  Clear session after user logout
	 *
	 * @since  2.0 
	 */
	function um_clear_session_after_logout() {
		unset( $_COOKIE['PHPSESSID'] );
		setcookie( 'PHPSESSID', null, -1, '/' );
	}


	/**
	 * Error Message handler
	 * 
	 * @param string $err
	 * @param $error_code
	 *
	 * @return string
	 *
	 * @since  2.0
	 */
	function error_message_handler( $err, $error_code ) {
       switch ( $error_code ) {
			case 'um_social_user_denied':
					return __( 'We were unable to request application access permissions.', 'um-social-login' );
				break;

			case 'um_social_unauthorized_scope_error':
					return __( 'One of the scopes is not authorized by your developer application.', 'um-social-login' );
				break;

			case 'um_sso_not_linked':
                   return sprintf(__( 'Your social account is not yet linked to the site. Please login below with your username and password or <a href="%s">click here</a> to register with your social account..', 'um-social-login' ), um_get_core_page("register") );
				break;

			case 'um_sso_already_linked':
					return __( 'Your social account is already linked to the site.', 'um-social-login' );
				break;

			case 'um_sso_email_already_linked':
					return __( 'You\'re not allowed to create a new account with the email address added to your social account.  Please login and link your social account in the Account page.', 'um-social-login' );
				break;

		}

		return $err;
	}

	/**
	 * Generate unique username
	 * @param  string $username 
	 * @return string
	 *
	 * @since  2.2
	 */
	function generate_unique_username( $username ) {

		$username = sanitize_user( $username );

		static $i;
		if ( null === $i ) {
			$i = 1;
		} else {
			$i ++;
		}
		if ( ! username_exists( $username ) ) {
			return $username;
		}
		$new_username = sprintf( '%s-%s', $username, $i );
		if ( ! username_exists( $new_username ) ) {
			return $new_username;
		} else {
			return call_user_func( array( $this, __FUNCTION__ ), $username );
		}
	}

}

/**
 * Create class variable
 * @since  2.0
 */
function um_init_social_login() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Social_Login_API', true );
	}
}
add_action( 'plugins_loaded', 'um_init_social_login', -10, 1 );
