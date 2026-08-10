<?php
namespace Bijan\Utils;

use Bijan\Model\OTP;
use Bijan\Utils;
use MJ\Whitebox\Utils\Date;
use MJ\Whitebox\Utils\Sanitizers;
use MJ\Whitebox\Utils\Validators;

class SMS extends Utils {
	public static function defaults() {
		return [
			'gateway'	=> '',
			'messages'	=> [
				'auth'	=> [
					'login'			=> '{otp}',
					'register'		=> '{otp}',
					'lost_password'	=> '{password}',
				]
			],
			'settings'	=> [
				'auth'	=> [
					'login'	=> [
						'enabled'	=> true,
						'pattern'	=> '',
						'otp_timer'	=> 60,
					],
					'register'	=> [
						'enabled'	=> true,
						'pattern'	=> '',
						'otp_timer'	=> 60,
					],
					'one_form'	=> true,
					'lost_password'	=> [
						'enabled'	=> true,
						'pattern'	=> '',
					],
				],
			],
			'security'	=> [
				'hide_mobile'			=> 'mid_star',
				'hide_mobile_custom'	=> sprintf( __( "{name}'s user", 'bijan' ), "\r\n\r\n" . get_option( 'blogname', '' ) ),
			],
		];
	}

	public static function gateways() {
		return apply_filters( 'bijan/sms/gateways', [
			'melipayamak'	=> [
				'label'			=> __( 'Melipayamak', 'bijan' ),
				'logo'			=> 'melipayamak.jpg',
				'fields'		=> ['username', 'password'],
			],
			'farazsms'	=> [
				'label'			=> __( 'Farazsms (ippanel)', 'bijan' ),
				'logo'			=> 'farazsms.png',
				'fields'		=> ['api_key', 'from'],
			],
			'farazsms_new'	=> [
				'label'			=> __( 'New Farazsms (iranpayamak)', 'bijan' ),
				'logo'			=> 'farazsms.png',
				'fields'		=> ['api_key', 'from'],
			],
			'smsir'	=> [
				'label'			=> __( 'SMS.ir', 'bijan' ),
				'logo'			=> 'sms.ir.svg',
				'fields'		=> ['api_key'],
			],
			'kavenegar'	=> [
				'label'			=> __( 'Kavenegar', 'bijan' ),
				'logo'			=> 'kavenegar.png',
				'fields'		=> ['api_key'],
			],
			'farapayamak'	=> [
				'label'			=> __( 'Farapayamak', 'bijan' ),
				'logo'			=> 'farapayamak.png',
				'fields'		=> ['username', 'password'],
			],
			'payamresan'	=> [
				'label'			=> __( 'Payamresan', 'bijan' ),
				'logo'			=> 'payamresan.svg',
				'fields'		=> ['api_key'],
			],
			'raygansms'	=> [
				'label'			=> __( 'Raygansms', 'bijan' ),
				'logo'			=> 'raygansms.png',
				'fields'		=> ['username', 'password', 'api_key'],
			],
			'asanak'	=> [
				'label'			=> __( 'Asanak', 'bijan' ),
				'logo'			=> 'asanak.png',
				'fields'		=> ['username', 'password'],
			],
		] );
	}
	
	public static function get_settings() {
		$settings = null;
		if( $settings === null ) {
			$settings = parent::check_default( get_option( 'bijan_sms_settings', self::defaults() ), self::defaults() );

			if( !empty( $settings['gateway'] ) ) {
				if( !isset( self::gateways()[$settings['gateway']] ) ) {
					$settings = parent::unset( $settings, [$settings['gateway']] ); // Remove the gateway settings if it's not valid
					$settings['gateway'] = '';
				} else {
					foreach( self::gateways()[$settings['gateway']]['fields'] as $field ) {
						$settings[$settings['gateway']][$field] = $settings[$settings['gateway']][$field] ?? '';
					}
				}
			}
		}

		return $settings;
	}

	public static function sanitize_gateway( string $gateway, array $gateways = [] ) {
		if( empty( $gateways ) ) {
			$gateways = self::gateways();
		}
		return parent::ensure_values_in_array( parent::convert_chars( $gateway, true, 'strtolower' ), array_keys( $gateways ) );
	}

