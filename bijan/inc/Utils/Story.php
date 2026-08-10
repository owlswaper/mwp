<?php
namespace Bijan\Utils;

use Bijan\Utils;
use MJ\Whitebox\Utils\Posts as WhiteboxPosts;
use MJ\Whitebox\Utils\Users as WhiteboxUsers;

class Story extends Utils {
	private static $cache = [];

	public static function default_options() {
		return [
			'small_img'		=> 0,
			'attachment'	=> 0,
			'post'			=> 0,
			'likes'			=> [],
			'views'			=> 0,
		];
	}

	public static function get_options( $post_id = 0 ) {
		return WhiteboxPosts::get_post_options( self::default_options(), $post_id );
	}

	public static function save_options( array $options, $post_id = 0 ) {
		WhiteboxPosts::save_post_options( $options, self::default_options(), $post_id );
	}

	public static function get( $post = null ) {
		$post = WhiteboxPosts::get_post( $post );
		$story = isset( self::$cache[$post->ID] ) ? self::$cache[$post->ID] : [];
		if( !$story ) {
			$options = self::get_options( $post->ID );
			$story = array_merge( [
				'id'			=> $post->ID,
				'title'			=> $post->post_title,
				'likes_count'	=> self::get_likes_count( $post->ID, $options['likes'] ),
			], $options );
			self::$cache[$post->ID] = $story;
		}
		return $story;
	}

	public static function get_views( $post_id = 0 ) {
		$post_id = WhiteboxPosts::get_post_id( $post_id );
		$views = get_post_meta( $post_id, '_views', true );
		return absint( $views );
	}

	public static function add_view( $post_id = 0 ) {
		$post_id = WhiteboxPosts::get_post_id( $post_id );
		$views = self::get_views( $post_id );
		$views++;
		update_post_meta( $post_id, '_views', $views );
		return $views;
	}

	public static function get_likes( $post_id = 0 ) {
		$post_id = WhiteboxPosts::get_post_id( $post_id );
		$likes = get_post_meta( $post_id, '_likes', true );
		if( !is_array( $likes ) ) $likes = [];

		return array_map( 'absint', $likes );
	}

	public static function get_likes_count( $post_id = 0, $likes = null ) {
		if( $likes === null ) {
			$likes = self::get_likes( $post_id );
		}
		return count( $likes );
	}

	public static function is_user_liked( $post_id = 0, $user_id = 0, $likes = null ) {
		$user_id = WhiteboxUsers::get_user_id( $user_id );

		if( $likes === null ) {
			$likes = self::get_likes( $post_id );
		}

		return in_array( $user_id, $likes );
	}

	public static function toggle_like( $post_id = 0, $user_id = 0 ) {
		$post_id = WhiteboxPosts::get_post_id( $post_id );
		$user_id = WhiteboxUsers::get_user_id( $user_id );
		
		$likes = self::get_likes( $post_id );
		if( self::is_user_liked( $post_id, $user_id, $likes ) ) {
			$key = array_search( $user_id, $likes );
			unset( $likes[$key] );
		} else {
			$likes[] = $user_id;
		}

		update_post_meta( $post_id, '_likes', $likes );
		return $likes;
	}
}