<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\User;
use MJ\Whitebox\Utils\Users as WhiteboxUsers;
use MJ\Whitebox\Utils\Validators;

if( !function_exists( "bijan_user_update_display_names" ) ) {
	function bijan_user_update_display_names( $settings ) {
		if( !empty( $settings['security']['hide_mobile'] ) && $settings['security']['hide_mobile'] != 'disabled' ) {
			global $wpdb;
			$query = "SELECT `ID`, `display_name` FROM `{$wpdb->users}` WHERE `user_login`=`display_name`";
			$users = $wpdb->get_results( $query );
			foreach( $users as $user ) {
				$wpdb->query( 'START TRANSACTION' );
				if( Validators::phone( $user->display_name ) ) {
					$update = User::change_display_name( $user, $settings );
					if( !$update ) {
						$wpdb->query( 'ROLLBACK' );
						break;
					}
				}
				$wpdb->query( 'COMMIT' );
			}
		}
	}
}
add_action( 'bijan/sms/settings/updated', 'bijan_user_update_display_names' );

if( !function_exists( "bijan_user_update_user" ) ) {
	function bijan_user_update_user( $user_id ) {
		if( !is_user_logged_in() ) return;
		remove_action( 'wp_update_user', 'bijan_user_update_user' );
		$user = get_user_by( 'id', $user_id );
		if( Validators::phone( $user->display_name ) ) {
			User::change_display_name( get_user_by( 'id', $user_id ) );
		}
	}
}
add_action( 'wp_update_user', 'bijan_user_update_user' );
add_action( 'user_register', 'bijan_user_update_user' );

// Custom avatar
if( !function_exists( "bijan_change_avatar" ) ) {
	function bijan_change_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
		if( is_a( $id_or_email, 'WP_Comment' ) ) {
			$id_or_email = !empty( $id_or_email->user_id ) ? $id_or_email->user_id : $id_or_email->comment_author_email;
		}
		$user = WhiteboxUsers::get_user_object( $id_or_email );
		if( !empty( $user ) ) {
			$avatar_id = WhiteboxUsers::get_avatar_id( $user->ID );
			if( $avatar_id ) {
				$args['alt'] = $alt;
				$avatar = wp_get_attachment_image( $avatar_id, [$size, $size], false, $args );
			} else {
				$avatar = '<img src="' . BIJAN_URI . "assets/img/user.svg" . '" alt="">';
			}
		}
		return $avatar;
	}
}
add_filter( 'get_avatar', 'bijan_change_avatar', 1, 6 );

if( !function_exists( "bijan_change_avatar_url" ) ) {
	function bijan_change_avatar_url( $url, $id_or_email, $args ) {
		if( is_a( $id_or_email, 'WP_Comment' ) ) {
			$id_or_email = !empty( $id_or_email->user_id ) ? $id_or_email->user_id : $id_or_email->comment_author_email;
		}
		$user = WhiteboxUsers::get_user_object( $id_or_email );
		if( !empty( $user ) ) {
			$avatar_id = WhiteboxUsers::get_avatar_id( $user->ID );
			if( $avatar_id ) {
				$url = wp_get_attachment_image_url( $avatar_id, [$args['size'], $args['size']] );
			} else {
				$url = BIJAN_URI . "assets/img/user.svg";
			}
		}
		return $url;
	}
}
add_filter( 'get_avatar_url', 'bijan_change_avatar_url', 10, 3 );

/**
 * Remove the legacy per-user upload capability that older versions granted to
 * every logged-in user. Roles remain untouched; only an explicitly assigned
 * capability is removed.
 */
function bijan_remove_legacy_upload_capability() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) || get_option( 'bijan_upload_capability_migrated' ) ) {
		return;
	}

	$offset = absint( get_option( 'bijan_upload_capability_migration_offset', 0 ) );
	$user_ids = get_users( [
		'fields' => 'ID',
		'number' => 200,
		'offset' => $offset,
	] );
	foreach ( $user_ids as $user_id ) {
		$user = new \WP_User( $user_id );
		if ( array_key_exists( 'upload_files', (array) $user->caps ) ) {
			$user->remove_cap( 'upload_files' );
		}
	}

	if ( count( $user_ids ) === 200 ) {
		update_option( 'bijan_upload_capability_migration_offset', $offset + 200, false );
		return;
	}

	delete_option( 'bijan_upload_capability_migration_offset' );
	update_option( 'bijan_upload_capability_migrated', BIJAN_VERSION, false );
}
add_action( 'admin_init', 'bijan_remove_legacy_upload_capability' );

if( !function_exists( "bijan_filter_attachments" ) ) {
	function bijan_filter_attachments( $query ) {
		if( !is_admin() || !wp_doing_ajax() ) {
			return;
		}

		if ( isset( $_POST['action'] ) && $_POST['action'] === 'query-attachments' ) {
			if ( is_user_logged_in() && ! current_user_can( 'administrator' ) ) {
				$current_user_id = get_current_user_id();
				$query->set( 'author', $current_user_id );
				$query->set( 'post_type', 'attachment' );
			}
		}
	}
}
add_action( 'pre_get_posts', 'bijan_filter_attachments' );
