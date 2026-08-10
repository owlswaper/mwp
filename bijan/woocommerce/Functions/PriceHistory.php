<?php
namespace Bijan\WooCommerce;

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\WC\PriceHistory as UtilsPriceHistory;

class PriceHistory {
	private static function get_time() {
		static $time = '';
		if( !$time ) {
			$time = current_time( 'U' );
		}
		return $time;
	}

	public static function save_by_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
		$options = Options::get_options( [
			'wc-price-history'	=> true,
		] );
		if( !$options['wc-price-history'] ) return;
		
		$time = self::get_time();
		
		if( get_post_type( $post_id ) == 'product' ) {
			if( in_array( $meta_key, ['_price', '_regular_price'] ) ) {
				UtilsPriceHistory::add( $post_id, $meta_value, $time );
			}
		}
	}
}
add_action( 'updated_post_meta', [PriceHistory::class, 'save_by_meta'], 10, 4 );