<?php

use Bijan\Utils\Product;

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Product page
	$opt_name,
	array(
		'title'			=> esc_html__( 'Product page', 'bijan' ),
		'id'			=> 'wc-single-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-show-stock-status-single
				'id'		=> 'wc-show-stock-status-single',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show stock status in product page', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
				'on'		=> esc_html__( 'Show', 'bijan' ),
				'off'		=> esc_html__( 'Hide', 'bijan' ),
				'default'	=> true,
			],
			[ // wc-single-show-color-in-attribute-value
				'id'		=> 'wc-single-show-color-in-attribute-value',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show color instead of color name in attributes table', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
			],
		),
	)
);

Redux::set_section( // Price history
	$opt_name,
	array(
		'title'			=> esc_html__( 'Price history', 'bijan' ),
		'id'			=> 'wc-single-price-history-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-price-history
				'id'		=> 'wc-price-history',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Activate product price history feature', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
			],
		),
	)
);

Redux::set_section( // Gallery settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Gallery settings', 'bijan' ),
		'id'			=> 'wc-single-gallery-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-single-gallery-thumb-pos
				'id'		=> 'wc-single-gallery-thumb-pos',
				'type'		=> 'radio',
				'title'		=> esc_html__( 'Gallery thumbnail position', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), is_rtl() ? esc_html__( 'Right', 'bijan' ) : esc_html__( 'Left', 'bijan' ) ),
				'default'	=> is_rtl() ? 'right' : 'left',
				'data'		=> [
					'left'		=> esc_html__( 'Left', 'bijan' ),
					'right'		=> esc_html__( 'Right', 'bijan' ),
					'top'		=> esc_html__( 'Top', 'bijan' ),
					'bottom'	=> esc_html__( 'Bottom', 'bijan' ),
				],
			],
			[ // divider
				'id'	=> 'wc-single-divider',
				'type'	=> 'divide',
			],

			[ // wc-lightbox
				'id'		=> 'wc-lightbox',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Zoom gallery images', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
			],
			[ // wc-lightbox-download
				'id'		=> 'wc-lightbox-download',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show download image button', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-lightbox', '=', true]
				],
			],
			[ // wc-lightbox-thumbnail
				'id'		=> 'wc-lightbox-thumbnail',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show thumbnail images in lightbox', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-lightbox', '=', true]
				],
			],
			[ // wc-lightbox-fullscreen
				'id'		=> 'wc-lightbox-fullscreen',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show fullscreen image button', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-lightbox', '=', true]
				],
			],
			[ // wc-lightbox-rotate
				'id'		=> 'wc-lightbox-rotate',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show rotate image button', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
				'required'	=> [
					['wc-lightbox', '=', true]
				],
			],
		)
	)
);

Redux::set_section( // Short description settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Short description settings', 'bijan' ),
		'id'			=> 'wc-single-short-description-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-single-short-description
				'id'		=> 'wc-single-short-description',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show short description', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
			],
			[ // wc-single-short-pos
				'id'		=> 'wc-single-short-pos',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Short description position', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Under add to cart', 'bijan' ) ),
				'default'	=> 'after_add_to_cart',
				'data'		=> [
					'after_add_to_cart'		=> esc_html__( 'Under add to cart', 'bijan' ),
					'after_title'			=> esc_html__( 'Right after title', 'bijan' ),
					'after_actions'			=> esc_html__( 'After action buttons', 'bijan' ),
					'after_featured_attrs'	=> esc_html__( 'After featured attributes', 'bijan' ),
					'after_variations'		=> esc_html__( 'After variation options', 'bijan' ),
					'under_gallery'			=> esc_html__( 'Under gallery', 'bijan' ),
					'box_before_icons'		=> esc_html__( 'Separated box before product icons', 'bijan' ),
					'box_after_icons'		=> esc_html__( 'Separated box after product icons', 'bijan' ),
				],
				'required'	=> [
					['wc-single-short-description','=',true]
				],
			],
			[ // wc-single-short-color
				'id'			=> 'wc-single-short-color',
				'type'			=> 'color',
				'title'			=> __( 'Short description color', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '#9b9ca4' ),
				'compiler'		=> true,
				'transparent'	=> false,
				'default'		=> '#9b9ca4',
				'required'	=> [
					['wc-single-short-description','=',true]
				]
			],
		)
	)
);

