<?php
namespace Bijan\PostTypes;

use Bijan\Utils;
use Bijan\Utils\Story as UtilsStory;

class Story {
	PRIVATE STATIC $POST_TYPE_NAME = 'story'; // Singular word

	public static function register() {
		$labels = [
			'name'					=> __( 'Stories', 'bijan' ),
			'singular_name'			=> __( 'Story', 'bijan' ),
			'menu_name'				=> __( 'Stories', 'bijan' ),
			'name_admin_bar'		=> __( 'Stories', 'bijan' ),
			'add_new'				=> __( 'Add New story', 'bijan' ),
			'add_new_item'			=> __( 'Add New story', 'bijan' ),
			'new_item'				=> __( 'New story', 'bijan' ),
			'edit_item'				=> __( 'Edit story', 'bijan' ),
			'all_items'				=> __( 'All stories', 'bijan' ),
			'search_items'			=> __( 'Search stories', 'bijan' ),
			'not_found'				=> __( 'No story found', 'bijan' ),
			'not_found_in_trash'	=> __( 'No story found', 'bijan' ),
		];
		$args = [
			'labels'				=> $labels,
			'public'				=> false,
			'show_in_rest'			=> false,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'menu_icon'				=> "dashicons-images-alt",
			'capability_type'		=> 'post',
			'has_archive'			=> false,
			'hierarchical'			=> false,
			'supports'				=> ['title'],
			'exclude_from_search'	=> true,
		];
		register_post_type( self::$POST_TYPE_NAME, $args );
	}

	private static function _columns() {
		return [
			'small_img'	=> esc_html__( 'Small image', 'bijan' ),
			'post'		=> esc_html__( 'Related post or product', 'bijan' ),
			'likes'		=> esc_html__( 'Likes', 'bijan' ),
			'views'		=> esc_html__( 'Views', 'bijan' ),
		];
	}

	public static function columns( $columns ) {
		$columns = array_merge( $columns, self::_columns() );

		Utils::reposition_array_element( $columns, 'small_img', 1 );

		return $columns;
	}

	public static function columns_value( $column, $post_id ) {
		if( in_array( $column, array_keys( self::_columns() ) ) ) {
			$story = UtilsStory::get( $post_id );
			if( $column == 'small_img' ) {
				echo wp_get_attachment_image( $story['small_img'], [96, 96] );
			} else if( $column == 'post' ) {
				if( $story['post'] ) {
					$post = get_post( $story['post'] );
					echo '<a href="' . get_edit_post_link( $post ) . '">' . esc_html( $post->post_title ) . '</a>';
				}
			} else if( $column == 'likes' ) {
				echo number_format_i18n( $story['likes_count'] );
			} else if( $column == 'views' ) {
				echo number_format_i18n( UtilsStory::get_views( $post_id ) );
			}
		}
	}
}
Story::register();
add_filter( "manage_story_posts_columns", [Story::class, 'columns'] );
add_action( "manage_story_posts_custom_column", [Story::class, 'columns_value'], 10, 2 );