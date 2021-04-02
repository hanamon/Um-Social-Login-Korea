<?php
/**
 * Template for the UM Social Login.
 * Used for the after registration overlay form.
 *
 * Caller: method UM_Social_Login_API->load_template()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-login/form.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $wp;
$current_url = home_url( add_query_arg( array(), $wp->request ) );

?>

<div class="um-social-login-overlay">
	<a href="<?php echo esc_url( $current_url ); ?>" class="um-social-login-cancel">
		<i class="um-icon-ios-close-empty"></i>
	</a>
</div>

<div class="um-social-login-wrap">
	<?php echo do_shortcode('[ultimatemember form_id="' . $form_id . '" /]'); ?>
</div>