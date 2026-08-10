<?php
namespace Bijan\Utils\WC;

use Bijan\Model\PriceHistory as ModelPriceHistory;
use Bijan\Utils;

use MJ\Whitebox\Utils\Date as WhiteboxDate;

class PriceHistory extends Utils {
	public static function list( $product_id ) {
		$find = ModelPriceHistory::query()->select( 'history' )->where( 'product_id', $product_id )->first();
		$list = [];
		if( $find ) {
			$list = $find->history;
		}
		return $list;
	}

	public static function get_time( $time = 0 ) {
		if( !$time ) {
			$time = current_time( 'U' );
		} else {
			if( is_string( $time ) ) {
				$time = WhiteboxDate::maybe_j2g( $time );
				$time = strtotime( $time );
			} else if( $time instanceof \DateTime ) {
				$time = $time->format( 'U' );
			}
		}
		return absint( $time );
	}

	public static function add_price_to_list( $list, $price, $time = 0 ) {
		if( end( $list ) != $price ) {
			$time = self::get_time( $time );
			$list[$time] = floatval( $price );

			ksort( $list );
		}
		return $list;
	}

	public static function add( $product_id, $value, $time = 0 ) {
		$find = ModelPriceHistory::query()->where( 'product_id', $product_id )->first();
		$list = [];
		if( $find ) {
			$list = $find->history;
		} else {
			$find = new ModelPriceHistory;
		}
		$new_list = self::add_price_to_list( $list, $value, $time );
		if( $new_list != $list ) {
			$find->product_id = $product_id;
			$find->history = $new_list;
			$find->save();
		}
	}
}