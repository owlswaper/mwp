<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // General
	$opt_name,
	array(
		'title'			=> esc_html__( 'General', 'bijan' ),
		'id'			=> 'texts-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'general_search_no_results',
				'type'		=> 'text',
				'title'		=> __( 'Search no results', 'bijan' ),
				'compiler'	=> true,
				'default'	=> __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'bijan' ),
			],
		),
	)
);

Redux::set_section( // WooCommerce
	$opt_name,
	array(
		'title'			=> esc_html__( 'WooCommerce', 'bijan' ),
		'id'			=> 'texts-wc-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'wc_add_to_cart_single_text',
				'type'		=> 'text',
				'title'		=> __( 'Add to cart (Single) text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Add to cart', 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Add to cart', 'bijan' ),
			],
			[
				'id'		=> 'wc_empty_cart_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty cart text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Your cart is empty!', 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Your cart is empty!', 'bijan' ),
			],
			[
				'id'		=> 'wc_return_to_shop_text',
				'type'		=> 'text',
				'title'		=> __( 'Return to shop button text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Return to shop', 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Return to shop', 'bijan' ),
			],
			[
				'id'		=> 'wc_checkout_text',
				'type'		=> 'text',
				'title'		=> __( 'Checkout button text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Checkout', 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Checkout', 'woocommerce' ),
			],
			[
				'id'		=> 'wc_proceed_to_checkout_text',
				'type'		=> 'text',
				'title'		=> __( 'Proceed to checkout button text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Proceed to checkout', 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Proceed to checkout', 'woocommerce' ),
			],
			[
				'id'		=> 'wc_pay_order_text',
				'type'		=> 'text',
				'title'		=> __( 'Pay order button text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'Pay and submit order', 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> __( 'Pay and submit order', 'bijan' ),
			],
			[
				'id'		=> 'wc_empty_orders_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty orders page text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'No order has been made yet.', 'woocommerce' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'No order has been made yet.', 'woocommerce' ),
			],
			[
				'id'		=> 'wc_empty_downloads_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty downloads page text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( 'No downloads available yet.', 'woocommerce' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'No downloads available yet.', 'woocommerce' ),
			],
			[
				'id'		=> 'wc_empty_shop_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty shop page text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "No product was found.", 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'No product was found.', 'bijan' ),
			],
			[
				'id'		=> 'wc_empty_coupons_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty coupons page text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "There is no coupon code.", 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'There is no coupon code.', 'bijan' ),
			],
			[
				'id'		=> 'wc_empty_notifications_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty notifications page text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "The notification list is empty.", 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'The notification list is empty.', 'bijan' ),
			],
			[
				'id'		=> 'wc_empty_wishlist_text',
				'type'		=> 'text',
				'title'		=> __( 'Empty wishlist page text', 'bijan' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'bijan' ), esc_html__( "There are no products in wishlist.", 'bijan' ) ),
				'compiler'	=> true,
				'default'	=> esc_html__( 'There are no products in wishlist.', 'bijan' ),
			],
		),
	)
);