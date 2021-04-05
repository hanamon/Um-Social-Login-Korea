<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">

	<?php UM()->admin_forms( array(
		'class'		=> 'um-form-register-social um-top-label',
		'prefix_id'	=> 'form',
		'fields' => array(
			array(
				'id'		    => '_um_social_login_form',
				'type'		    => 'select',
				'label'    		=> __( 'Use this form in the overlay?', 'um-social-login' ),
				'tooltip'    	=> __( 'Please note only one registration form can be used for social overlay and this form should only be used for completion of social registration and not regular registration', 'um-social-login' ),
				'value' 		=> UM()->query()->get_meta_value( '_um_social_login_form', null, 0 ),
				'options' 		=> array(
					'0'	=>	__( 'No', 'um-social-login' ),
					'1'	=>	__( 'Yes', 'um-social-login' )
				),
			),
			array(
				'id'		    => '_um_register_show_social',
				'type'		    => 'select',
				'label'    		=> __( 'Show social connect on this form?', 'um-social-login' ),
				'value' 		=> UM()->query()->get_meta_value( '_um_register_show_social', null, 1 ),
				'options' 		=> array(
					'0'	=>	__('No','um-social-login'),
					'1'	=>	__('Yes','um-social-login')
				),
				'conditional'	=> array( '_um_social_login_form', '=', '0' )
			),
			array(
				'id'		    => '_um_register_show_social_2steps',
				'type'		    => 'select',
				'label'    		=> __( 'Use Two-step Registration', 'um-social-login' ),
				'value' 		=> UM()->query()->get_meta_value( '_um_register_show_social_2steps', null, 1 ),
				'tooltip'		=> __('Two-step registration allows users to check the field values from Social Network APIs before they can submit the register form. One-step Registration submits the details automatically after authenticating Social Network accounts.','um-social-login'),
				'options' 		=> array(
					'1'	=>	__('Yes','um-social-login'),
					'0'	=>	__('No - I want One-step process','um-social-login'),
				),
				'conditional'	=> array( '_um_social_login_form', '=', '1' )
			),
			array(
				'id'		    => '_um_register_1step_link_matched_email',
				'type'		    => 'select',
				'label'    		=> __( 'When Social Account\'s Email is already registered', 'um-social-login' ),
				'value' 		=> UM()->query()->get_meta_value( '_um_register_1step_link_matched_email', null, 1 ),
				'tooltip'		=> __('','um-social-login'),
				'options' 		=> array(
					'1'	=>	__('Link Accounts & Login immediately','um-social-login'),
					'2'	=>	__('Link Accounts & Redirect to Login page','um-social-login'),
					'3'	=>	__('Allow new account creation with a generated Email','um-social-login'),
					'4'	=>	__('Do not link accounts and prevent from account creation','um-social-login'),
				),
				'conditional'	=> array( '_um_register_show_social_2steps', '=', '0' )
			),
			array(
				'id'		    => '_um_register_show_flash_screen',
				'type'		    => 'select',
				'label'    		=> __( 'Show splash screen', 'um-social-login' ),
				'value' 		=> UM()->query()->get_meta_value( '_um_register_show_flash_screen', null, 1 ),
				'tooltip'		=> __('Add content to social overlay with a content block in the form builder.','um-social-login'),
				'options' 		=> array(
					'1'	=>	__('Yes','um-social-login'),
					'0'	=>	__('No','um-social-login'),
				),
				'conditional'	=> array( '_um_register_show_social_2steps', '=', '0'  )
			),
		)
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>