<?php
namespace Bijan\AJAX;

use Bijan\AJAX;
use Bijan\Utils;
use Bijan\Utils\Notifications as UtilsNotifications;

class Notifications extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	public function set_read() {
		$this->set_request_data();

		$notif_id = Utils::convert_chars( $this->data['id'], true, 'absint' );
		$this->check_nonce( "bijan_notification_read_{$notif_id}" );
		$notification_ids = wp_list_pluck( UtilsNotifications::get_user_notifications( true ), 'ID' );
		if ( ! in_array( $notif_id, $notification_ids, true ) ) {
			$this->result( 'error', [
				'code' => 'forbidden',
				'message' => esc_html__( 'You do not have permission to access this notification.', 'bijan' ),
			] );
		}
		UtilsNotifications::add_user_read( $notif_id );

		$this->result( 'success', [
			'readText'		=> esc_html__( "Read", 'bijan' ),
			'unreadCount'	=> UtilsNotifications::count_user_unread(),
		] );
	}
}
