<?php

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Custom codes
	$opt_name,
	array(
		'title'			=> esc_html__( 'Custom codes', 'bijan' ),
		'id'			=> 'general-custom-codes-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'header_custom_code',
				'type'		=> 'ace_editor',
				'title'		=> __( 'Header', 'bijan' ),
				'desc'		=> esc_html__( 'The following code will add to the <head> tag.', 'bijan' ),
				'compiler'	=> true,
				'mode'		=> 'html',
			],
			[
				'id'		=> 'footer_custom_code',
				'type'		=> 'ace_editor',
				'title'		=> __( 'Footer', 'bijan' ),
				'desc'		=> esc_html__( 'The following code will be added to the footer before the closing </body> tag.', 'bijan' ),
				'compiler'	=> true,
				'mode'		=> 'html',
			],
		),
	)
);

Redux::set_section( // Search settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Search settings', 'bijan' ),
		'id'			=> 'general-search-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'exclude_post_types',
				'type'		=> 'select',
				'title'		=> __( 'Exclude post types', 'bijan' ),
				'data'		=> 'post_types',
				'multi'		=> true,
				'default'	=> ['page', 'attachment', 'e-floating-buttons']
			],
		),
	)
);

Redux::set_section( // 404 settings
	$opt_name,
	array(
		'title'			=> esc_html__( '404 settings', 'bijan' ),
		'id'			=> 'general-404-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // 404_image
				'id'				=> '404_image',
				'type'		 		=> 'media',
				'title'				=> esc_html__( '404 Image', 'bijan' ),
				'compiler'	 		=> true,
				'url'				=> true,
				'preview_size'		=> 'full',
				'library_filter'	=> ['jpeg', 'gif', 'png', 'bmp', 'tiff', 'x-icon', 'svg', 'svg+xml', 'webp'],
				'default'			=> [
					'url'	=> BIJAN_URI . "assets/img/404.svg",
				],
			],
			[ // 404_title
				'id'		=> '404_title',
				'type'		=> 'text',
				'title'		=> __( "404 Page title", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'bijan' ), esc_html__( "The desired page was not found.", 'bijan' ) ),
				'default'	=> esc_html__( "The desired page was not found.", 'bijan' ),
			],
			[ // 404_subtitle
				'id'		=> '404_subtitle',
				'type'		=> 'text',
				'title'		=> __( "404 Page subtitle", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'bijan' ), esc_html__( "This page may not exist or has been deleted.", 'bijan' ) ),
				'default'	=> esc_html__( "This page may not exist or has been deleted.", 'bijan' ),
			],
		),
	)
);