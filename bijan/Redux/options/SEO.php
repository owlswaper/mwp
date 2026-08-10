<?php
defined( 'ABSPATH' ) || exit;

$tags = [
	'h1'	=> esc_html__( 'H1', 'bijan' ),
	'h2'	=> esc_html__( 'H2', 'bijan' ),
	'h3'	=> esc_html__( 'H3', 'bijan' ),
	'h4'	=> esc_html__( 'H4', 'bijan' ),
	'h5'	=> esc_html__( 'H5', 'bijan' ),
	'h6'	=> esc_html__( 'H6', 'bijan' ),
	'div'	=> esc_html__( 'div', 'bijan' ),
	'p'		=> esc_html__( 'p', 'bijan' ),
	'span'	=> esc_html__( 'span', 'bijan' ),
];

Redux::set_section( // General
	$opt_name,
	array(
		'title'			=> esc_html__( 'Title tags', 'bijan' ),
		'id'			=> 'seo-title-tags-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // homepage-site-title-tag
				'id'		=> 'homepage-site-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Home page site title', 'bijan' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // otherpage-site-title-tag
				'id'		=> 'otherpage-site-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Other pages site title', 'bijan' ),
				'default'	=> 'div',
				'options'	=> $tags
			],
			[ // archive-title-tag
				'id'		=> 'archive-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Archive page title', 'bijan' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // shop-page-title-tag
				'id'		=> 'shop-page-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Shop page title', 'bijan' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // page-title-tag
				'id'		=> 'page-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Pages title', 'bijan' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // post-title-tag
				'id'		=> 'post-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Single post title', 'bijan' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
			[ // product-title-tag
				'id'		=> 'product-title-tag',
				'type'		=> 'select',
				'title'		=> __( 'Single product title', 'bijan' ),
				'default'	=> 'h1',
				'options'	=> $tags
			],
		),
	)
);