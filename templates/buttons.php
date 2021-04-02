<?php /**
 * Template for the UM Social Login.
 * Used for the Social Login page.
 *
 * Caller: method Social_Login_Shortcode->load()
 * Shortcode: [ultimatemember_social_login]
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-social-login/buttons.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div class="um um-shortcode-social" id="um-shortcode-social-<?php echo esc_attr( $id ); ?>" style="padding:<?php echo esc_attr( $padding ); ?>;margin:<?php echo esc_attr( $margin ); ?>!important;">

	<div class="um-field">

		<div class="um-col-alt">

			<?php $i = 0;

			foreach ( $o_networks as $provider => $arr ) {
				$i++;
				$class = 'um-left';
				if ( $i % 2 == 0 ) {
					$class = 'um-right';
				} ?>

				<div <?php if ( $button_style == 'floated' ) { echo 'style="display:inline"'; } ?> class="<?php if ( $button_style == 'responsive' ) echo esc_attr( $class ) . ' um-half'; ?>">
					<a href="<?php echo esc_url( UM()->Social_Login_API()->hybridauth()->getConnectUrl( $provider, $id ) ); ?>" title="<?php echo esc_attr( $arr['button'] ); ?>"
					   class="um-button um-alt um-button-social um-button-<?php echo $provider; ?>" data-redirect-url="<?php echo esc_url( UM()->Social_Login_API()->hybridauth()->getCurrentUrl() ); ?>"  onclick="um_social_login_oauth_window( this.href,'authWindow', 'width=600,height=600,scrollbars=yes' );return false;">
					<?php if ( $show_icons ) { ?>
						<i class="<?php echo $arr['icon']; ?>" <?php if ( $show_labels ) { echo 'style="margin-right: 8px;"'; } ?>></i>
					<?php }

					if ( $show_labels ) { ?>
						<span><?php echo $arr['button']; ?></span>
					<?php } ?>
					</a>
				</div>

				<?php if ( $button_style == 'default' ) { ?>
					<div class="um-clear"></div>
				<?php }

				if ( $i % 2 == 0 && count( $o_networks ) != $i && $button_style == 'responsive' ) {
					echo '<div class="um-clear"></div></div><div class="um-col-alt um-col-alt-s">';
				}
			} ?>

			<div class="um-clear"></div>

		</div>

	</div>

	<style type="text/css">

		div#um-shortcode-social-<?php echo esc_attr( $id ); ?> div.um-field {padding: 0}

		div#um-shortcode-social-<?php echo esc_attr( $id ); ?> a.um-button.um-button-social {
			font-size: <?php echo ( $fontsize ) ? esc_attr( $fontsize ) : '15px'; ?>;
			padding: <?php echo ( ! empty( $button_padding ) ) ? esc_attr( $button_padding ) : '16px 20px'; ?> !important;
		}

		div#um-shortcode-social-<?php echo esc_attr( $id ); ?> a.um-button.um-button-social i {
			font-size: <?php echo ( $iconsize ) ? esc_attr( $iconsize ) : '18px'; ?>;
			width: <?php echo ( $iconsize ) ? esc_attr( $iconsize ) : '18px'; ?>;
			top: auto;
			vertical-align: baseline !important;
			margin-right: 0;
		}

		<?php if ( $button_style == 'responsive' ) { ?>

			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> div.um-field {margin:0 auto; max-width: <?php echo esc_attr( $container_max_width ); ?>}

		<?php }


		if ( $button_style == 'floated' ) { ?>

			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> a.um-button.um-button-social {
				display: inline-block !important;
				float: none !important;
				margin-right: 5px !important;
				margin-left: 5px !important;
				margin-bottom: 10px !important;
				width: auto;
				<?php if ( ! empty( $button_min_width ) ) { ?>
				min-width: <?php echo esc_attr( $button_min_width ); ?>;
				<?php } ?>
			}

			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> div.um-field {text-align: center}

		<?php }


		if ( $button_style == 'default' ) { ?>

			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> a.um-button.um-button-social {
				display: inline-block !important;
				float: none !important;
				margin-bottom: 10px !important;
				width: auto;
				<?php if ( ! empty( $button_min_width ) ) { ?>
				min-width: <?php echo esc_attr( $button_min_width ); ?>;
				<?php } ?>
			}

			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> div.um-field {text-align: center}

		<?php }

		foreach( $o_networks as $provider => $arr ) { ?>

			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> a.um-button.um-button-<?php echo esc_attr( $provider ); ?> {background-color: <?php echo esc_attr( $arr['bg'] ); ?>!important}
			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> a.um-button.um-button-<?php echo esc_attr( $provider ); ?>:hover {background-color: <?php echo esc_attr( $arr['bg_hover'] ); ?>!important}
			div#um-shortcode-social-<?php echo esc_attr( $id ); ?> a.um-button.um-button-<?php echo esc_attr( $provider ); ?> {color: <?php echo esc_attr( $arr['color'] ); ?>!important}

		<?php } ?>

	</style>

</div>