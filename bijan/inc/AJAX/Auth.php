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

		$username = sanitize_user( $this->data['username'] );
		if( !$this->find_user_with_everything( $username ) ) {
			$this->result( 'error', [
				'code'	=> 'user_not_found',
				'msg'	=> esc_html__( 'User not found', 'bijan' ),
			] );
		}

		$login = wp_signon( [
			'user_login'	=> $username,
			'user_password'	=> sanitize_text_field( $this->data['password'] ),
			'remember'		=> Utils::to_bool( $this->data['remember'] )
		] );
		if( is_wp_error( $login ) ) {
			$this->result( 'error', [
				'code'	=> array_keys( $login->errors )[0],
				'msg'	=> $login->get_error_message(),
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
		$user = User::find_user_by_mobile( $mobile );
		if( $user ) { // Login
			if( empty( $sms_settings['settings']['auth']['login']['enabled'] ) ) {
				$this->result( 'error', [
					'code'	=> 'login_not_active',
					'msg'	=> esc_html__( 'Login via SMS is not active.', 'bijan' ),
				] );
			}
			SMS::send( $mobile, 'auth.login' );
		} else { // Register
			if( empty( $sms_settings['settings']['auth']['register']['enabled'] ) ) {
				$this->result( 'error', [
					'code'	=> 'register_not_active',
					'msg'	=> esc_html__( 'Register via SMS is not active.', 'bijan' ),
				] );
			}
			SMS::send( $mobile, 'auth.register' );
		}
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

		Utils::show_errors();

		$mobile = WhiteboxSanitizers::phone( $this->data['mobile'] );
		$otp = WhiteboxSanitizers::otp( $this->data['otp'] );

		$find_otp = OTP::query()->where( [
			['mobile', $mobile],
			['otp', $otp],
			['expire', '>', WhiteboxDate::maybe_j2g( wp_date( 'Y-m-d H:i:s' ) )],
		] )->first();

		// OTP expired
		if( !$find_otp ) {
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
			$user = get_user_by( 'id', $user_id );
		}
		if( !is_wp_error( $user ) ) {
			wp_set_current_user( $user->ID, $user->user_login );
			wp_set_auth_cookie( $user->ID, true );
			do_action( 'wp_login', $user->user_login, $user );

			$find_otp->delete();

			$this->result( 'success', [
				'code'	=> 'login_success',
				'msg'	=> esc_html__( 'Login successful. The page will reload.', 'bijan' ),
			] );
		} else {
			$this->result( 'error', [
				'code'	=> array_keys( $user_id->errors )[0],
				'msg'	=> $user_id->get_error_message(),
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

		$user = $this->find_user_with_everything( $this->data['entry'] );
		if( $user ) {
			$user_id = $user->ID;
			$new_password = wp_generate_password( 8, false );
			$update = wp_update_user( [ 'ID' => $user_id, 'user_pass' => $new_password ] );
			if( is_wp_error( $update ) ) {
				$this->result( 'error', [
					'code'	=> array_keys( $update->errors )[0],
					'msg'	=> $update->get_error_message(),
				] );
			} else {
				$options = Options::get_options( [
					'lost-password-email-subject'	=> '',
					'lost-password-email-template'	=> '',
				] );

				if( empty( $options['lost-password-email-template'] ) ) {
					$options['lost-password-email-template'] = get_bloginfo( 'name' ) . "<br>" . __( 'Your new password is: <strong>{password}</strong>', 'bijan' );
				}

				$email_msg = str_replace( "{password}", $new_password, $options['lost-password-email-template'] );

				if( $user->user_email ) {
					wp_mail( $user->user_email, wp_strip_all_tags( $options['lost-password-email-subject'] ), $email_msg );
				}

				$options = Options::get_options( [
					'sms'	=> true,
				] );
				if( Utils::to_bool( $options['sms'] ) ) {
					$sms_settings = UtilsSMS::get_settings();
					if( !empty( $sms_settings['settings']['auth']['lost_password']['enabled'] ) ) {
						$mobile = User::get_user_mobile( $user_id );
						if( $mobile ) {
							SMS::send( User::get_user_mobile( $user_id ), 'auth.lost_password', [ 'password' => $new_password ] );
						}
					}
				}

				$this->result( 'success', [
					'code'	=> 'new_password_sent',
					'msg'	=> esc_html__( 'Your new password has been sent to your email address.', 'bijan' ),
				] );
			}
		} else {
			$this->result( 'error', [
				'code'	=> 'user_not_found',
				'msg'	=> esc_html__( 'User not found', 'bijan' ),
			] );
		}
	}
}