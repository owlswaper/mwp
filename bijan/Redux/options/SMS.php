<?php
defined( 'ABSPATH' ) || exit;

Redux::set_section( // SMS general settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'SMS general settings', 'bijan' ),
		'id'			=> 'sms-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // sms
				'id'		=> 'sms',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'SMS', 'bijan' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'bijan' ), esc_html__( 'Enabled', 'bijan' ) ),
				'desc'		=> esc_html__( 'Enable or disable the SMS service.', 'bijan' ),
				'on'		=> esc_html__( 'Enabled', 'bijan' ),
				'off'		=> esc_html__( 'Disabled', 'bijan' ),
				'default'	=> true,
			],
			[
				'id'	=> 'sms_info',
				'type'	=> 'info',
				'desc'	=> sprintf( __( "To configure the SMS, Please go to <a href='%s'>SMS Settings</a>", 'bijan' ), admin_url( "admin.php?page=bijan-sms" ) ),
				'style'	=> 'info',
				'icon'	=> 'el-icon-info-sign',
			]
		),
	)
);