<?php
namespace Bijan\Utils;

use MJ\Whitebox\Utils\Options as WhiteboxOptions;

class Options extends WhiteboxOptions {
	public static function get_option_name() {
		return 'bijan';
	}
}