<?php
namespace Bijan\AJAX;

use Bijan\AJAX;

class FindUser extends AJAX {
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

	public function query() {
		$this->set_request_data();
		
		$this->data['text'] = sanitize_text_field( $this->data['text'] );

		$args = [
			'search'	=> "*{$this->data['text']}*",
			'fields'	=> ['ID', 'display_name'],
		];
		$get_users = get_users( $args );
		$users = [];
		foreach( $get_users as $user ) {
			$users[] = [
				'id'	=> $user->ID,
				'text'	=> $user->display_name
			];
		}

		$this->result( 'success', $users );
	}
}