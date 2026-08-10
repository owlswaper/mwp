<?php
namespace Bijan\Updates;

class V2_2_0_0 {
	public static function update() {
		// Set product icons type to '' to get from settings
		global $wpdb;
		$query = "SELECT `post_id`, `meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key`=%s";
		$meta_results = $wpdb->get_results( $wpdb->prepare( $query, [
			'_bijan_icons',
		] ) );
		foreach( $meta_results as $result ) {
			$meta_value = maybe_unserialize( $result->meta_value );
			foreach( $meta_value as $index => $icon_data ) {
				$meta_value[$index]['type'] = '';
			}
			update_post_meta( $result->post_id, '_bijan_icons', $meta_value );
		}
	}
}