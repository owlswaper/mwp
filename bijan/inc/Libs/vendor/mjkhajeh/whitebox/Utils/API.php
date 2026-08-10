<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class API extends Utils {
	/**
	 * Convert all keys of array to lowercase recursively
	 *
	 * @param array $data
	 * @return array
	 */
	public static function to_lower_before_response( array $data ) : array {
		if( !is_array( $data ) ) return $data;

		$data = array_change_key_case( $data, CASE_LOWER );
		foreach( $data as $key => $value ) {
			if( is_array( $value ) ) {
				$data[$key] = self::to_lower_before_response( $data[$key] );
			}
		}

		return $data;
	}
}