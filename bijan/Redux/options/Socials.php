<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Socials
	$opt_name,
	array(
		'title'			=> esc_html__( 'Socials', 'bijan' ),
		'id'			=> 'socials-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // socials
				'id'		=> 'socials',
				'type'			=> 'repeater',
				'title'			=> __( 'Social items', 'bijan' ),
				'compiler'		=> true,
				'group_values'	=> true,
				'init_empty'	=> true,
				'fields'		=> [
					[
						'id'	=> 'social_name',
						'type'	=> 'text',
						'title'	=> esc_html__( "Title", 'bijan' ),
					],
					[
						'id'			=> 'social_icon',
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
						'id'		=> 'social_link',
						'type'		=> 'text',
						'validate'	=> ['url'],
						'title'		=> esc_html__( 'URL', 'bijan' ),
						'compiler'	=> true,
					],
				],
			],
		),
	)
);