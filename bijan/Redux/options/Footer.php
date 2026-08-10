<?php

use Bijan\Utils;

defined( 'ABSPATH' ) || exit;

$markets = Utils::app_markets();
unset( $markets['custom'] );

Redux::set_section( // Footer section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Footer', 'bijan' ),
		'id'			=> 'footer-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_footer
				'id'		=> 'show_footer',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Footer status', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // footer_bg
				'id'			=> 'footer_bg',
				'type'			=> 'background',
				'title'			=> __( 'Footer background', 'bijan' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> [
					'background-color'	=> '#363636'
				],
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_menu_count
				'id'			=> 'footer_menu_count',
				'type'			=> 'spinner',
				'title'			=> __( 'Footer menus count', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 2 ),
				'default'		=> 2,
				'min'			=> 0,
				'max'			=> 3
			],
		),
	)
);

Redux::set_section( // Newsletter section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Newsletter', 'bijan' ),
		'id'			=> 'footer-newsletter-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_newsletter
				'id'		=> 'footer_show_newsletter',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show newsletter section', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true]
				]
			],
			[ // footer_newsletter_icon
				'id'			=> 'footer_newsletter_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-message-text' ),
				'default'		=> 'bijan-icon-message-text',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_footer','=',true],
					['footer_show_newsletter','=',true]
				],
			],
			[ // footer_newsletter_title
				'id'		=> 'footer_newsletter_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Newsletter title', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Subscribe to newsletter', 'bijan' ) ),
				'default'	=> __( 'Subscribe to newsletter', 'bijan' ),
				'required'		=> [
					['show_footer','=',true],
					['footer_show_newsletter','=',true]
				],
			],
			[ // footer_newsletter_subtitle
				'id'		=> 'footer_newsletter_subtitle',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Newsletter subtitle', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Get the latest notifications', 'bijan' ) ),
				'default'	=> __( 'Get the latest notifications', 'bijan' ),
				'required'		=> [
					['show_footer','=',true],
					['footer_show_newsletter','=',true]
				],
			],
			[ // footer_newsletter_shortcode
				'id'		=> 'footer_newsletter_shortcode',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Newsletter shortcode', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), "[newsletter_form]" ),
				'default'	=> '[newsletter_form]',
				'required'	=> [
					['show_footer','=',true],
					['footer_show_newsletter','=',true]
				],
			],
			[ // footer_newsletter_sms_shortcode
				'id'		=> 'footer_newsletter_sms_shortcode',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Newsletter SMS shortcode', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), "[contact-form-7]" ),
				'default'	=> '[contact-form-7]',
				'required'	=> [
					['show_footer','=',true],
					['footer_show_newsletter','=',true]
				],
			],
		),
	)
);

Redux::set_section( // Menu 1 section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Menu 1', 'bijan' ),
		'id'			=> 'footer-menu-1-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_menu1
				'id'		=> 'footer_show_menu1',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show menu', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true],
					['footer_menu_count', '>=', 1],
				]
			],
			[ // footer_menu1_icon
				'id'			=> 'footer_menu1_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '<i class="bijan-icon-grid"></i>' ),
				'default'		=> 'bijan-icon-grid',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_footer','=',true],
					['footer_show_menu1','=',true],
					['footer_menu_count', '>=', 1],
				],
			],
			[ // footer_menu1_title
				'id'		=> 'footer_menu1_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Menu title', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Menu', 'bijan' ) ),
				'default'	=> __( 'Menu', 'bijan' ),
				'required'		=> [
					['show_footer','=',true],
					['footer_show_menu1','=',true],
					['footer_menu_count', '>=', 1],
				],
			]
		),
	)
);

