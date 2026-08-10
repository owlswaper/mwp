<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // Notifications
	$opt_name,
	array(
		'title'			=> esc_html__( 'Notifications', 'bijan' ),
		'id'			=> 'notifications-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // notifications
				'id'		=> 'notifications',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Notifications status', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
		),
	)
);