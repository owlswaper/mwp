<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // WooCommerce section
	$opt_name,
	array(
		'title'			=> esc_html__( 'WooCommerce', 'bijan' ),
		'id'			=> 'wc-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // default_wc_products_style
				'id'		=> 'default_wc_products_style',
				'type'		=> 'image_select',
				'title'		=> esc_html__( "Default products card style", 'bijan' ),
				'subtitle'	=> esc_html__( "Default products card style in shop, product categories, product tags, search and other general pages", 'bijan' ),
				'options'	=> [
					'products-style-1'	=> [
						'alt'	=> esc_html__( "Products style 1", 'bijan' ),
						'img'	=> BIJAN_URI . "assets/img/backend/products-style-1.png"
					],
					'products-style-2'	=> [
						'alt'	=> esc_html__( "Products style 2", 'bijan' ),
						'img'	=> BIJAN_URI . "assets/img/backend/products-style-2.png"
					],
					'products-style-3'	=> [
						'alt'	=> esc_html__( "Products style 3", 'bijan' ),
						'img'	=> BIJAN_URI . "assets/img/backend/products-style-3.png"
					],
				],
				'default'	=> 'products-style-1',
			],
			[ // sku_status
				'id'		=> 'sku_status',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'SKU status', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],

			[ // divider
				'id'	=> 'wc-general-divider',
				'type'	=> 'divide',
			],

			[ // custom_toman
				'id'		=> 'custom_toman',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Custom toman', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'desc'		=> esc_html__( 'Use custom toman instead of normal toman', 'bijan' ),
				'compiler'	=> true,
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
			],
			[ // custom_toman_style
				'id'		=> 'custom_toman_style',
				'type'		=> 'image_select',
				'title'		=> esc_html__( "Select your Toman", 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Toman 1', 'bijan' ) ),
				'options'	=> [
					'toman'	=> [
						'alt'	=> esc_html__( "Toman 1", 'bijan' ),
						'img'	=> BIJAN_URI . "assets/img/backend/toman.jpg"
					],
					'toman2'	=> [
						'alt'	=> esc_html__( "Toman 2", 'bijan' ),
						'img'	=> BIJAN_URI . "assets/img/backend/toman2.jpg"
					],
					'toman3'	=> [
						'alt'	=> esc_html__( "Toman 3", 'bijan' ),
						'img'	=> BIJAN_URI . "assets/img/backend/toman3.jpg"
					],
				],
				'default'	=> 'toman',
				'required'	=> [
					['custom_toman', '=', true]
				]
			],
		),
	)
);

Redux::set_section( // Shop archive
	$opt_name,
	array(
		'title'			=> esc_html__( 'Shop archive', 'bijan' ),
		'id'			=> 'wc-shop-archive-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-show-archive-description
				'id'		=> 'wc-show-archive-description',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show description', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			// [ // wc-archive-description-accordion
			// 	'id'		=> 'wc-archive-description-accordion',
			// 	'type'		=> 'switch',
			// 	'title'		=> esc_html__( 'Use accordion style for description', 'bijan' ),
			// 	'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'No', 'bijan' ) ),
			// 	'on'		=> esc_html__( 'Yes', 'bijan' ),
			// 	'off'		=> esc_html__( 'No', 'bijan' ),
			// 	'default'	=> false,
			// 	'required'	=> [
			// 		['wc-show-archive-description','=',true],
			// 	]
			// ],
			[ // wc-archive-description-bottom
				'id'		=> 'wc-archive-description-bottom',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Descriptions position', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Bottom', 'bijan' ) ),
				'on'		=> esc_html__( 'Bottom', 'bijan' ),
				'off'		=> esc_html__( 'Top', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-show-archive-description','=',true],
				]
			],
			[ // wc-move-out-of-stock-to-end
				'id'		=> 'wc-move-out-of-stock-to-end',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Move out of stock products to the end of the list', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'No', 'bijan' ) ) . "<br>" . esc_html__( "Caution: Enabling this feature may slow down the site speed.", 'bijan' ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> false,
			],
			[ // wc-show-stock-status
				'id'		=> 'wc-show-stock-status',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show stock status in product archives', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // wc-brands-page-id
				'id'	=> 'wc-brands-page-id',
				'type'	=> 'select',
				'title'	=> esc_html__( 'Select a page for brands archive', 'bijan' ),
				'data'	=> 'pages',
			],
			[ // wc-second-image-hover-show
				'id'		=> 'wc-second-image-hover-show',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show second image on hover', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'No', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> false,
			],
			[ // wc-show-archive-order
				'id'		=> 'wc-show-archive-order',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive order', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // wc-filters-mobile-opener-icon
				'id'			=> 'wc-filters-mobile-opener-icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'Filter opener icon (mobile)', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), is_rtl() ? '<i class="bijan-icon-double-arrow-left"></i>' : '<i class="bijan-icon-double-arrow-right"></i>' ),
				'default'		=> is_rtl() ? 'bijan-icon-double-arrow-left' : 'bijan-icon-double-arrow-right',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
			],
			[ // wc-filters-mobile-close-icon
				'id'			=> 'wc-filters-mobile-close-icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'Filter close icon (mobile)', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), is_rtl() ? '<i class="bijan-icon-double-arrow-right"></i>' : '<i class="bijan-icon-double-arrow-left"></i>' ),
				'default'		=> is_rtl() ? 'bijan-icon-double-arrow-right' : 'bijan-icon-double-arrow-left',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
			],
		),
	)
);