Redux::set_section( // Menu 2 section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Menu 2', 'bijan' ),
		'id'			=> 'footer-menu-2-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_menu2
				'id'		=> 'footer_show_menu2',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show menu', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true],
					['footer_menu_count', '>=', 2],
				]
			],
			[ // footer_menu2_icon
				'id'			=> 'footer_menu2_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '<i class="bijan-icon-call"></i>' ),
				'default'		=> 'bijan-icon-call',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_footer','=',true],
					['footer_show_menu2','=',true],
					['footer_menu_count', '>=', 2],
				],
			],
			[ // footer_menu2_title
				'id'		=> 'footer_menu2_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Menu title', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Contact us', 'bijan' ) ),
				'default'	=> __( 'Contact us', 'bijan' ),
				'required'		=> [
					['show_footer','=',true],
					['footer_show_menu2','=',true],
					['footer_menu_count', '>=', 2],
				],
			]
		),
	)
);

Redux::set_section( // Menu 3 section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Menu 3', 'bijan' ),
		'id'			=> 'footer-menu-3-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_show_menu3
				'id'		=> 'footer_show_menu3',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show menu', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_footer','=',true],
					['footer_menu_count', '>=', 3],
				]
			],
			[ // footer_menu3_icon
				'id'			=> 'footer_menu3_icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '<i class="bijan-icon-grid"></i>' ),
				'default'		=> 'bijan-icon-call',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_footer','=',true],
					['footer_show_menu3','=',true],
					['footer_menu_count', '>=', 3],
				],
			],
			[ // footer_menu3_title
				'id'		=> 'footer_menu3_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Menu title', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Contact us', 'bijan' ) ),
				'default'	=> __( 'Contact us', 'bijan' ),
				'required'		=> [
					['show_footer','=',true],
					['footer_show_menu3','=',true],
					['footer_menu_count', '>=', 3],
				],
			]
		),
	)
);

Redux::set_section( // About section
	$opt_name,
	array(
		'title'			=> esc_html__( 'About', 'bijan' ),
		'id'			=> 'footer-about-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_about
				'id'		=> 'footer_about',
				'type'		=> 'editor',
				'title'		=> esc_html__( "About text", 'bijan' ),
				'subtitle'	=> sprintf( __( "Default:<br>%s", 'bijan' ), __( "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.", 'bijan' ) ),
				'default'	=> __( "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.", 'bijan' ),
				'args'		=> [
					'media_buttons'	=> false,
				],
				'required'	=> [
					['show_footer','=',true],
				]
			],
		),
	)
);

Redux::set_section( // Organizations Logo section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Organizations Logo', 'bijan' ),
		'id'			=> 'footer-org-logos-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_footer_org_logos
				'id'		=> 'show_footer_org_logos',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Organizations Logo', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // footer_org_logos_position
				'id'		=> 'footer_org_logos_position',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Organizations Logo position', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'About column', 'bijan' ) ),
				'default'	=> 'about',
				'data'		=> [
					'about'		=> esc_html__( 'About column', 'bijan' ),
					'contact'	=> esc_html__( 'Contact column', 'bijan' ),
				],
				'required'	=> [
					['show_footer_org_logos','=',true],
				]
			],
			[ // footer_before_org_items
				'id'			=> 'footer_before_org_items',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'Before organizations logo', 'bijan' ),
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'before_org_logos_title',
						'type'	=> 'text',
						'title'	=> esc_html__( 'Title', 'bijan' )
					],
					[
						'id'	=> 'before_org_logos',
						'type'	=> 'ace_editor',
						'title'	=> esc_html__( 'Script', 'bijan' ),
					]
				],
				'required'	=> [
					['show_footer','=',true],
					['show_footer_org_logos','=',true],
				]
			],
			[ // footer_orgs_logo_items
				'id'			=> 'footer_orgs_logo_items',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'Organizations logo', 'bijan' ),
				'subtitle'		=> esc_html__( 'Example: enamad and etc.', 'bijan' ),
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'org_logos_title',
						'type'	=> 'text',
						'title'	=> esc_html__( 'Title', 'bijan' )
					],
					[
						'id'	=> 'org_logos',
						'type'	=> 'ace_editor',
						'title'	=> esc_html__( 'Script', 'bijan' ),
					]
				],
				'required'	=> [
					['show_footer','=',true],
					['show_footer_org_logos','=',true],
				]
			],
			[ // footer_after_org_items
				'id'			=> 'footer_after_org_items',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'After organizations logo', 'bijan' ),
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'after_org_logos_title',
						'type'	=> 'text',
						'title'	=> esc_html__( 'Title', 'bijan' )
					],
					[
						'id'	=> 'after_org_logos',
						'type'	=> 'ace_editor',
						'title'	=> esc_html__( 'Script', 'bijan' ),
					]
				],
				'required'	=> [
					['show_footer','=',true],
					['show_footer_org_logos','=',true],
				]
			],
		),
	)
);