	public static function save_settings( $settings ) {
		$gateways = self::gateways();

		// Save gateway settings
		$settings["bijan_sms_gateway"] = !empty( $settings["bijan_sms_gateway"] ) ? $settings["bijan_sms_gateway"] : '';
		$result_settings['gateway'] = self::sanitize_gateway( $settings["bijan_sms_gateway"], $gateways );
		foreach( $gateways as $id => $gateway ) {
			foreach( $gateway['fields'] as $field ) {
				$result_settings[$id][$field] = $settings["bijan_sms_{$id}"][$field] ?? '';
			}
		}

		// Save messages settings
		$messages_settings = $settings['bijan_sms_settings'];

		// Auth: Login
		$result_settings['settings']['auth']['login']['enabled'] = !empty( $messages_settings['auth']['login']['enabled'] );
		$result_settings['settings']['auth']['login']['pattern'] = parent::convert_chars( $messages_settings['auth']['login']['pattern'] );
		$result_settings['settings']['auth']['login']['otp_timer'] = parent::convert_chars( $messages_settings['auth']['login']['otp_timer'], true, 'absint' );
		$result_settings['messages']['auth']['login'] = sanitize_textarea_field( $messages_settings['auth']['login']['message'] );

		// Auth: Register
		$result_settings['settings']['auth']['register']['enabled'] = !empty( $messages_settings['auth']['register']['enabled'] );
		$result_settings['settings']['auth']['register']['pattern'] = parent::convert_chars( $messages_settings['auth']['register']['pattern'] );
		$result_settings['settings']['auth']['register']['otp_timer'] = parent::convert_chars( $messages_settings['auth']['register']['otp_timer'], true, 'absint' );
		$result_settings['messages']['auth']['register'] = sanitize_textarea_field( $messages_settings['auth']['register']['message'] );

		$result_settings['settings']['auth']['one_form'] = $result_settings['settings']['auth']['login']['enabled'] && $result_settings['settings']['auth']['register']['enabled'] && !empty( $messages_settings['auth']['one_form'] );

		// Auth: Lost password
		$result_settings['settings']['auth']['lost_password']['enabled'] = !empty( $messages_settings['auth']['lost_password']['enabled'] );
		$result_settings['settings']['auth']['lost_password']['pattern'] = parent::convert_chars( $messages_settings['auth']['lost_password']['pattern'] );
		$result_settings['messages']['auth']['lost_password'] = sanitize_textarea_field( $messages_settings['auth']['lost_password']['message'] );

		update_option( 'bijan_sms_settings', $result_settings, false );
		do_action( 'bijan/sms/settings/updated', $result_settings );
		add_settings_error( 'bijan-sms-settings', 'updated', __( 'Settings updated', 'bijan' ), 'success' );
	}

	public static function auth_variables( $additional = [], $excludes = [] ) : array {
		$variables = [
			'otp'		=> __( "The OTP code", 'bijan' ),
			'end_time'	=> __( "The end time of the OTP code", 'bijan' ),
			'domain'	=> __( "The domain name", 'bijan' ),
			'name'		=> __( "The website name", 'bijan' ),
		];
		$variables = array_merge( $variables, $additional );
		return parent::unset( $variables, $excludes );
	}

	public static function reserve_variables() {
		return [
			'mentor_name'	=> __( 'Mentor name', 'bijan' ),
			'user_name'		=> __( 'User name', 'bijan' ),
			'plan'			=> __( 'Plan name', 'bijan' ),
			'domain'		=> __( "The domain name", 'bijan' ),
			'name'			=> __( "The website name", 'bijan' ),
		];
	}

	public static function security_variables() {
		return [
			'domain'	=> __( "The domain name", 'bijan' ),
			'name'		=> __( "The website name", 'bijan' ),
		];
	}

	public static function reminder_timing_options() {
		return [
			'30'	=> __( '30 minutes before', 'bijan' ),
			'60'	=> __( '1 hour before', 'bijan' ),
			'120'	=> sprintf( __( '%d hour before', 'bijan' ), 2 ),
			'180'	=> sprintf( __( '%d hour before', 'bijan' ), 3 ),
			'240'	=> sprintf( __( '%d hour before', 'bijan' ), 4 ),
			'360'	=> sprintf( __( '%d hour before', 'bijan' ), 5 ),
			'720'	=> sprintf( __( '%d hour before', 'bijan' ), 12 ),
			'1440'	=> sprintf( __( '%d hour before', 'bijan' ), 24 ),
			'2880'	=> __( '2 days before', 'bijan' ),
			'-1'	=> __( 'No reminder', 'bijan' ),
		];
	}

	public static function apply_variables( string $text, $to, string $type = '', array $custom_variables = [] ) {
		if( strpos( $text, "{otp}" ) !== false ) {
			$otp = rand( 1000, 9999 );
			$text = str_replace( "{otp}", $otp, $text );
			
			$timer = parent::get_nested_value( self::get_settings()['settings'], $type )['otp_timer'];
			$end_time = parent::convert_chars( date_i18n( 'U' ) ) + $timer;
			$end_time = date_i18n( "Y-m-d H:i:s", $end_time );
			$text = str_replace( "{end_time}", $end_time, $text );

			$otp_db = new OTP;
			$otp_db->updateOrCreate( [
				'mobile'	=> $to[0]
			], [
				'mobile'	=> $to[0],
				'otp'		=> $otp,
				'expire'	=> Date::maybe_j2g( parent::convert_chars( $end_time ) ),
			] );
		}

		$text = parent::apply_general_variables( $text, $custom_variables );

		return $text;
	}

	public static function hide_mobile_types() {
		return [
			'disabled'	=> esc_html__( "Disabled", 'bijan' ),
			'mid_star'	=> '0999***9999',
			'end_star'	=> '0999999****',
			'sitetitle'	=> esc_html__( "Site title", 'bijan' ),
			'custom'	=> esc_html__( "Custom", 'bijan' ),
		];
	}
}