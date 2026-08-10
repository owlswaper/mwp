<?php
namespace Bijan\PostTypes;

use Bijan\Utils\Notifications;

class Notification {
	PRIVATE STATIC $POST_TYPE_NAME = 'notification'; // Singular word

	public static function register() {
		$labels = [
			'name'					=> __( 'Notifications', 'bijan' ),
			'singular_name'			=> __( 'Notification', 'bijan' ),
			'menu_name'				=> __( 'Notifications', 'bijan' ),
			'name_admin_bar'		=> __( 'Notifications', 'bijan' ),
			'add_new'				=> __( 'Add New notification', 'bijan' ),
			'add_new_item'			=> __( 'Add New notification', 'bijan' ),
			'new_item'				=> __( 'New notification', 'bijan' ),
			'edit_item'				=> __( 'Edit notification', 'bijan' ),
			'all_items'				=> __( 'All notifications', 'bijan' ),
			'search_items'			=> __( 'Search notifications', 'bijan' ),
			'not_found'				=> __( 'No notification found', 'bijan' ),
			'not_found_in_trash'	=> __( 'No notification found', 'bijan' ),
		];
		$args = [
			'labels'				=> $labels,
			'public'				=> false,
			'show_in_rest'			=> false,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'menu_icon'				=> "dashicons-format-status",
			'capability_type'		=> 'post',
			'has_archive'			=> false,
			'hierarchical'			=> false,
			'supports'				=> ['title', 'editor'],
			'exclude_from_search'	=> true,
		];
		register_post_type( self::$POST_TYPE_NAME, $args );
	}

	private static function _columns() {
		return [
			'users'	=> esc_html__( 'Users', 'bijan' ),
		];
	}

	public static function columns( $columns ) {
		$columns = array_merge( $columns, self::_columns() );

		return $columns;
	}

	public static function columns_value( $column, $post_id ) {
		if( in_array( $column, array_keys( self::_columns() ) ) ) {
			$notification = Notifications::get( $post_id );
			if( $column == 'users' ) {
				if( $notification['all_users'] ) {
					esc_html_e( "All users", 'bijan' );
				} else {
					if( !empty( $notification['users'] ) ) {
						echo implode( " , ", wp_list_pluck( $notification['users'], 'display_name' ) );
					}
				}
			}
		}
	}
}
Notification::register();
add_filter( "manage_notification_posts_columns", [Notification::class, 'columns'] );
add_action( "manage_notification_posts_custom_column", [Notification::class, 'columns_value'], 10, 2 );