Redux::set_section( // Contact section section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Contact section', 'bijan' ),
		'id'			=> 'footer-contact-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // footer_more_info_title
				'id'		=> 'footer_more_info_title',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Contact section title', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'A real hypermarket!', 'bijan' ) ),
				'default'	=> __( 'A real hypermarket!', 'bijan' ),
				'required'	=> [
					['show_footer','=',true],
				],
			],
			[ // footer_more_info_subtitle
				'id'		=> 'footer_more_info_subtitle',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Contact section subtitle', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'With a professional and powerful team, we will always be by your side.', 'bijan' ) ),
				'default'	=> __( 'With a professional and powerful team, we will always be by your side.', 'bijan' ),
				'required'	=> [
					['show_footer','=',true],
				],
			],
			[ // footer_contact_info
				'id'		=> 'footer_contact_info',
				'type'		=> 'multi_text',
				'title'		=> esc_html__( 'Contact info', 'bijan' ),
				'subtitle'	=> esc_html__( 'Example: Phone numbers, email address, etc.', 'bijan' ),
				'default'	=> ['0215853202', '0213456700'],
				'required'	=> [
					['show_footer','=',true],
				],
			],
			[ // footer_contact_info_color_type
				'id'		=> 'footer_contact_info_color_type',
				'type'		=> 'select',
				'title'		=> esc_html__( "Contact info color types", 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Just first item', 'bijan' ) ),
				'default'	=> 'just_first',
				'options'	=> [
					'just_first'	=> __( 'Just first item', 'bijan' ),
					'just_second'	=> __( 'Just second item', 'bijan' ),
					'just_last'		=> __( 'Just last item', 'bijan' ),
					'all'			=> __( 'All items', 'bijan' ),
					'none'			=> __( 'None', 'bijan' ),
				],
			],
			[ // footer_contact_subtitle
				'id'		=> 'footer_contact_subtitle',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Contact section bottom text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'We are available 24/7 to answer your questions.', 'bijan' ) ),
				'default'	=> __( 'We are available 24/7 to answer your questions.', 'bijan' ),
				'required'	=> [
					['show_footer','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Market buttons section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Market buttons', 'bijan' ),
		'id'			=> 'footer-market-btns-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_footer_market_btns
				'id'		=> 'show_footer_market_btns',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Market buttons', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // footer_before_market_btns
				'id'			=> 'footer_before_market_btns',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'Before market buttons', 'bijan' ),
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'before_market_btns_title',
						'type'	=> 'text',
						'title'	=> esc_html__( 'Title', 'bijan' )
					],
					[
						'id'	=> 'before_market_btns',
						'type'	=> 'ace_editor',
						'title'	=> esc_html__( 'Script', 'bijan' ),
					]
				],
				'required'	=> [
					['show_footer','=',true],
					['show_footer_market_btns','=',true],
				]
			],
			[ // footer_market_btns
				'id'			=> 'footer_market_btns',
				'type'			=> 'repeater',
				'title'			=> esc_html__( "Market buttons", 'bijan' ),
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'			=> 'market_btn_text',
						'type'			=> 'text',
						'title'			=> esc_html__( 'Text', 'bijan' ),
						'placeholder'	=> esc_html__( 'Play Store', 'bijan' ),
					],
					[
						'id'			=> 'market_btn_top_text',
						'type'			=> 'text',
						'title'			=> esc_html__( 'Top text', 'bijan' ),
						'placeholder'	=> esc_html__( 'Download from', 'bijan' ),
					],
					[
						'id'		=> 'market_btn_link',
						'type'		=> 'text',
						'validate'	=> ['url'],
						'title'		=> esc_html__( 'Button link', 'bijan' ),
					],
					[
						'id'		=> 'market_logos',
						'type'		=> 'select',
						'title'		=> esc_html__( 'Market icon', 'bijan' ),
						'options'	=> $markets,
					]
				],
				'required'	=> [
					['show_footer','=',true],
					['show_footer_market_btns','=',true],
				]
			],
			[ // footer_after_market_btns
				'id'			=> 'footer_after_market_btns',
				'type'			=> 'repeater',
				'title'			=> esc_html__( 'After market buttons', 'bijan' ),
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'after_market_btns_title',
						'type'	=> 'text',
						'title'	=> esc_html__( 'Title', 'bijan' )
					],
					[
						'id'	=> 'after_market_btns',
						'type'	=> 'ace_editor',
						'title'	=> esc_html__( 'Script', 'bijan' ),
					]
				],
				'required'	=> [
					['show_footer','=',true],
					['show_footer_market_btns','=',true],
				]
			],
		),
	)
);

