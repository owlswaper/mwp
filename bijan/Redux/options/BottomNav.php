<?php

defined( 'ABSPATH' ) || exit;

$cart_url = '';
if( function_exists( 'wc_get_cart_url' ) ) {
	$cart_url = wc_get_cart_url();
}
$wishlist_url = '';
if( function_exists( 'wc_get_account_endpoint_url' ) ) {
	$wishlist_url = wc_get_account_endpoint_url( 'wishlist' );
}
$my_account = '';
if( function_exists( 'wc_get_page_permalink' ) ) {
	$my_account = wc_get_page_permalink( 'myaccount' );
}

Redux::set_section( // Bottom nav
	$opt_name,
	array(
		'title'			=> esc_html__( 'Bottom navigation', 'bijan' ),
		'id'			=> 'bottom-nav-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show_bottom_nav
				'id'		=> 'show_bottom_nav',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show bottom navigation', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // auto_hide_bottom_nav
				'id'		=> 'auto_hide_bottom_nav',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Auto hide bottom navigation', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Auto-hide", 'bijan' ) ),
				'desc'		=> esc_html__( 'When scrolling down, the bottom navigation hides; when scrolling up, the bottom navigation reappears.', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Auto-hide', 'bijan' ),
				'off'		=> esc_html__( 'Always show', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_nav','=',true],
				]
			],
		),
	)
);