$features_icons_fields = [
	[ // wc-show-product-icons
		'id'		=> 'wc-show-product-icons',
		'type'		=> 'switch',
		'title'		=> esc_html__( 'Show product feature icons', 'bijan' ),
		'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
		'desc'		=> esc_html__( 'Showing express delivery and other icons', 'bijan' ),
		'on'		=> esc_html__( 'Show', 'bijan' ),
		'off'		=> esc_html__( 'Hide', 'bijan' ),
		'default'	=> true,
	],

	[ // divider
		'id'	=> 'product-icons-div',
		'type'	=> 'divide',
	],
];
$default_features_icons = Product::default_icons_args();
for( $index = 1; $index <= 4; $index++ ) {
	$features_icons_fields[] = [
		'id'		=> "wc-product-icon-{$index}",
		'type'		=> 'switch',
		'title'		=> sprintf( esc_html__( 'Show product feature icon %d', 'bijan' ), $index ),
		'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Show', 'bijan' ) ),
		'on'		=> esc_html__( 'Show', 'bijan' ),
		'off'		=> esc_html__( 'Hide', 'bijan' ),
		'default'	=> true,
		'required'	=> [
			['wc-show-product-icons','=',true]
		]
	];
	$features_icons_fields[] = [
		'id'				=> "wc-product-icon-{$index}-img",
		'type'		 		=> 'media',
		'title'				=> esc_html__( 'Item image', 'bijan' ),
		'url'				=> true,
		'preview_size'		=> 'full',
		'readonly'			=> false,
		'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
		'default'			=> [
			'url'	=> $default_features_icons[$index-1]['icon'],
		],
		'required'	=> [
			['wc-show-product-icons','=',true],
			["wc-product-icon-{$index}",'=',true],
		]
	];
	$features_icons_fields[] = [
		'id'		=> "wc-product-icon-{$index}-title",
		'type'		=> 'text',
		'title'		=> __( "Item title", 'bijan' ),
		'subtitle'	=> sprintf( esc_html__( "Default: %s", 'bijan' ), $default_features_icons[$index-1]['title'] ),
		'default'	=> $default_features_icons[$index-1]['title'],
		'required'	=> [
			['wc-show-product-icons','=',true],
			["wc-product-icon-{$index}",'=',true],
		]
	];
	$features_icons_fields[] = [
		'id'		=> "wc-product-icon-{$index}-subtitle",
		'type'		=> 'text',
		'title'		=> __( "Item subtitle", 'bijan' ),
		'subtitle'	=> sprintf( esc_html__( "Default: %s", 'bijan' ), $default_features_icons[$index-1]['subtitle'] ),
		'default'	=> $default_features_icons[$index-1]['subtitle'],
		'required'	=> [
			['wc-show-product-icons','=',true],
			["wc-product-icon-{$index}",'=',true],
		]
	];
	$features_icons_fields[] = [
		'id'		=> "wc-product-icon-{$index}-link",
		'type'		=> 'text',
		'title'		=> __( "Item link", 'bijan' ),
		'subtitle'	=> sprintf( esc_html__( "Default: %s", 'bijan' ), $default_features_icons[$index-1]['link'] ),
		'default'	=> $default_features_icons[$index-1]['link'],
		'required'	=> [
			['wc-show-product-icons','=',true],
			["wc-product-icon-{$index}",'=',true],
		]
	];

	$features_icons_fields[] = [
		'id'		=> "product-icons-div-{$index}",
		'type'		=> 'divide',
		'required'	=> [
			['wc-show-product-icons','=',true],
			["wc-product-icon-{$index}",'=',true],
		]
	];
}
Redux::set_section( // Product features icons
	$opt_name,
	array(
		'title'			=> esc_html__( 'Product features icons', 'bijan' ),
		'id'			=> 'wc-product-features-icons-section',
		'subsection'	=> true,
		'fields'		=> $features_icons_fields
	)
);

Redux::set_section( // End products
	$opt_name,
	array(
		'title'			=> esc_html__( 'End products', 'bijan' ),
		'id'			=> 'wc-single-end-products-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wc-single-end-products-show
				'id'		=> 'wc-single-end-products-show',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show end page products', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Yes', 'bijan' ) ),
				'on'		=> esc_html__( 'Yes', 'bijan' ),
				'off'		=> esc_html__( 'No', 'bijan' ),
				'default'	=> true,
			],
			[ // wc-single-end-products-title
				'id'			=> 'wc-single-end-products-title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'End products title', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Related products', 'bijan' ) ),
				'default'		=> esc_html__( 'Related products', 'bijan' ),
				'placeholder'	=> esc_html__( 'Related products', 'bijan' ),
				'required'		=> [
					['wc-single-end-products-show','=',true],
				],
			],
			[ // wc-single-end-products-title_icon
				'id'			=> 'wc-single-end-products-title_icon',
				'type'			=> 'icon_select',
				'title'			=> __( 'End products title icon', 'bijan' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'bijan' ), '<i class="bijan-icon-flash"></i>' ),
				'default'		=> 'bijan-icon-flash',
				'enqueue_frontend'	=> false,
				'stylesheet'	=> [
					[
						'url'		=> BIJAN_URI . 'assets/css/iconly.min.css',
						'title'		=> __( 'Bijan icons', 'bijan' ),
						'prefix'	=> 'bijan-icon',
					],
				],
				'required'		=> [
					['wc-single-end-products-show','=',true],
				],
			],
			[ // wc-single-end-products-title_tag
				'id'		=> 'wc-single-end-products-title_tag',
				'type'		=> 'select',
				'title'		=> __( 'End products title tag', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'H3', 'bijan' ) ),
				'default'	=> 'h3',
				'options'	=> $tags,
				'required'		=> [
					['wc-single-end-products-show','=',true],
				]
			],
			[ // wc-single-end-products-ppp
				'id'		=> 'wc-single-end-products-ppp',
				'type'		=> 'spinner',
				'title'		=> __( 'Post to show', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), '5' ),
				'default'	=> '5',
				'min'		=> '1',
				'max'		=> '5',
				'required'	=> [
					['wc-single-end-products-show','=',true],
				]
			],
			[ // wc-single-end-products-type
				'id'		=> 'wc-single-end-products-type',
				'type'		=> 'select',
				'title'		=> __( 'End products type', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Related products', 'bijan' ) ),
				'default'	=> 'related',
				'options'	=> [
					'related'	=> esc_html__( "Related products", 'bijan' ),
					'latests'	=> esc_html__( "Latests products", 'bijan' ),
				],
				'required'		=> [
					['wc-single-end-products-show','=',true],
				]
			],
		)
	)
);