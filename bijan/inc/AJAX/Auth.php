<?php
namespace Bijan\AJAX;

use Bijan\AJAX;
use Bijan\Model\OTP;
use Bijan\SMS\SMS;
use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\SMS as UtilsSMS;
use Bijan\Utils\User;

use MJ\Whitebox\Utils\Sanitizers as WhiteboxSanitizers;
use MJ\Whitebox\Utils\Date as WhiteboxDate;

class Auth extends AJAX {
	private const OTP_SEND_LIMIT = 3;
	private const OTP_VERIFY_LIMIT = 5;
	private const RATE_LIMIT_WINDOW = 900;
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	private function find_user_with_everything( $entry ) {
		$user = get_user_by( 'email', $entry );
		if( !$user ) {
			$user = get_user_by( 'login', $entry );
			if( !$user ) {
				$user = User::find_user_by_mobile( $entry );
				if( $user ) {
					$user = get_user_by( 'ID', $user );
				}
			}
		}
		return $user;
	}

	private function rate_limit_key( $action, $identifier = '' ) {
		$ip = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return 'bijan_' . $action . '_' . md5( $identifier . '|' . $ip );
	}

	private function is_rate_limited( $action, $identifier, $limit ) {
		return (int) get_transient( $this->rate_limit_key( $action, $identifier ) ) >= $limit;
	}

