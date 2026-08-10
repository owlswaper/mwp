<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Header section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Header', 'bijan' ),
		'id'			=> 'header-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_header
				'id'		=> 'show_header',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Header status', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // sticky_header
				'id'		=> 'sticky_header',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Header type', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Sticky', 'bijan' ),
				'off'		=> esc_html__( 'Static', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					[
						'show_header',
						'=',
						true
					]
				]
			],
			[ // auto_hide_header
				'id'		=> 'auto_hide_header',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Auto hide header', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Auto-hide", 'bijan' ) ),
				'desc'		=> esc_html__( 'When scrolling down, the header hides; when scrolling up, the header reappears.', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Auto-hide', 'bijan' ),
				'off'		=> esc_html__( 'Always show', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true],
					['sticky_header','=',true],
				]
			],
			[ // header_bg
				'id'			=> 'header_bg',
				'type'			=> 'color',
				'title'			=> __( 'Header background', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '#ffffff' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#ffffff',
				'required'	=> [
					[
						'show_header',
						'=',
						true
					]
				]
			],
			[ // header_padding
				'id'		=> 'header_padding',
				'type'		=> 'spacing',
				'title'		=> esc_html__( 'Header padding', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Top: 32px & Right: 0 & Bottom: 32px & Left: 0', 'bijan' ) ),
				'default'	=> [
					'padding-top'		=> 32,
					'padding-right'		=> 0,
					'padding-bottom'	=> 32,
					'padding-left'		=> 0,
				],
				'required'	=> [
					['show_header','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Bottom header section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Bottom header', 'bijan' ),
		'id'			=> 'bottom-header-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_bottom_header
				'id'		=> 'show_bottom_header',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Bottom header status', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Show", 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // sticky_bottom_header
				'id'		=> 'sticky_bottom_header',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Bottom header type', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Sticky", 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Sticky', 'bijan' ),
				'off'		=> esc_html__( 'Static', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_header','=',true]
				]
			],
			[ // bottom_header_bg
				'id'			=> 'bottom_header_bg',
				'type'			=> 'color',
				'title'			=> __( 'Bottom header background', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '#f8f8f8' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#f8f8f8',
				'required'	=> [
					['show_bottom_header','=',true]
				]
			],
		),
	)
);

Redux::set_section( // Logo section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Logo', 'bijan' ),
		'id'			=> 'header-logo-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-logo
				'id'		=> 'show-logo',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show logo in the header', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					'show_header',
					'=',
					true
				],
			],
			[ // logo-type
				'id'		=> 'logo-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Logo type', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Image', 'bijan' ) ),
				'data'		=> [
					'text'	=> esc_html__( 'Text', 'bijan' ),
					'img'	=> esc_html__( 'Image', 'bijan' ),
				],
				'default'	=> 'img',
				'required'	=> [
					[
						'show-logo',
						'=',
						true
					],
				]
			],
			[ // logo-text-type
				'id'		=> 'logo-text-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Text type', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Site title', 'bijan' ) ),
				'data'		=> [
					'title'		=> esc_html__( 'Site title', 'bijan' ),
					'custom'	=> esc_html__( 'Custom', 'bijan' ),
				],
				'default'	=> 'title',
				'required'	=> [
					[
						'show-logo',
						'=',
						true
					],
					[
						'logo-type',
						'=',
						'text'
					],
				],
			],
			[ // logo-text-custom
				'id'		=> 'logo-text-custom',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Logo text', 'bijan' ),
				'required'	=> [
					[
						'show-logo',
						'=',
						true
					],
					[
						'logo-type',
						'=',
						'text'
					],
					[
						'logo-text-type',
						'=',
						'custom'
					],
				],
			],
			[ // logo-img
				'id'				=> 'logo-img',
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
					['show-logo','=',true],
					['logo-type','=','img'],
				],
			],
			[ // logo-img-size
				'id'		=> 'logo-img-size',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Logo size', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), 'W: 108 & H: 30' ),
				'desc'		=> esc_html__( 'Leave empty for full size', 'bijan' ),
				'default'	=> [
					'width'		=> 108,
					'height'	=> 30
				],
				'required'	=> [
					[
						'show-logo',
						'=',
						true
					],
					[
						'logo-type',
						'=',
						'img'
					],
				],
			],
			[ // logo-link
				'id'			=> 'logo-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Logo URL', 'bijan' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'bijan' ), home_url() ),
				'validate'		=> ['url'],
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					[
						'show-logo',
						'=',
						true
					],
				],
			],
			[ // header_logo_color
				'id'			=> 'header_logo_color',
				'type'			=> 'color',
				'title'			=> __( 'Logo text color', 'bijan' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '#2e313c' ),
				'validate'		=> 'color',
				'transparent'	=> false,
				'default'		=> '#2e313c',
				'required'		=> [
					[
						'show-logo',
						'=',
						true
					],
					[
						'logo-type',
						'=',
						'text'
					],
				],
			],
			[ // sticky_header_logo_color
				'id'			=> 'sticky_header_logo_color',
				'type'			=> 'color',
				'title'			=> __( 'Logo text color (sticky)', 'bijan' ),
				'compiler'		=> true,
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '#2e313c' ),
				'transparent'	=> false,
				'default'		=> '#2e313c',
				'required'		=> [
					[
						'show-logo',
						'=',
						true
					],
					[
						'logo-type',
						'=',
						'text'
					],
					[
						'sticky_header',
						'=',
						true
					]
				],
			],
		),
	)
);

