<?php
namespace Bijan\Updates;

class V2_0_0_0 {
	public static function update() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}bijan_otp` DROP `created_at`;" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}bijan_wishlist` ADD `created_at` TIMESTAMP NOT NULL AFTER `user_id`;" );
	}
}