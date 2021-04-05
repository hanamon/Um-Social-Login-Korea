<?php
namespace um_ext\um_social_login\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Admin
 * @package um_ext\um_social_login\core
 */
class Social_Login_Admin {


	/**
	 * Social_Login_Admin constructor.
	 */
	function __construct() {
		add_action( 'um_extend_admin_menu',  array( &$this, 'extend_admin_menu' ), 100 );

		add_action( 'admin_menu', array(&$this, 'prepare_metabox' ), 20 );

		add_action( 'load-post.php', array(&$this, 'add_metabox' ), 9 );
		add_action( 'load-post-new.php', array(&$this, 'add_metabox' ), 9 );

		add_action( 'um_admin_field_edit_hook_sso_sync_value', array(&$this,'add_sso_sync_field'), 10, 3);
		add_filter( 'um_core_fields_hook',  array(&$this,'add_sso_sync_dropdown_field'), 10 );
	}

	/**
	 * Outputs Sync field
	 * @param string 	$attributes 
	 * @param integer 	$form_id    
	 * @param array 	$edit_array 
	 */
	function add_sso_sync_field( $attributes, $form_id = null, $edit_array = array() ){

		if ( isset( $_REQUEST['form_mode'] ) && 'register' !== $_REQUEST['form_mode'] ) {
			return;
		}

		$edit_mode_value = UM()->metabox()->edit_mode_value; ?>

		<p><label for="_sso_sync_value"><?php _e( 'Social Login - Sync Field', 'um-social-login' ) ?> <?php UM()->tooltip( __('Select which user info to populate this field from Social Account','ultimate-member' ) ); ?></label>
			<select name="_sso_sync_value" id="_sso_sync_value" style="width: 100%">
				<option value="none"  <?php selected( 'none', $edit_mode_value ); ?>>Not Set</option>
				<?php $sso_response_fields = UM()->Social_Login_API()->api_response_fields;
				foreach ( $sso_response_fields as $k => $v ) { ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k, $edit_mode_value ); ?>><?php echo esc_attr( $v );?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Add dropdown selection
	 * @param array $fields
	 */
	function add_sso_sync_dropdown_field( $fields ){

		foreach( $fields as $key => $value ){

			if( in_array( $key , array('checkbox','radio','date','multiselect','number','select','text','textarea','time','url','vimeo_video','youtube_video') ) ){
				$fields[ $key ]['col2'][ ] = '_sso_sync_value';
			}

		}

		return $fields;
	}


	/**
	 * Extends the admin menu
	 */
	function extend_admin_menu() {
	
		add_submenu_page( 'ultimatemember', __( 'Social Login', 'um-social-login' ), __( 'Social Login', 'um-social-login' ), 'manage_options', 'edit.php?post_type=um_social_login', '' );
	}


	/**
	 * Prepare metabox
	 */
	function prepare_metabox() {
		
		add_action( 'load-toplevel_page_ultimatemember', array( &$this, 'load_metabox' ) );
	}


	/**
	 * Load metabox
	 */
	function load_metabox() {
		wp_register_script('um-chart', '//www.gstatic.com/charts/loader.js');
		wp_enqueue_script('um-chart');

		add_meta_box('um-metaboxes-social', __( 'Social Signups', 'um-social-login' ), array( &$this, 'metabox_content' ), 'toplevel_page_ultimatemember', 'normal', 'core' );
	}


	/**
	 * Metabox content
	 */
	function metabox_content() {
		
		include_once um_social_login_path . 'includes/admin/templates/metabox.php';
	}


	/**
	 * Init the metaboxes
	 */
	function add_metabox() {
		global $current_screen;

		if ( $current_screen->id == 'um_form' ) {

			add_action( 'save_post', array( &$this, 'set_social_login_form_id' ), 10, 2 );

		} elseif( $current_screen->id == 'um_social_login' ) {

			add_action( 'add_meta_boxes', array(&$this, 'add_metabox_form'), 1 );
			add_action( 'save_post', array(&$this, 'save_metabox_form'), 11, 2 );

		}
	}


	/**
	 * Assign registration form as overlay fields
	 *
	 * @param $um_post_id
	 * @param $um_post
	 */
	function set_social_login_form_id( $um_post_id, $um_post ) {
		global $wpdb;

		if ( $um_post->post_type == 'um_form' ) {

			if ( isset( $_POST['form']['_um_social_login_form'] ) && $_POST['form']['_um_social_login_form'] > 0 ) {
				$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE post_id <> %d AND meta_key = %s ", $um_post_id, '_um_social_login_form' ) );
				update_option('um_social_login_form_installed', $um_post_id );
			} else {
				delete_post_meta( $um_post_id, '_um_social_login_form' );
			}

		}
	}


	/**
	 * Add form metabox
	 */
	function add_metabox_form() {

		add_meta_box('um-admin-social-login-buttons', __('Options','um-social-login'), array(&$this, 'load_metabox_form'), 'um_social_login', 'normal', 'default');
		add_meta_box('um-admin-social-login-shortcode', __('Shortcode','um-social-login'), array(&$this, 'load_metabox_form'), 'um_social_login', 'side', 'default');
	}


	/**
	 * Load a form metabox
	 *
	 * @param $object
	 * @param $box
	 */
	function load_metabox_form( $object, $box ) {
		$box['id'] = str_replace('um-admin-social-login-','', $box['id']);
		include_once um_social_login_path . 'includes/admin/templates/'. $box['id'] . '.php';
		wp_nonce_field( basename( __FILE__ ), 'um_admin_metabox_social_login_form_nonce' );
	}


	/**
	 * Save form metabox
	 *
	 * @param $um_post_id
	 * @param $um_post
	 *
	 * @return mixed
	 */
	function save_metabox_form( $um_post_id, $um_post ) {
		// validate nonce
		if ( ! isset( $_POST['um_admin_metabox_social_login_form_nonce'] ) || ! wp_verify_nonce( $_POST['um_admin_metabox_social_login_form_nonce'], basename( __FILE__ ) ) ) {
			return $um_post_id;
		}

		// validate post type
		if ( $um_post->post_type != 'um_social_login' ) {
			return $um_post_id;
		}

		// validate user
		$post_type = get_post_type_object( $um_post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $um_post_id ) ) {
			return $um_post_id;
		}

		// save
		foreach ( $_POST['social_login'] as $k => $v ) {
			if ( strstr( $k, '_um_' ) ) {
				update_post_meta( $um_post_id, $k, $v );
			}
		}

		return $um_post_id;
	}

}