Redux::set_section( // Search section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Search bar', 'bijan' ),
		'id'			=> 'header-search-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-header-search
				'id'		=> 'show-header-search',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show search bar', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( "Show", 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // header-search-placeholder
				'id'		=> 'header-search-placeholder',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Search placeholder', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Search...", 'bijan' ) ),
				'default'	=> esc_html__( "Search...", 'bijan' ),
				'required'	=> [
					['show_header','=',true],
					['show-header-search', '=', true],
				],
			],
			[ // header-search-icon
				'id'			=> 'header-search-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Search icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-search-normal' ),
				'default'		=> 'bijan-icon-search-normal',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_header','=',true],
					['show-header-search', '=', true],
				],
			],
			[ // header-search-post-types
				'id'		=> 'header-search-post-types',
				'type'		=> 'select',
				'title'		=> __( 'Header search post types', 'bijan' ),
				'data'		=> 'post_types',
				'multi'		=> true,
				'default'	=> ['post', 'product'],
				'required'		=> [
					['show_header','=',true],
					['show-header-search', '=', true],
				],
			],
		),
	)
);

Redux::set_section( // Menu section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Menu', 'bijan' ),
		'id'			=> 'header-menu-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-header-menu
				'id'		=> 'show-header-menu',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show main menu', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true],
				]
			],
			[ // show-header-second-menu
				'id'		=> 'show-header-second-menu',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show second menu', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true],
				]
			],
		),
	)
);

Redux::set_section( // Megamenu section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Megamenu', 'bijan' ),
		'id'			=> 'header-megamenu-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // active-megamenu
				'id'		=> 'active-megamenu',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Megamenu', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
		),
	)
);

Redux::set_section( // Cart section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Cart button', 'bijan' ),
		'id'			=> 'header-cart-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-cart
				'id'		=> 'show-cart',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show cart icon', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					[
						'show_header',
						'=',
						true
					]
				],
			],
			[ // cart-icon
				'id'			=> 'cart-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Cart button icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-shopping-cart' ),
				'default'		=> 'bijan-icon-shopping-cart',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					[
						'show-cart',
						'=',
						true
					],
				],
			],
			[ // show-mini-cart
				'id'		=> 'show-mini-cart',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show mini cart', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true],
					['show-cart','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Account section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Account button', 'bijan' ),
		'id'			=> 'header-account-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-account-btn
				'id'		=> 'show-account-btn',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show account button', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_header','=',true]
				]
			],
			[ // account-btn-icon
				'id'			=> 'account-btn-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Button icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-user' ),
				'default'		=> 'bijan-icon-user',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-link
				'id'			=> 'account-btn-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Button URL', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), home_url( 'my-account' ) ),
				'validate'		=> ['url'],
				'default'		=> home_url( 'my-account' ),
				'placeholder'	=> home_url( 'my-account' ),
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // account-btn-link-newtab
				'id'		=> 'account-btn-link-newtab',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Open link in newtab', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'No', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> false,
				'required'	=> [
					['show-account-btn','=',true]
				]
			],
			[ // header-btn-bg-color
				'id'			=> 'header-btn-bg-color',
				'type'			=> 'color',
				'title'			=> __( 'Button background', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '#f0f0f080' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#f0f0f080',
				'color_alpha'	=> true,
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
			[ // header-btn-color
				'id'			=> 'header-btn-color',
				'type'			=> 'color',
				'title'			=> __( 'Button icon color', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '#4e5364' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> '#4e5364',
				'required'		=> [
					['show-account-btn','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Account items section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Account items', 'bijan' ),
		'id'			=> 'header-account-items-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-login-item
				'id'		=> 'show-login-item',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show login item', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show-account-btn','=',true]
				]
			],
			[ // login-text
				'id'			=> 'login-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Login text', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Login", 'bijan' ) ),
				'default'		=> esc_html__( "Login", 'bijan' ),
				'placeholder'	=> esc_html__( "Login", 'bijan' ),
				'required'		=> [
					['show-login-item','=',true],
				],
			],
			[ // login-icon
				'id'			=> 'login-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Login icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-login' ),
				'default'		=> 'bijan-icon-login',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show-login-item','=',true],
				],
			],
			[ // show-signup-item
				'id'		=> 'show-signup-item',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show signup item', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show-account-btn','=',true]
				]
			],
			[ // signup-text
				'id'			=> 'signup-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Signup text', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Signup", 'bijan' ) ),
				'default'		=> esc_html__( "Signup", 'bijan' ),
				'placeholder'	=> esc_html__( "Signup", 'bijan' ),
				'required'		=> [
					['show-signup-item','=',true],
				],
			],
			[ // signup-icon
				'id'			=> 'signup-icon',
				'type'			=> 'icon_select',
				'title'			=> esc_html__( 'Signup icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-author' ),
				'default'		=> 'bijan-icon-author',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show-signup-item','=',true],
				],
			],
			[
				'type'		=> 'content',
				'mode'		=> 'submessage',
				'content'	=> sprintf( __( 'To set items for logged in users, proceed through the <a href="%s">menus</a>', 'bijan' ), admin_url( 'nav-menus.php' ) ),
				'style'		=> 'info'
			]
		),
	)
);

