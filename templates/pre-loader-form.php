<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php 
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
	<center><img src="<?php echo esc_url( um_url."/assets/img/loading.gif" ); ?>" /></center>
	
</div>