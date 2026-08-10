<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Modal
	$opt_name,
	array(
		'title'			=> esc_html__( 'Modal', 'bijan' ),
		'id'			=> 'auth-modal-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // auth-modal
				'id'		=> 'auth-modal',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Authentication modal', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'desc'		=> esc_html__( 'If you want to use third-party authentication plugins(Like: Digits), disable this.', 'bijan' ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
			[ // auth_sms
				'id'		=> 'auth_sms',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Authentication by SMS', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'desc'		=> esc_html__( 'Enable or disable the Authentication by SMS.', 'bijan' ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
				'required'		=> [
					['auth-modal','=',true],
					['sms','=',true]
				],
			],
			[ // show-auth-modal-logo
				'id'		=> 'show-auth-modal-logo',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show logo in the header', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['auth-modal','=',true],
				],
			],
			[ // auth-modal-logo-type
				'id'		=> 'auth-modal-logo-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Logo type', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Image', 'bijan' ) ),
				'data'		=> [
					'text'	=> esc_html__( 'Text', 'bijan' ),
					'img'	=> esc_html__( 'Image', 'bijan' ),
				],
				'default'	=> 'img',
				'required'	=> [
					['auth-modal','=',true],
					['show-auth-modal-logo','=',true],
				]
			],
			[ // auth-modal-logo-text-type
				'id'		=> 'auth-modal-logo-text-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Text type', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Site title', 'bijan' ) ),
				'data'		=> [
					'title'		=> esc_html__( 'Site title', 'bijan' ),
					'custom'	=> esc_html__( 'Custom', 'bijan' ),
				],
				'default'	=> 'title',
				'required'	=> [
					['auth-modal','=',true],
					['show-auth-modal-logo','=',true],
					['auth-modal-logo-type','=','text'],
				],
			],
			[ // auth-modal-logo-text-custom
				'id'		=> 'auth-modal-logo-text-custom',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Logo text', 'bijan' ),
				'required'	=> [
					['auth-modal','=',true],
					['show-auth-modal-logo','=',true],
					['auth-modal-logo-type','=','text'],
					['auth-modal-logo-text-type','=','custom'],
				],
			],
			[ // auth-modal-logo-img
				'id'				=> 'auth-modal-logo-img',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Logo image file', 'bijan' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'default'			=> [
					'url'	=> BIJAN_URI . "assets/img/logo.svg",
				],
				'required'			=> [
					['auth-modal','=',true],
					['show-auth-modal-logo','=',true],
					['auth-modal-logo-type','=','img'],
				],
			],
			[ // auth-modal-logo-img-size
				'id'		=> 'auth-modal-logo-img-size',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Logo size', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), 'W: 160 & H: 60' ),
				'desc'		=> esc_html__( 'Leave empty for full size', 'bijan' ),
				'default'	=> [
					'width'		=> 160,
					'height'	=> 60
				],
				'required'	=> [
					['auth-modal','=',true],
					['show-auth-modal-logo','=',true],
					['auth-modal-logo-type','=','img'],
				],
			],
			[ // auth-modal-logo-link
				'id'			=> 'auth-modal-logo-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Logo URL', 'bijan' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'bijan' ), home_url() ),
				'validate'		=> ['url'],
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['auth-modal','=',true],
					['show-auth-modal-logo','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Email Auth
	$opt_name,
	array(
		'title'			=> esc_html__( 'Email/Username', 'bijan' ),
		'id'			=> 'auth-email-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // auth-email
				'id'		=> 'auth-email',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Authentication with email/username', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
		),
	)
);

Redux::set_section( // Lost password
	$opt_name,
	array(
		'title'			=> esc_html__( 'Lost password', 'bijan' ),
		'id'			=> 'auth-modal-lost-password-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // lost-password-email-subject
				'id'		=> 'lost-password-email-subject',
				'type'		=> 'text',
				'title'		=> __( "Lost password email subject", 'bijan' ),
				'required'	=> [
					['auth-modal','=',true],
					['auth-email','=',true],
				]
			],
			[ // lost-password-email-template
				'id'		=> 'lost-password-email-template',
				'type'		=> 'editor',
				'title'		=> __( "Lost password email template", 'bijan' ),
				'subtitle'	=> __( "The email structure does not support everything, and the email that is sent may differ from the content you have provided.<br>Use {password} to display the new password.", 'bijan' ),
				'required'	=> [
					['auth-modal','=',true],
					['auth-email','=',true],
				]
			],
		)
	)
);