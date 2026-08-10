<?php
namespace Bijan\Utils;

use MJ\Whitebox\Utils\Sanitizers as WhiteboxSanitizers;

class Sanitizers extends WhiteboxSanitizers {
	public static function phone($string): string {
		$phone = parent::convert_chars( $string );
		$phone = str_replace( '+98', '0', $phone );
		return str_replace( ' ', '', $phone );
	}
}