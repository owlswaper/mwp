<?php
namespace Bijan\SMS;

use Bijan\Utils\SMS;

class Farapayamak extends Gateway {
	public function __construct( $service = false ) {
		$settings = SMS::get_settings();
		parent::__construct( [
			'id'			=> 'farapayamak',
			'url_template'	=> 'https://rest.payamak-panel.com/api/SendSMS',
			'username'		=> $service ? $settings['farapayamak_service']['username'] : $settings['farapayamak']['username'],
			'password'		=> $service ? $settings['farapayamak_service']['password'] : $settings['farapayamak']['password'],
			'from'			=> $service ? '' : $settings['farapayamak']['from'],
		] );

		return $this;
	}

	protected function prepare() {
		$this->set_data( [
			'request_options'	=> [
				'httpversion'	=> '1.1',
				'timeout'		=> 60,
				'headers'	=> [
					'content-type'	=> 'application/x-www-form-urlencoded'
				],
			],
		] );

		return $this;
	}

	public function _send() {
		$request_args = $this->request_options;
		$request_args['body'] = http_build_query( [
			'username'	=> $this->username,
			'password'	=> $this->password,
			'from'		=> $this->from,
			'to'		=> implode( ',', $this->to ),
			'text'		=> $this->text,
		] );
		$request = wp_remote_post( "{$this->base_url}/SendSMS", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}

	public function _send_by_pattern( string $pattern ) {
		$request_args = $this->request_options;
		$request_args['body'] = http_build_query( [
			'username'	=> $this->username,
			'password'	=> $this->password,
			'to'		=> $this->to[0],
			'text'		=> $this->text,
			'bodyId'	=> $pattern,
		] );
		$request = wp_remote_post( "{$this->base_url}/BaseServiceNumber", $request_args );
		if( is_wp_error( $request ) ) {
			$this->wp_error = $request;
		}
		return $this;
	}
}