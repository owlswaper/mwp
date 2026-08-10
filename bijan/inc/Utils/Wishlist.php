<?php
namespace Bijan\Utils;

use Bijan\Model\Wishlist as ModelWishlist;
use Bijan\Utils;
use MJ\Whitebox\Utils\Users as WhiteboxUsers;

class Wishlist extends Utils {
	public static function get_user_wishlist( $user_id = 0, array $db_args = [] ) {
		if( !is_user_logged_in() ) return [];
		$user_id = WhiteboxUsers::get_user_id( $user_id );

		$wishlist = ModelWishlist::query()->where( 'user_id', $user_id );
		if( !empty( $db_args['columns'] ) ) {
			$wishlist = $wishlist->select( $db_args['columns'] );
		}
		if( !empty( $db_args['where'] ) ) {
			$wishlist = $wishlist->where( $db_args['where'] );
		}
		if( !empty( $db_args['limit'] ) ) {
			$wishlist = $wishlist->limit( $db_args['limit'] );
		}
		if( !empty( $db_args['offset'] ) ) {
			$wishlist = $wishlist->offset( $db_args['offset'] );
		}

		$wishlist = $wishlist->orderBy( 'created_at', 'DESC' );

		$wishlist = $wishlist->get();

		return $wishlist;
	}

	public static function is_in_wishlist( $product_id, $user_id = 0 ) {
		if( !is_user_logged_in() ) return false;
		
		$user_id = WhiteboxUsers::get_user_id( $user_id );

		$wishlist = ModelWishlist::query()->where( [
			'product_id'	=> $product_id,
			'user_id'		=> $user_id
		] )->first();

		return !empty( $wishlist );
	}

	public static function add_to_wishlist( $product_id, $user_id = 0 ) {
		$user_id = WhiteboxUsers::get_user_id( $user_id );
		
		$wishlist = new ModelWishlist( [
			'product_id'	=> $product_id,
			'user_id'		=> $user_id,
		] );
		return $wishlist->save();
	}

	public static function remove_from_wishlist( $product_id, $user_id = 0 ) {
		$user_id = WhiteboxUsers::get_user_id( $user_id );

		$item = ModelWishlist::query()->where( [
			'product_id'	=> parent::convert_chars( $product_id, true, 'absint' ),
			'user_id'		=> parent::convert_chars( $user_id, true, 'absint' ),
		] )->first();
		if( $item ) {
			$item->delete();
		}
		return true;
	}
}