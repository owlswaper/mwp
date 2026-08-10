<?php
namespace Bijan\Utils;

use MJ\Whitebox\Utils\Elementor as WhiteboxElementor;

class Elementor extends WhiteboxElementor {
	public static function button_types( $args = [] ) {
		$types = parent::button_types( $args );
		unset( $types['white'] );
		return $types;
	}
}