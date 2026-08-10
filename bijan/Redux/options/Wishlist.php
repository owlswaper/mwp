<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Wishlist
	$opt_name,
	array(
		'title'			=> esc_html__( 'Wishlist', 'bijan' ),
		'id'			=> 'wishlist-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // wishlist
				'id'		=> 'wishlist',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Wishlist status', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
			[ // wishlist_ppp
				'id'		=> 'wishlist_ppp',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Products per page", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '3' ),
				'min'		=> 1,
				'max'		=> 100,
				'default'	=> 3,
				'required'	=> [
					['wishlist','=',true]
				]
			],
		),
	)
);