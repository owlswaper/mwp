<?php
namespace Bijan\Utils;

use Bijan\Model\NotificationsUserRel;
use Bijan\Utils;
use MJ\Whitebox\Utils\Posts as WhiteboxPosts;
use MJ\Whitebox\Utils\Users as WhiteboxUsers;

class Notifications extends Utils {
	private static $users = [];
	private static $reads = [];

	public static function default_options() {
		return [
			'all_users'	=> true,
		];
	}

	public static function get_options( $post_id = 0 ) {
		return WhiteboxPosts::get_post_options( self::default_options(), $post_id );
	}

	public static function save_options( array $options, $post_id = 0 ) {
		WhiteboxPosts::save_post_options( $options, self::default_options(), $post_id );
	}
	public static function get( $post = null, bool $get_users = true ) {
		$post = WhiteboxPosts::get_post( $post );
		$options = self::get_options( $post->ID );
		$options['users'] = [];
		if( !$options['all_users'] ) {
			$user_ids = NotificationsUserRel::query()->select( 'user_id' )->where( 'notif_id', $post->ID )->get()->pluck( 'user_id' );
			if( !empty( $user_ids ) ) {
				if( $get_users ) {
					foreach( $user_ids as $user_id ) {
						if( empty( self::$users[$user_id] ) ) {
							self::$users[$user_id] = get_user_by( 'id', $user_id );
						}
						$options['users'][] = self::$users[$user_id];
					}
				} else {
					$options['users'] = $user_ids;
				}
			}
		}
		return $options;
	}

	public static function get_user_reads( $user_id = 0 ) : array {
		$user_id = WhiteboxUsers::get_user_id( $user_id );
		if( !isset( self::$reads[$user_id] ) ) {
			self::$reads[$user_id] = get_user_meta( $user_id, 'bijan-read-notifs', true );
			if( !is_array( self::$reads[$user_id] ) ) self::$reads[$user_id] = [];
		}

		return self::$reads[$user_id];
	}

	public static function get_user_notifications( bool $include_reads = false ) : array {
		static $notifications = null;
		if( $notifications === null && is_user_logged_in() ) {
			$user_id = get_current_user_id();

			$post_in = NotificationsUserRel::query()->select( 'notif_id' )->where( 'user_id', $user_id )->get()->pluck( 'notif_id' );

			global $wpdb;
			$query = "SELECT p.`ID`, p.`post_date`, p.`post_title`, p.`post_content` FROM `{$wpdb->posts}` AS p INNER JOIN `{$wpdb->postmeta}` AS pm ON pm.`post_id`=p.`ID` WHERE ";
			$where = [];
			$prepares = [
				'_all_users',
				'1'
			];
			if( $post_in ) {
				$where[] = "((pm.`meta_key`=%s AND pm.`meta_value`=%s) OR p.`ID` IN (" . parent::db_placeholder( $post_in, '%d' ) . ")) ";
			} else {
				$where[] = "(pm.`meta_key`=%s AND pm.`meta_value`=%s) ";
			}
			$prepares = array_merge( $prepares, $post_in );

			$prepares[] = 'notification';
			$prepares[] = 'publish';
			$where[] = " p.`post_type`=%s AND p.`post_status`=%s";
			$query .= implode( " AND ", $where );
			$query .= " GROUP BY p.`ID` ORDER BY p.`post_date` DESC";
			$query = $wpdb->prepare( $query, $prepares );
		
			$notifications = $wpdb->get_results( $query );
			
			$reads = self::get_user_reads( $user_id );
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			foreach( $notifications as $index => $notification ) {
				$notification = get_post( $notification );
				$notification->post_date = date_i18n( "{$date_format} {$time_format}", strtotime( $notification->post_date ) );
				$notification->read = in_array( $notification->ID, $reads );
				$notifications[$index] = $notification;
			}
		}

		$results = $notifications;
		if( !is_array( $results ) ) {
			$results = [];
		}
		if( !$include_reads && !empty( $results ) ) {
			$results = array_filter( $results, fn( $notification ) => $notification->read === false );
		}

		return $results;
	}

	public static function add_user_read( int $notif_id, $user_id = 0 ) {
		$user_id = WhiteboxUsers::get_user_id( $user_id );
		$reads = self::get_user_reads( $user_id );
		if( !in_array( $notif_id, $reads ) ) {
			$reads[] = $notif_id;
			update_user_meta( $user_id, 'bijan-read-notifs', $reads );
			self::$reads[$user_id] = $reads;
		}
	}

	public static function count_user_unread() {
		return is_user_logged_in() ? count( self::get_user_notifications() ) : 0;
	}
}