Redux::set_section( // Header banner section
	$opt_name,
	array(
		'title'			=> esc_html__( 'Header banner', 'bijan' ),
		'id'			=> 'header-banner-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-header-banner
				'id'		=> 'show-header-banner',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show header banner', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Hide', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> false,
			],
			[ // header-banner-title
				'id'			=> 'header-banner-title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Header banner title', 'bijan' ),
				'required'		=> [
					['show-header-banner','=',true],
				],
			],
			[ // header-banner-img
				'id'				=> 'header-banner-img',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Banner image file', 'bijan' ),
				'subtitle'			=> esc_html__( "For screens with a width larger than 1025 pixels", 'bijan' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'readonly'			=> false,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'required'			=> [
					['show-header-banner','=',true],
				],
			],
			[ // header-banner-height
				'id'		=> 'header-banner-height',
				'type'		=> 'slider',
				'title'		=> esc_html__( 'Banner height', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), 80 ),
				'default'	=> 80,
				'min'		=> 1,
				'max'		=> 120,
				'required'	=> [
					['show-header-banner','=',true],
				],
			],
			[ // show-header-banner-tablet
				'id'		=> 'show-header-banner-tablet',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show header banner in tablet', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show-header-banner','=',true],
				],
			],
			[ // header-banner-img-tablet
				'id'				=> 'header-banner-img-tablet',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Banner image file (tablet)', 'bijan' ),
				'subtitle'			=> esc_html__( "For screens with a width between 768 pixels and 1024 pixels", 'bijan' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'readonly'			=> false,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'required'			=> [
					['show-header-banner','=',true],
					['show-header-banner-tablet','=',true],
				],
			],
			[ // header-banner-tablet-height
				'id'		=> 'header-banner-tablet-height',
				'type'		=> 'slider',
				'title'		=> esc_html__( 'Banner height (tablet)', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), 80 ),
				'default'	=> 80,
				'min'		=> 1,
				'max'		=> 120,
				'required'	=> [
					['show-header-banner','=',true],
					['show-header-banner-tablet','=',true],
				],
			],
			[ // show-header-banner-mobile
				'id'		=> 'show-header-banner-mobile',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show header banner in mobile', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show-header-banner','=',true],
				],
			],
			[ // header-banner-img-mobile
				'id'				=> 'header-banner-img-mobile',
				'type'		 		=> 'media',
				'title'				=> esc_html__( 'Banner image file (mobile)', 'bijan' ),
				'subtitle'			=> esc_html__( "For screens with a width lower than 767 pixels", 'bijan' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'readonly'			=> false,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'required'			=> [
					['show-header-banner','=',true],
					['show-header-banner-mobile','=',true],
				],
			],
			[ // header-banner-mobile-height
				'id'		=> 'header-banner-mobile-height',
				'type'		=> 'slider',
				'title'		=> esc_html__( 'Banner height (mobile)', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), 80 ),
				'default'	=> 80,
				'min'		=> 1,
				'max'		=> 120,
				'required'	=> [
					['show-header-banner','=',true],
					['show-header-banner-mobile','=',true],
				],
			],

			[ // divider
				'id'		=> 'header-banner-div',
				'type'		=> 'divide',
				'required'	=> [
					['show-header-banner','=',true],
				],
			],

			[ // header-banner-link
				'id'			=> 'header-banner-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Header banner link', 'bijan' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'bijan' ), home_url() ),
				'validate'		=> ['url'],
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['show-header-banner','=',true],
				],
			],
			[ // header-banner-link-new_tab
				'id'		=> 'header-banner-link-new_tab',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Open link in new tab', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'No', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> false,
				'required'	=> [
					['show-header-banner','=',true],
				],
			],

			[ // divider
				'id'		=> 'header-banner-div-2',
				'type'		=> 'divide',
				'required'	=> [
					['show-header-banner','=',true],
				],
			],
		)
	)
);