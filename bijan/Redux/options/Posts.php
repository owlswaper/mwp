<?php

use Bijan\Utils\Archive;

defined( 'ABSPATH' ) || exit;

Redux::set_section( // Archive settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Archive settings', 'bijan' ),
		'id'			=> 'general-archive-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // default_archive_sort
				'id'		=> 'default_archive_sort',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Default archive sort', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( "Default: %s", 'bijan' ), _x( "Newest", 'Archive sort item', 'bijan' ) ),
				'options'	=> Archive::sorts(),
				'default'	=> 'newest'
			],
			[ // archive_breadcrumb
				'id'		=> 'archive_breadcrumb',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show breadcrumb', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
			[ // archive_show_title
				'id'		=> 'archive_show_title',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive title', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
			[ // archive_show_sidebar
				'id'		=> 'archive_show_sidebar',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show archive sidebar', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
			[ // archive_sidebar
				'id'		=> 'archive_sidebar',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Archive sidebar', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Blog sidebar', 'bijan' ) ),
				'data'		=> 'sidebars',
				'default'	=> 'blog',
				'required'	=> [
					['archive_show_sidebar','=',true],
				]
			],
			[ // archive_desktop_cols
				'id'		=> 'archive_desktop_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop columns", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '3' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 3,
			],
			[ // archive_desktop_gap
				'id'		=> 'archive_desktop_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Desktop gap (px)", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 24,
			],
			[ // archive_tablet_cols
				'id'		=> 'archive_tablet_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet columns", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '2' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 2,
			],
			[ // archive_tablet_gap
				'id'		=> 'archive_tablet_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Tablet gap (px)", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
			[ // archive_mobile_cols
				'id'		=> 'archive_mobile_cols',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile columns", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '2' ),
				'min'		=> 1,
				'max'		=> 6,
				'default'	=> 2,
			],
			[ // archive_mobile_gap
				'id'		=> 'archive_mobile_gap',
				'type'		=> 'spinner',
				'title'		=> esc_html__( "Mobile gap (px)", 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), '24' ),
				'min'		=> 1,
				'max'		=> 64,
				'default'	=> 16,
			],
		),
	)
);