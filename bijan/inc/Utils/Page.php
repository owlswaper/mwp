<?php
namespace Bijan\Utils;

use Bijan\Utils;
use MJ\Whitebox\Utils\Posts as WhiteboxPosts;

class Page extends Utils {
	public static function default_options() {
		return [
			'disable_header'		=> false,
			'disable_header_user'	=> 'all',
			'show_breadcrumb'		=> true,
			'show_title'			=> true,
			'show_sidebar'			=> true,
			'fullwidth'				=> false,
			'use_content_style'		=> true,
			'disable_footer'		=> false,
			'disable_footer_user'	=> 'all',
			'page_icon'				=> 'bijan-icon-flash',
			'sidebar'				=> 'page',
		];
	}

	public static function get_options( $post_id = null ) {
		static $options = null;
		if( $options === null ) {
			$options = WhiteboxPosts::get_post_options( self::default_options(), $post_id );
		}
		return $options;
	}

	public static function save_options( array $options, $post_id = null ) {
		WhiteboxPosts::save_post_options( $options, self::default_options(), $post_id );
	}
}