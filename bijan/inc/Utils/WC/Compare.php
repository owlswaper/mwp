<?php
namespace Bijan\Utils\WC;

use Bijan\Utils;

class Compare extends Utils {
	public static function get_products_from_cookie() {
		return !empty( $_COOKIE['bijan_compare_products'] ) ? explode( ",", urldecode( $_COOKIE['bijan_compare_products'] ) ) : [];
	}

	public static function set_products_in_cookie( array $products ) {
		setcookie(
			'bijan_compare_products',
			implode( ",", $products ),
			current_time( 'U' )+HOUR_IN_SECONDS,
			COOKIEPATH,
			COOKIE_DOMAIN
		);
	}

	public static function add_product_to_cookie( $product_id ) {
		$products = self::get_products_from_cookie();
		if( !in_array( $product_id, $products ) ) {
			if( count( $products ) < 3 ) {
				$products[] = $product_id;
			} else {
				array_unshift( $products, $product_id );
			}
			$products = array_slice( $products, 0, 3 );
			$products = array_values( $products );
		}

		self::set_products_in_cookie( $products );

		return $products;
	}

	public static function remove_product_from_cookie( $product_id ) {
		$products = self::get_products_from_cookie();
		if( in_array( $product_id, $products ) ) {
			unset( $products[array_search( $product_id, $products, true )] );
			$products = array_values( $products );
			self::set_products_in_cookie( $products );
		}

		return $products;
	}
}