Redux::set_section( // Copyright
	$opt_name,
	array(
		'title'			=> esc_html__( 'Copyright', 'bijan' ),
		'id'			=> 'footer-copyright-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'footer_copyright',
				'type'		=> 'text',
				'title'		=> __( 'Copyright text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default:<br>%s", 'bijan' ), __( "All rights of this website belong to Bijan store.", 'bijan' ) ),
				'default'	=> __( "All rights of this website belong to Bijan store.", 'bijan' ),
			],
		),
	)
);

Redux::set_section( // Socials
	$opt_name,
	array(
		'title'			=> esc_html__( 'Socials', 'bijan' ),
		'id'			=> 'footer-socials-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_footer_socials_items
				'id'		=> 'show_footer_socials_items',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Socials', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // footer_socials_position
				'id'		=> 'footer_socials_position',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Social icons position', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Front copyright text', 'bijan' ) ),
				'default'	=> 'front_copyright',
				'data'		=> [
					'front_copyright'	=> esc_html__( 'Front copyright text', 'bijan' ),
					'bottom_copyright'	=> esc_html__( 'Bottom copyright text', 'bijan' ),
					'about'				=> esc_html__( 'About column', 'bijan' ),
					'contact'			=> esc_html__( 'Contact column', 'bijan' ),
				],
				'required'	=> [
					['show_footer_socials_items','=',true],
				]
			],
			[ // footer_socials_items
				'id'			=> 'footer_socials_items',
				'type'			=> 'repeater',
				'title'			=> __( 'Social items', 'bijan' ),
				'compiler'		=> true,
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'			=> 'footer_social_icon',
						'type'			=> 'icon_select',
						'title'			=> esc_html__( 'Icon', 'bijan' ),
						'compiler'		=> true,
						'default'		=> 'bijan-icon-instagram',
						'enqueue_frontend'	=> false,
						'stylesheet'	=> [
							[
								'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
								'title'		=> __( 'Bijan icons', 'bijan' ),
								'prefix'	=> 'bijan-icon',
							],
						],
					],
					[
						'id'		=> 'footer_social_link',
						'type'		=> 'text',
						'validate'	=> ['url'],
						'title'		=> esc_html__( 'URL', 'bijan' ),
						'compiler'	=> true,
					],
				],
				'required'	=> [
					['show_footer','=',true],
					['show_footer_socials_items','=',true],
				]
			],

		),
	)
);