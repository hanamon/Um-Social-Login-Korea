<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED_Social_Login_Connect
 *
 */
class UM_myCRED_Social_Login_Connect extends myCRED_Hook {

	/**
	 * @var array
	 */
	var $um_hooks = array();


	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = UM()->Social_Login_API()->available_networks();

		$arr_defaults = array();

		foreach ( $this->um_hooks as $provider => $network ) {
			$this->um_hooks[ $provider ]['title'] = $network['name'];

			$arr_defaults[ $provider ] = array(
				'creds'     => 1,
				'log'       => sprintf( __( '%%plural%% for connecting %s account.', 'um-social-login' ), $network['name'] ),
				'limit'     => '0/x',
				'provider'  => $provider,
				'um_hook'   => $provider,
			);

			$arr_defaults[ $provider ] = apply_filters( 'um_mycred_hook_defaults', $arr_defaults[ $provider ], $provider, $network, $this->um_hooks );

		}

		parent::__construct( array(
			'id'       => 'um-mycred-social-login-connect',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );

	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		add_action( 'um_social_login_after_connect', array( $this, 'user_connects_social_network' ), 10, 2 );
	}


	/**
	 * Check if the user qualifies for points
	 *
	 * @param $provider
	 * @param $user_id
	 */
	public function user_connects_social_network( $provider, $user_id ) {
		if ( $this->prefs[ $provider ]['creds'] == 0 ) {
			return;
		}


		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( $provider, 'um-mycred-social-login-connect', $user_id ) ) {
			return;
		}

		$data = apply_filters( 'um_mycred_hooks_data', '', $provider, $this->prefs );

		// Execute
		$this->core->add_creds(
			'um-mycred-social-login-connect',
			$user_id,
			$this->prefs[ $provider ]['creds'],
			$this->prefs[ $provider ]['log'],
			0,
			$data,
			$this->mycred_type
		);
	}

	/**
	 * Add Settings
	 */
	public function preferences() {
		UM()->myCRED()->hooks()->build_hook_widget( $this );
	}


	/**
	 * Sanitize Preferences
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function sanitise_preferences( $data ) {
		return UM()->myCRED()->hooks()->sanitise_preferences( $data, $this );
	}
}


/**
 * Class UM_myCRED_Social_Login_Disconnect
 */
class UM_myCRED_Social_Login_Disconnect extends myCRED_Hook {

	/**
	 * @var array
	 */
	var $um_hooks = array();


	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = UM()->Social_Login_API()->available_networks();

		$arr_defaults = array();

		foreach ( $this->um_hooks as $provider => $network ) {

			$this->um_hooks[ $provider ]['title'] = $network['name'];

			$this->um_hooks[ $provider ]['deduct'] = true;

			$arr_defaults[ $provider ] = array(
				'creds'     => 1,
				'log'       => sprintf( __( '%%plural%% for disconnecting %s account.', 'um-social-login' ), $network['name'] ),
				'limit'     => '0/x',
				'provider'  => $provider,
				'um_hook'   => $provider,
			);

			$arr_defaults[ $provider ] = apply_filters( 'um_mycred_hook_defaults', $arr_defaults[ $provider ], $provider, $network, $this->um_hooks );

		}

		parent::__construct( array(
			'id'       => 'um-mycred-social-login-disconnect',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		add_action( 'um_social_login_after_disconnect', array( $this, 'user_disconnects_social_network' ), 10, 2 );
	}


	/**
	 * Check if the user qualifies for points
	 *
	 * @param $provider
	 * @param $user_id
	 */
	public function user_disconnects_social_network( $provider, $user_id ) {
		if ( $this->prefs[ $provider ]['creds'] == 0 ) {
			return;
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( $provider, 'um-mycred-social-login-connect', $user_id ) ) {
			return;
		}

		$data = apply_filters( 'um_mycred_hooks_data', '', $provider, $this->prefs );

		// Execute
		mycred_subtract(
			'um-mycred-social-login-connect',
			$user_id, 
			$this->prefs[ $provider ]['creds'], 
			$this->prefs[ $provider ]['log'],
			0,
			$data,
			$this->mycred_type
		);
	}


	/**
	 * Add Settings
	 */
	public function preferences() {
		UM()->myCRED()->hooks()->build_hook_widget( $this );
	}


	/**
	 * Sanitize Preferences
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function sanitise_preferences( $data ) {
		return UM()->myCRED()->hooks()->sanitise_preferences( $data, $this );
	}
}