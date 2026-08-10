<?php
namespace Bijan;

use MJ\Whitebox\Utils as WhiteboxUtils;

class AJAX {
	public $data = [];

	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	private function __construct() {
		if( !wp_doing_ajax() ) return;

		if( BIJAN_DEV ) {
			WhiteboxUtils::show_errors();
		}

		// Without _ at the end
		$action_prefix = 'bijan';

		// Fill this with you actions - prefix will automatically added
		/**
		 * Key for action name	=> [
		 * 	file		=> string File address. By default it will use the key name as filename by PascalCase template.
		 * 	class		=> string Name of the class. By default it will use the key name as class name by PascalCase template.
		 * 	guest		=> boolean Run this action for guest users [Default: true]
		 * 	user		=> boolean Run this action for logged in users. [Default: true]
		 * 	need_login	=> boolean Send need login message for guest users. [Default: true]
		 * 	function	=> string Custom name for function. [Default: 'view']
		 * 	nonce		=> string Nonce name of this action. If nonce is empty it will not check the nonce
		 * 	requires	=> array[mixed] List of requires keys in HTTP request(POST | REQUEST(On Dev mode))
		 * ]
		 */
		$default_action = [
			'file'			=> '',
			'class'			=> '',
			'guest'			=> true,
			'user'			=> true,
			'need_login'	=> true,
			'function'		=> '',
			'nonce'			=> '',
			'requires'		=> [],
		];
		$actions = [
			'update_mini_cart'	=> [
				'file'			=> 'MiniCartSetQTY',
				'class'			=> 'MiniCartSetQTY',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'update',
				'requires'		=> ['nonce', 'item_key', 'item_qty'],
			],
			'login' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> BIJAN_DEV,
				'need_login'	=> false,
				'function'		=> 'login',
				'nonce'			=> 'bijan-auth-login',
				'requires'		=> ['username', 'password'],
			],
			'signup' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> BIJAN_DEV,
				'need_login'	=> false,
				'function'		=> 'signup',
				'nonce'			=> 'bijan-auth-signup',
				'requires'		=> ['username', 'email', 'password'],
			],
			'send_otp' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> BIJAN_DEV,
				'need_login'	=> false,
				'function'		=> 'send_otp',
				'nonce'			=> 'bijan-auth-mobile',
				'requires'		=> ['mobile'],
			],
			'check_otp' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> BIJAN_DEV,
				'need_login'	=> false,
				'function'		=> 'check_otp',
				'nonce'			=> 'bijan-auth-otp',
				'requires'		=> ['mobile', 'otp'],
			],
			'lost_password' => [
				'file'			=> 'Auth',
				'class'			=> 'Auth',
				'guest'			=> true,
				'user'			=> BIJAN_DEV,
				'need_login'	=> false,
				'function'		=> 'lost_password',
				'nonce'			=> 'bijan-auth-lost_password',
				'requires'		=> ['entry'],
			],
			'search' => [
				'file'			=> 'Search',
				'class'			=> 'Search',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'query',
				'nonce'			=> 'bijan-search-query',
				'requires'		=> ['text'],
			],
			'find_post' => [
				'file'			=> 'FindPost',
				'class'			=> 'FindPost',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'query',
				'nonce'			=> 'bijan-metabox-post-finder',
				'requires'		=> ['text'],
			],
			'find_user' => [
				'file'			=> 'FindUser',
				'class'			=> 'FindUser',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'query',
				'nonce'			=> 'bijan-metabox-user-finder',
				'requires'		=> ['text'],
			],
			'story' => [
				'file'			=> 'Story',
				'class'			=> 'Story',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'content',
				'requires'		=> ['id', 'nonce'],
			],
			'toggle_like_story' => [
				'file'			=> 'Story',
				'class'			=> 'Story',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'toggle_like',
				'requires'		=> ['id', 'nonce'],
			],
			'story_like_html'	=> [
				'file'			=> 'Story',
				'class'			=> 'Story',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'get_likes_html',
				'requires'		=> ['id', 'nonce'],
			],
			'toggle_wishlist'	=> [
				'file'			=> 'Wishlist',
				'class'			=> 'Wishlist',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'toggle',
				'requires'		=> ['nonce', 'product_id'],
			],
			'set_notification_read'	=> [
				'file'			=> 'Notifications',
				'class'			=> 'Notifications',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> true,
				'function'		=> 'set_read',
				'requires'		=> ['id'],
			],
			'get_notices'	=> [
				'file'			=> 'Notices',
				'class'			=> 'Notices',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'get',
			],
			'dismiss_notice'	=> [
				'file'			=> 'Notices',
				'class'			=> 'Notices',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'dismiss',
				'nonce'			=> 'bijan_dismiss_notice',
				'requires'		=> ['id'],
			],
			'icon_picker'		=> [
				'file'			=> 'IconPicker',
				'class'			=> 'IconPicker',
				'guest'			=> false,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'html',
				'nonce'			=> 'bijan-icon-picker',
			],
			'compare_popup'		=> [
				'file'			=> 'Compare',
				'class'			=> 'Compare',
				'guest'			=> true,
				'user'			=> true,
				'need_login'	=> false,
				'function'		=> 'html',
				'nonce'			=> 'bijan-add-compare',
			],
		];
		$dir = BIJAN_DIR . "inc/AJAX/";

		$this->set_request_data();
		if( empty( $this->data['action'] ) ) return;

		foreach( $actions as $key => $data ) {
			$action = $default_action;
			if( is_array( $data ) ) {
				$action = WhiteboxUtils::check_default( $data, $default_action );
			}

			// Prepare filename to include
			if( empty( $action['file'] ) ) {
				$action['file'] = $data;
			}
			$action['file'] = WhiteboxUtils::convert_to_pascal_case( $action['file'] );
			include_once( $dir . $action['file'] . ".php" );

			if( empty( $action['class'] ) ) {
				$action['class'] = $data;
			}
			$action['class'] = "\Bijan\AJAX\\" . WhiteboxUtils::convert_to_pascal_case( $action['class'] );
			
			$actions[$key] = $action;
		}
		$action_name_without_prefix = str_replace( "{$action_prefix}_", '', $this->data['action'] );
		if( !in_array( $action_name_without_prefix, array_keys( $actions ) ) ) return;
		
		$action = $actions[$action_name_without_prefix];

		if( $action['need_login'] ) {
			add_action( "wp_ajax_nopriv_{$this->data['action']}", [$this, 'need_login'] );
		}

		if( !empty( $action['nonce'] ) ) {
			$this->check_nonce( $action['nonce'] );
		}

		if( !empty( $action['requires'] ) ) {
			$this->check_requires( $action['requires'] );
		}

		if( $action['guest'] ) {
			if( !$action['need_login'] ) {
				add_action( "wp_ajax_nopriv_{$this->data['action']}", [$action['class']::get_instance(), $action['function']] );
			}
		}
		if( $action['user'] ) {
			add_action( "wp_ajax_{$this->data['action']}", [$action['class']::get_instance(), $action['function']] );
		}
	}

	/**
	 * Send result to response
	 *
	 * @param string $type Accepts: error | success
	 * @param mixed $data Your data
	 * @return void
	 */
	public function result( $type, $data = '' ) {
		if( $type == 'error' ) {
			wp_send_json_error( $data );
		} else {
			wp_send_json_success( $data );
		}
		die;
	}

	/**
	 * Array of nonce error
	 *
	 * @return array
	 */
	public function nonce_error() {
		return [
			'code'		=> 'security_error',
			'message'	=> __( "Security error", 'bijan' ),
		];
	}

	/**
	 * Automatically select the HTTP method
	 *
	 * @return array
	 */
	public function set_request_data() {
		$this->data = array_change_key_case( BIJAN_DEV ? $_REQUEST : $_POST );
		return $this;
	}

	/**
	 * Check nonce
	 *
	 * @param string $action The nonce action
	 * @param boolean $send_error Automatically send error in response or return boolean
	 * @return void|boolean
	 */
	public function check_nonce( $action, $send_error = true ) {
		$result = !empty( $this->data ) && !empty( $this->data['nonce'] ) && wp_verify_nonce( WhiteboxUtils::convert_chars( $this->data['nonce'] ), $action );
		if( $result ) {
			return true;
		} else {
			if( $send_error ) {
				$this->result( 'error', $this->nonce_error() );
			} else {
				return false;
			}
		}
	}

	/**
	 * Array of requires error
	 *
	 * @param array $requires List of requires
	 * @return array
	 */
	public function requires_error( $requires ) : array {
		$requires = array_map( function( $require ) {
			return str_replace( '_', ' ', $require );
		}, $requires );
		$requires = implode( ", ", $requires );
		return [
			'code'		=> 'invalid_requires',
			'message'	=> sprintf( __( '%s are required', 'bijan' ), $requires ),
		];
	}

	/**
	 * Check data requires
	 *
	 * @param array[string] $requires List of required keys
	 * @param boolean $send_error Automatically send error in response or return boolean
	 * @return boolean
	 */
	public function check_requires( $requires, $send_error = true ) : bool {
		if( WhiteboxUtils::check_requires( $this->data, $requires ) ) {
			return true;
		} else {
			if( $send_error ) {
				$this->result( 'error', $this->requires_error( $requires ) );
			}
			return false;
		}
	}

	public function need_login() : void {
		$this->result( 'error', [
			'code'		=> 'forbidden',
			'message'	=> '',
		] );
	}
}
AJAX::get_instance();