	private function record_rate_limit( $action, $identifier ) {
		$key = $this->rate_limit_key( $action, $identifier );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, self::RATE_LIMIT_WINDOW );
	}

	private function clear_rate_limit( $action, $identifier ) {
		delete_transient( $this->rate_limit_key( $action, $identifier ) );
	}

	public function login( $args = [] ) {
		$options = Options::get_options( [
			'auth-modal'	=> true,
			'auth_sms'		=> true,
		] );
		if( !Utils::to_bool( $options['auth-modal'] ) ) {
			$this->result( 'error', [
				'code'	=> 'disabled_auth',
				'msg'	=> esc_html__( 'Authentication is disabled', 'bijan' ),
			] );
		}
		
		if( empty( $args ) ) {
			$this->set_request_data();
		} else {
			$this->data = $args;
		}

		$username = sanitize_text_field( $this->data['username'] );

		$login = wp_signon( [
			'user_login'	=> $username,
			'user_password'	=> sanitize_text_field( $this->data['password'] ),
			'remember'		=> Utils::to_bool( $this->data['remember'] )
		] );
		if( is_wp_error( $login ) ) {
			$this->result( 'error', [
				'code'	=> 'invalid_credentials',
				'msg'	=> esc_html__( 'The username, email address, or password is incorrect.', 'bijan' ),
			] );
		} else {
			$this->result( 'success', [
				'code'	=> 'login_success',
				'msg'	=> esc_html__( 'Login successful. The page will reload.', 'bijan' ),
			] );
		}
	}

	public function signup() {
		$options = Options::get_options( [
			'auth-modal'	=> true,
			'auth_sms'		=> true,
		] );
		if( !Utils::to_bool( $options['auth-modal'] ) ) {
			$this->result( 'error', [
				'code'	=> 'disabled_auth',
				'msg'	=> esc_html__( 'Authentication is disabled', 'bijan' ),
			] );
		}

		$this->set_request_data();

		if( !empty( $this->data['mobile'] ) && $this->find_user_with_everything( $this->data['mobile'] ) ) {
			$this->result( 'error', [
				'code'	=> 'user_exists',
				'msg'	=> esc_html__( 'User already exists', 'bijan' ),
			] );
		}

		$user_id = User::create_user( $this->data['username'], $this->data['password'], $this->data['email'], !empty( $this->data['mobile'] ) ? $this->data['mobile'] : '' );
		if( is_wp_error( $user_id ) ) {
			$this->result( 'error', [
				'code'	=> array_keys( $user_id->errors )[0],
				'msg'	=> $user_id->get_error_message(),
			] );
		} else {
			$this->login( [
				'username'	=> $this->data['username'],
				'password'	=> $this->data['password'],
				'remember'	=> true
			] );
			$this->result( 'success', [
				'code'	=> 'user_created',
				'msg'	=> esc_html__( 'Your account has been created. The page will reload.', 'bijan' ),
			] );
		}
	}

	public function send_otp() {
		$options = Options::get_options( [
			'auth-modal'	=> true,
			'auth_sms'		=> true,
		] );
		if( !Utils::to_bool( $options['auth-modal'] ) || !Utils::to_bool( $options['auth_sms'] ) ) {
			$this->result( 'error', [
				'code'	=> 'disabled_auth',
				'msg'	=> esc_html__( 'Authentication is disabled', 'bijan' ),
			] );
		}

		$this->set_request_data();

		$options = Options::get_options( [
			'sms'	=> true,
		] );
		if( !Utils::to_bool( $options['sms'] ) ) {
			$this->result( 'error', [
				'code'	=> 'sms_not_active',
				'msg'	=> esc_html__( 'SMS is not active.', 'bijan' ),
			] );
		}

		$sms_settings = UtilsSMS::get_settings();

		$mobile = WhiteboxSanitizers::phone( $this->data['mobile'] );
		if ( ! $mobile || $this->is_rate_limited( 'otp_send', $mobile, self::OTP_SEND_LIMIT ) ) {
			$this->result( 'error', [
				'code' => 'rate_limited',
				'msg' => esc_html__( 'Please wait before requesting another verification code.', 'bijan' ),
			] );
		}
		$user = User::find_user_by_mobile( $mobile );
		if( $user ) { // Login
			if( empty( $sms_settings['settings']['auth']['login']['enabled'] ) ) {
				$this->result( 'error', [
					'code'	=> 'login_not_active',
					'msg'	=> esc_html__( 'Login via SMS is not active.', 'bijan' ),
				] );
			}
			$result = SMS::send( $mobile, 'auth.login' );
		} else { // Register
			if( empty( $sms_settings['settings']['auth']['register']['enabled'] ) ) {
				$this->result( 'error', [
					'code'	=> 'register_not_active',
					'msg'	=> esc_html__( 'Register via SMS is not active.', 'bijan' ),
				] );
			}
			$result = SMS::send( $mobile, 'auth.register' );
		}
		if ( is_wp_error( $result ) ) {
			$this->result( 'error', [ 'code' => 'sms_failed', 'msg' => esc_html__( 'Unable to send the verification code. Please try again later.', 'bijan' ) ] );
		}
		$this->record_rate_limit( 'otp_send', $mobile );
		$this->result( 'success', [
			'code'	=> 'otp_sent',
			'msg'	=> esc_html__( 'The verification code has been sent to your mobile number.', 'bijan' ),
			'mode'	=> $user ? 'login' : 'register',
		] );
	}

	public function check_otp() {
		$options = Options::get_options( [
			'auth-modal'	=> true,
			'auth_sms'		=> true,
		] );
		if( !Utils::to_bool( $options['auth-modal'] ) || !Utils::to_bool( $options['auth_sms'] ) ) {
			$this->result( 'error', [
				'code'	=> 'disabled_auth',
				'msg'	=> esc_html__( 'Authentication is disabled', 'bijan' ),
			] );
		}

		$this->set_request_data();

		$mobile = WhiteboxSanitizers::phone( $this->data['mobile'] );
		$otp = WhiteboxSanitizers::otp( $this->data['otp'] );
		if ( ! $mobile || $this->is_rate_limited( 'otp_verify', $mobile, self::OTP_VERIFY_LIMIT ) ) {
			$this->result( 'error', [
				'code' => 'rate_limited',
				'message' => esc_html__( 'Too many failed attempts. Please request a new verification code later.', 'bijan' ),
			] );
		}

		$find_otp = OTP::query()->where( [
			['mobile', $mobile],
			['otp', $otp],
			['expire', '>', WhiteboxDate::maybe_j2g( wp_date( 'Y-m-d H:i:s' ) )],
		] )->first();

		// OTP expired
		if( !$find_otp ) {
			$this->record_rate_limit( 'otp_verify', $mobile );
			$this->result( 'error', [
				'code'		=> 'otp_not_match',
				'message'	=> esc_html__( 'OTP code does not match', 'bijan' ),
			] );
		}

		$sms_settings = UtilsSMS::get_settings();

		$user = User::find_user_by_mobile( $mobile );
		if( $user ) { // Login
			if( empty( $sms_settings['settings']['auth']['login']['enabled'] ) ) {
				$this->result( 'error', [
					'code'	=> 'login_not_active',
					'msg'	=> esc_html__( 'Login via SMS is not active.', 'bijan' ),
				] );
			}
		} else { // Register
			if( empty( $sms_settings['settings']['auth']['register']['enabled'] ) || empty( $sms_settings['settings']['auth']['one_form'] ) ) {
				$this->result( 'error', [
					'code'	=> 'register_not_active',
					'msg'	=> esc_html__( 'Register via SMS is not active.', 'bijan' ),
				] );
			}

			$user_id = User::create_user( $mobile, '', '', $mobile );
			if ( is_wp_error( $user_id ) ) {
				$this->result( 'error', [
					'code' => 'user_creation_failed',
					'msg' => esc_html__( 'Unable to create your account. Please try again later.', 'bijan' ),
				] );
			}
			$user = get_user_by( 'id', $user_id );
		}
		if( $user && ! is_wp_error( $user ) ) {
			wp_set_current_user( $user->ID, $user->user_login );
			wp_set_auth_cookie( $user->ID, true );
			do_action( 'wp_login', $user->user_login, $user );

			$find_otp->delete();
			$this->clear_rate_limit( 'otp_verify', $mobile );

			$this->result( 'success', [
				'code'	=> 'login_success',
				'msg'	=> esc_html__( 'Login successful. The page will reload.', 'bijan' ),
			] );
		} else {
			$this->result( 'error', [
				'code'	=> 'user_creation_failed',
				'msg'	=> esc_html__( 'Unable to create your account. Please try again later.', 'bijan' ),
			] );
		}
	}

	public function lost_password() {
		$options = Options::get_options( [
			'auth-modal'	=> true,
			'auth_sms'		=> true,
		] );
		if( !Utils::to_bool( $options['auth-modal'] ) ) {
			$this->result( 'error', [
				'code'	=> 'disabled_auth',
				'msg'	=> esc_html__( 'Authentication is disabled', 'bijan' ),
			] );
		}
		$this->set_request_data();

		$entry = sanitize_text_field( $this->data['entry'] );
		if ( $this->is_rate_limited( 'password_reset', $entry, 3 ) ) {
			$this->result( 'error', [ 'code' => 'rate_limited', 'msg' => esc_html__( 'Please wait before requesting another password reset email.', 'bijan' ) ] );
		}
		$user = $this->find_user_with_everything( $entry );
		$login = $user ? $user->user_login : $entry;
		retrieve_password( $login );
		$this->record_rate_limit( 'password_reset', $entry );
		$this->result( 'success', [
			'code' => 'password_reset_requested',
			'msg' => esc_html__( 'If an account matches this information, a password reset link has been sent.', 'bijan' ),
		] );
	}
}