Redux::set_section( // Item 1
	$opt_name,
	array(
		'title'			=> esc_html__( 'Item 1', 'bijan' ),
		'id'			=> 'bottom-nav-item-1-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // bottom_nav_show_item_1
				'id'		=> 'bottom_nav_show_item_1',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show item 1', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_nav','=',true],
				]
			],
			[ // bottom_nav_1_icon
				'id'			=> 'bottom_nav_1_icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'Item 1 - Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-home' ),
				'default'		=> 'bijan-icon-home',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_1','=',true],
				],
			],
			[ // bottom_nav_1_text
				'id'		=> 'bottom_nav_1_text',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 1 - Text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Home", 'bijan' ) ),
				'default'	=> esc_html__( "Home", 'bijan' ),
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_1','=',true],
				],
			],
			[ // bottom_nav_1_url
				'id'		=> 'bottom_nav_1_url',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 1 - URL', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), home_url() ),
				'default'	=> home_url(),
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_1','=',true],
				],
			],
			[ // bottom_nav_1_special
				'id'		=> 'bottom_nav_1_special',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Item 1 - Type', 'bijan' ),
				'subtitle'	=> esc_html__( 'If this item should do special thing. Select one of actions.', 'bijan' ),
				'default'	=> 'none',
				'options'	=> [
					'none'			=> __( "Normal item", 'bijan' ),
					'cart'			=> __( "Cart menu", 'bijan' ),
					'categories'	=> __( "Categories menu", 'bijan' ),
					'account'		=> __( "Account menu", 'bijan' ),
				],
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_1','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Item 2
	$opt_name,
	array(
		'title'			=> esc_html__( 'Item 2', 'bijan' ),
		'id'			=> 'bottom-nav-item-2-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // bottom_nav_show_item_2
				'id'		=> 'bottom_nav_show_item_2',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show item 2', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_nav','=',true],
				]
			],
			[ // bottom_nav_2_icon
				'id'			=> 'bottom_nav_2_icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'Item 2 - Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-shopping-bag' ),
				'default'		=> 'bijan-icon-shopping-bag',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_2','=',true],
				],
			],
			[ // bottom_nav_2_text
				'id'		=> 'bottom_nav_2_text',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 2 - Text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Cart", 'bijan' ) ),
				'default'	=> esc_html__( "Cart", 'bijan' ),
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_2','=',true],
				],
			],
			[ // bottom_nav_2_url
				'id'		=> 'bottom_nav_2_url',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 2 - URL', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), $cart_url ),
				'default'	=> $cart_url,
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_2','=',true],
				],
			],
			[ // bottom_nav_2_special
				'id'		=> 'bottom_nav_2_special',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Item 2 - Type', 'bijan' ),
				'subtitle'	=> esc_html__( 'If this item should do special thing. Select one of actions.', 'bijan' ),
				'default'	=> 'cart',
				'options'	=> [
					'none'			=> __( "Normal item", 'bijan' ),
					'cart'			=> __( "Cart menu", 'bijan' ),
					'categories'	=> __( "Categories menu", 'bijan' ),
					'account'		=> __( "Account menu", 'bijan' ),
				],
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_2','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Item 3
	$opt_name,
	array(
		'title'			=> esc_html__( 'Item 3', 'bijan' ),
		'id'			=> 'bottom-nav-item-3-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // bottom_nav_show_item_3
				'id'		=> 'bottom_nav_show_item_3',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show item 3', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_nav','=',true],
				]
			],
			[ // bottom_nav_3_icon
				'id'			=> 'bottom_nav_3_icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'Item 3 - Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-grid' ),
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
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_3','=',true],
				],
			],
			[ // bottom_nav_3_text
				'id'		=> 'bottom_nav_3_text',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 3 - Text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Categories", 'bijan' ) ),
				'default'	=> esc_html__( "Categories", 'bijan' ),
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_3','=',true],
				],
			],
			[ // bottom_nav_3_url
				'id'		=> 'bottom_nav_3_url',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 3 - URL', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), '#' ),
				'default'	=> '#',
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_3','=',true],
				],
			],
			[ // bottom_nav_3_special
				'id'		=> 'bottom_nav_3_special',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Item 3 - Type', 'bijan' ),
				'subtitle'	=> esc_html__( 'If this item should do special thing. Select one of actions.', 'bijan' ),
				'default'	=> 'categories',
				'options'	=> [
					'none'			=> __( "Normal item", 'bijan' ),
					'cart'			=> __( "Cart menu", 'bijan' ),
					'categories'	=> __( "Categories menu", 'bijan' ),
					'account'		=> __( "Account menu", 'bijan' ),
				],
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_3','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Item 4
	$opt_name,
	array(
		'title'			=> esc_html__( 'Item 4', 'bijan' ),
		'id'			=> 'bottom-nav-item-4-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // bottom_nav_show_item_4
				'id'		=> 'bottom_nav_show_item_4',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show item 4', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_nav','=',true],
				]
			],
			[ // bottom_nav_4_icon
				'id'			=> 'bottom_nav_4_icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'Item 4 - Icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), 'bijan-icon-heart' ),
				'default'		=> 'bijan-icon-heart',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_4','=',true],
				],
			],
			[ // bottom_nav_4_text
				'id'		=> 'bottom_nav_4_text',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 4 - Text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Wishlist", 'bijan' ) ),
				'default'	=> esc_html__( "Wishlist", 'bijan' ),
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_4','=',true],
				],
			],
			[ // bottom_nav_4_url
				'id'		=> 'bottom_nav_4_url',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 4 - URL', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), $wishlist_url ),
				'default'	=> $wishlist_url,
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_4','=',true],
				],
			],
			[ // bottom_nav_4_special
				'id'		=> 'bottom_nav_4_special',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Item 4 - Type', 'bijan' ),
				'subtitle'	=> esc_html__( 'If this item should do special thing. Select one of actions.', 'bijan' ),
				'default'	=> '',
				'options'	=> [
					'none'			=> __( "Normal item", 'bijan' ),
					'cart'			=> __( "Cart menu", 'bijan' ),
					'categories'	=> __( "Categories menu", 'bijan' ),
					'account'		=> __( "Account menu", 'bijan' ),
				],
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_4','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Item 5
	$opt_name,
	array(
		'title'			=> esc_html__( 'Item 5', 'bijan' ),
		'id'			=> 'bottom-nav-item-5-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // bottom_nav_show_item_5
				'id'		=> 'bottom_nav_show_item_5',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show item 5', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_nav','=',true],
				]
			],
			[ // bottom_nav_5_icon
				'id'			=> 'bottom_nav_5_icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'Item 5 - Icon', 'bijan' ),
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
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_5','=',true],
				],
			],
			[ // bottom_nav_5_text
				'id'		=> 'bottom_nav_5_text',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 5 - Text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Profile", 'bijan' ) ),
				'default'	=> esc_html__( "Profile", 'bijan' ),
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_5','=',true],
				],
			],
			[ // bottom_nav_5_url
				'id'		=> 'bottom_nav_5_url',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Item 5 - URL', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), $my_account ),
				'default'	=> $my_account,
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_5','=',true],
				],
			],
			[ // bottom_nav_5_special
				'id'		=> 'bottom_nav_5_special',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Item 5 - Type', 'bijan' ),
				'subtitle'	=> esc_html__( 'If this item should do special thing. Select one of actions.', 'bijan' ),
				'default'	=> 'account',
				'options'	=> [
					'none'			=> __( "Normal item", 'bijan' ),
					'cart'			=> __( "Cart menu", 'bijan' ),
					'categories'	=> __( "Categories menu", 'bijan' ),
					'account'		=> __( "Account menu", 'bijan' ),
				],
				'required'	=> [
					['show_bottom_nav','=',true],
					['bottom_nav_show_item_5','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Mobile menu
	$opt_name,
	array(
		'title'			=> esc_html__( 'Mobile menu', 'bijan' ),
		'id'			=> 'bottom-nav-mobile-menu-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // show-mobile-search
				'id'		=> 'show-mobile-search',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show search bar', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), __( "Show", 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['show_bottom_nav','=',true],
				]
			],
			[ // mobile-search-placeholder
				'id'		=> 'mobile-search-placeholder',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Search placeholder', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Search...", 'bijan' ) ),
				'default'	=> esc_html__( "Search...", 'bijan' ),
				'required'	=> [
					['show_bottom_nav','=',true],
					['show-mobile-search', '=', true],
				],
			],
			[ // mobile-search-icon
				'id'			=> 'mobile-search-icon',
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
					['show_bottom_nav','=',true],
					['show-mobile-search', '=', true],
				],
			],
		),
	)
);