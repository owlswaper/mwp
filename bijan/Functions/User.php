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

function bijan_allow_users_upload_files() {
    if ( ! current_user_can( 'upload_files' ) ) {
        $user = wp_get_current_user();
        $user->add_cap( 'upload_files' );
    }
}
add_action( 'init', 'bijan_allow_users_upload_files' );

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

///////// Bypass update user & set password when user registered with SMS
if( !function_exists( "bijan_bypass_user_password" ) ) {
	function bijan_bypass_user_password( $check, $password, $hash, $user_id ) {
		if( class_exists( "Bijan\Utils\Options" ) ) {
			$options = Options::get_options( [
				'auth'  => true
			] );
		} else {
			$options = get_option( 'bijan', [] );
			if( !isset( $options['auth'] ) ) {
				$options['auth'] = true;
			}
		}
		if( !$options['auth'] ) return $check;
		$has_password = get_user_meta( $user_id, 'has_password', true );
		if( ( class_exists( "Bijan\Utils" ) && !Utils::to_bool( $has_password ) ) || ( $has_password === 'false' || $has_password === false || $has_password === "" || $has_password === 0 ) ) {
			return !wp_doing_ajax(); // Don't return true on ajax functions
		}

		return $check;
	}
}
add_filter( 'check_password', 'bijan_bypass_user_password', 10, 4 );

if( !function_exists( "bijan_set_password_for_users_without_password" ) ) {
	function bijan_set_password_for_users_without_password() {
		if( empty( $_POST ) ) return;
		if( ( is_admin() && !wp_doing_ajax() ) || is_admin() ) return;

		// WC
		if( !empty( $_POST['password_1'] ) && !empty( $_POST['password_2'] ) ) {
			if( !Utils::to_bool( get_user_meta( get_current_user_id(), 'has_password', true ) ) ) {
				$_POST['password_current'] = 'bijan_bypass';
			}
		}
	}
}
add_action( 'init', 'bijan_set_password_for_users_without_password', 9 );

if( !function_exists( "bijan_change_has_password" ) ) {
	function bijan_change_has_password( $user_id, $userdata ) {
		if( !empty( $userdata['user_pass'] ) ) {
			update_user_meta( $user_id, 'has_password', true );
		}
	}
}
add_action( 'wp_update_user', 'bijan_change_has_password', 10, 2 );
//////////////// END Bypass