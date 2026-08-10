<?php
namespace Bijan\Casts;

use MJ\Whitebox\Utils\Sanitizers;
use MJ\Whitebox\Utils\Validators;
use MJ\WPORM\Casts\CastableInterface;

class Mobile implements CastableInterface {
	public function get( $value ) {
		return Sanitizers::phone( $value );
	}

	public function set( $value ) {
		if( Validators::phone( $value ) ) {
			$value = Sanitizers::phone( $value );
		}
		return $value;
	}
}