Redux::set_section( // Account page
	$opt_name,
	array(
		'title'			=> esc_html__( 'Account page', 'bijan' ),
		'id'			=> 'wc-account-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // my-account-show-logo
				'id'		=> 'my-account-show-logo',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show logo in the header', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // my-account-logo-type
				'id'		=> 'my-account-logo-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Logo type', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Image', 'bijan' ) ),
				'data'		=> [
					'text'	=> esc_html__( 'Text', 'bijan' ),
					'img'	=> esc_html__( 'Image', 'bijan' ),
				],
				'default'	=> 'img',
				'required'	=> [
					['my-account-show-logo','=',true],
				]
			],
			[ // my-account-logo-text-type
				'id'		=> 'my-account-logo-text-type',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Text type', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Site title', 'bijan' ) ),
				'data'		=> [
					'title'		=> esc_html__( 'Site title', 'bijan' ),
					'custom'	=> esc_html__( 'Custom', 'bijan' ),
				],
				'default'	=> 'title',
				'required'	=> [
					['my-account-show-logo','=',true],
					[
						'my-account-logo-type',
						'=',
						'text'
					],
				],
			],
			[ // my-account-logo-text-custom
				'id'		=> 'my-account-logo-text-custom',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Logo text', 'bijan' ),
				'required'	=> [
					['my-account-show-logo','=',true],
					[
						'my-account-logo-type',
						'=',
						'text'
					],
					[
						'my-account-logo-text-type',
						'=',
						'custom'
					],
				],
			],
			[ // my-account-logo-img
				'id'				=> 'my-account-logo-img',
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
					['my-account-show-logo','=',true],
					['my-account-logo-type','=','img'],
				],
			],
			[ // my-account-logo-img-size
				'id'		=> 'my-account-logo-img-size',
				'type'		=> 'dimensions',
				'title'		=> esc_html__( 'Logo size', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), 'W: 160 & H: 60' ),
				'desc'		=> esc_html__( 'Leave empty for full size', 'bijan' ),
				'default'	=> [
					'width'		=> 160,
					'height'	=> 60
				],
				'required'	=> [
					['my-account-show-logo','=',true],
					[
						'my-account-logo-type',
						'=',
						'img'
					],
				],
			],
			[ // my-account-logo-link
				'id'			=> 'my-account-logo-link',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Logo URL', 'bijan' ),
				'subtitle'		=> sprintf( esc_html__( 'Default: %s', 'bijan' ), home_url() ),
				'validate'		=> ['url'],
				'default'		=> home_url(),
				'placeholder'	=> home_url(),
				'required'		=> [
					['my-account-show-logo','=',true],
				],
			],
			[
				'id'		=> 'my-account-welcome',
				'type'		=> 'text',
				'title'		=> __( 'Account welcome text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "Welcome to Bijan store.", 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( "Welcome to Bijan store.", 'bijan' ),
			],
			[ // my-account-menu-open
				'id'			=> 'my-account-menu-open',
				'type'			=> 'icon_select',
				'title'			=> __( 'Menu opener icon (mobile)', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), is_rtl() ? '<i class="bijan-icon-double-arrow-left"></i>' : '<i class="bijan-icon-double-arrow-right"></i>' ),
				'default'		=> is_rtl() ? 'bijan-icon-double-arrow-left' : 'bijan-icon-double-arrow-right',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
			],
		),
	)
);

Redux::set_section( // Compare
	$opt_name,
	array(
		'title'			=> esc_html__( 'Compare', 'bijan' ),
		'id'			=> 'wc-compare-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-compare
				'id'		=> 'wc-compare',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Activate products compare feature', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
			],
			[ // wc-compare-image
				'id'		=> 'wc-compare-image',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product image', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-compare', '=', true],
				],
			],
			[ // wc-compare-price
				'id'		=> 'wc-compare-price',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product price', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-compare', '=', true],
				],
			],
			[ // wc-compare-excerpt
				'id'		=> 'wc-compare-excerpt',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product excerpt', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-compare', '=', true],
				],
			],
			[ // wc-compare-qty
				'id'		=> 'wc-compare-qty',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product quantity', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-compare', '=', true],
				],
			],
			[ // wc-compare-weight
				'id'		=> 'wc-compare-weight',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product weight', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-compare', '=', true],
				],
			],
			[ // wc-compare-dimension
				'id'		=> 'wc-compare-dimension',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product dimension', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-compare', '=', true],
				],
			],
			[ // wc-compare-add-to-cart
				'id'		=> 'wc-compare-add-to-cart',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show product add to cart button', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-compare', '=', true],
				],
			],
		),
	)
);