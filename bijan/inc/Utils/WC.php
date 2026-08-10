<?php
namespace Bijan\Utils;

use Bijan\Utils;
use MJ\Whitebox\Utils\WC as UtilsWC;

class WC extends UtilsWC {
	public static function default_attribute_settings() {
		return [
			'display_type'	=> 'select',
		];
	}
	
	public static function get_attribute_settings( int $id ) {
		$settings = get_option( "bijan_wc_attr_{$id}", self::default_attribute_settings() );
		if( !is_array( $settings ) ) $settings = [];
		return Utils::check_default( $settings, self::default_attribute_settings() );
	}

	public static function update_attribute_settings( int $id, array $settings ) {
		$settings = Utils::check_default( $settings, self::default_attribute_settings() );
		update_option( "bijan_wc_attr_{$id}", $settings, false );
	}

	public static function get_term_color( int $id ) {
		$color = get_term_meta( $id, '_bijan_color', true );
		if( !is_array( $color ) ) {
			$color = [
				'color_1'	=> $color,
			];
		}
		$color = Utils::check_default( $color, [
			'color_1'	=> '#ffffff',
			'color_2'	=> '',
			'direction'	=> 'vertical',
		] );
		$color['direction'] = Utils::ensure_values_in_array( $color['direction'], ['vertical', 'horizontal'], 'vertical' );
		return $color;
	}

	public static function update_term_color( int $id, $color ) {
		$default = [
			'color_1'	=> '#ffffff',
			'color_2'	=> '#000000',
			'direction'	=> 'vertical',
		];
		update_term_meta( $id, '_bijan_color', Utils::check_default( $color, $default ) );
	}

	public static function get_term_img( int $id ) {
		return absint( get_term_meta( $id, '_bijan_img', true ) );
	}

	public static function update_term_img( int $id, $img ) {
		update_term_meta( $id, '_bijan_img', absint( $img ) );
	}

	public static function get_term_icon( int $id ) {
		return get_term_meta( $id, '_bijan_icon', true );
	}

	public static function update_term_icon( int $id, $icon ) {
		update_term_meta( $id, '_bijan_icon', sanitize_text_field( $icon ) );
	}

	public static function get_term_gradient( int $id ) {
		$gradient = get_term_meta( $id, '_bijan_gradient', true );
		$default = [
			'color_1'	=> '#ffffff',
			'color_2'	=> '#000000',
			'angle'		=> 0,
		];
		if( !is_array( $gradient ) || empty( $gradient ) ) {
			return $default;
		}

		return Utils::check_default( $gradient, $default );
	}

	public static function update_term_gradient( int $id, array $gradient ) {
		$default = [
			'color_1'	=> '#ffffff',
			'color_2'	=> '#000000',
			'angle'		=> 0,
		];
		update_term_meta( $id, '_bijan_gradient', Utils::check_default( $gradient, $default ) );
	}

	public static function my_account_custom_links() {
		$links = [
			'wishlist'		=> _x( "Wishlist", 'My Account Link', 'bijan' ),
			'coupons'		=> _x( "Coupons", 'My Account Link', 'bijan' ),
			'notifications'	=> _x( "Notifications", 'My Account Link', 'bijan' ),
			// 'support'		=> _x( "Support", 'My Account Link', 'bijan' ),
		];
		$options = Options::get_options( [
			'wishlist'		=> true,
			'notifications'	=> true,
		] );
		$unset = [];
		if( !Utils::to_bool( $options['wishlist'] ) ) {
			$unset[] = 'wishlist';
		}
		if( !Utils::to_bool( $options['notifications'] ) ) {
			$unset[] = 'notifications';
		}
		if( !empty( $unset ) ) {
			$links = Utils::unset( $links, $unset );
		}
		return $links;
	}

	public static function my_account_menu_link_icons() {
		$icons = [
			'dashboard'			=> 'bijan-icon-home',
			'orders'			=> 'bijan-icon-box',
			'downloads'			=> 'bijan-icon-download-2',
			'edit-address'		=> 'bijan-icon-location',
			'edit-account'		=> 'bijan-icon-author',
			'wishlist'			=> 'bijan-icon-heart',
			'coupons'			=> 'bijan-icon-ticket',
			'notifications'		=> 'bijan-icon-notification',
			'customer-logout'	=> 'bijan-icon-logout',
		];

		$options = Options::get_options( [
			'wishlist'		=> true,
			'notifications'	=> true,
		] );
		$unset = [];
		if( !Utils::to_bool( $options['wishlist'] ) ) {
			$unset[] = 'wishlist';
		}
		if( !Utils::to_bool( $options['notifications'] ) ) {
			$unset[] = 'notifications';
		}
		if( !empty( $unset ) ) {
			$icons = Utils::unset( $icons, $unset );
		}

		return apply_filters( "bijan/wc/my-account/links/icons", $icons );
	}

	public static function get_active_coupons_for_user() {
		$user = wp_get_current_user();
	
		if( !$user->ID ) {
			return []; // Return empty if no user is logged in
		}
	
		// Get the current date
		$current_date = date( 'Y-m-d H:i:s' );
	
		// Query WooCommerce coupons
		$args = [
			'post_type'			=> 'shop_coupon',
			'posts_per_page'	=> -1,
			'post_status'		=> 'publish',
			'meta_query'		=> [
				'relation' => 'OR',
				[
					'key'     => 'expiry_date',
					'value'   => $current_date,
					'compare' => '>=', // Coupon has not expired
					'type'    => 'DATETIME',
				],
				[
					'key'     => 'expiry_date',
					'compare' => 'NOT EXISTS', // Coupons with no expiry date
				],
			],
			'fields'			=> 'ids', // Only retrieve IDs for efficiency
			'no_found_rows'		=> true,
		];
	
		$coupons = get_posts( $args );
		$active_coupons = [];
	
		// Filter coupons based on usage and user restrictions
		foreach( $coupons as $coupon_id ) {
			$coupon = new \WC_Coupon( $coupon_id );
	
			// Check if the coupon has usage restrictions
			$allowed_users = $coupon->get_email_restrictions();
	
			// If there are specific allowed users, check if the current user is allowed
			if( !empty( $allowed_users ) && !in_array( $user->user_email, $allowed_users, true ) ) {
				continue; // Skip coupons not allowed for this user
			}
	
			// Check if usage limit has been reached
			if( $coupon->get_usage_limit() && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
				continue; // Skip coupons that have reached their usage limit
			}
	
			$active_coupons[] = $coupon;
		}
	
		return $active_coupons;
	}

	public static function apply_custom_toman( $status = '' ) {
		static $result = true;
		if( $status !== '' ) {
			$result = $status;
		}
		return $result;
	}

	public static function attr_display_types() {
		static $types = null;
		if( $types === null ) {
			$types = [
				'select'	=> __( 'Dropdown list', 'bijan' ),
				'color'		=> __( 'Color', 'bijan' ),
				'image'		=> __( 'Image', 'bijan' ),
				'radio'		=> __( 'Select options', 'bijan' ),
				'icon'		=> __( 'Icon', 'bijan' ),
				'gradient'	=> __( 'Gradient', 'bijan' ),
			];
		}
		return $types;
	}

	public static function get_gallery_ids( $product ) {
		$images = parent::get_gallery_ids( $product );
		$product_options = Product::get_options( $product->get_id() );
		if( $product_options['video_id'] ) {
			$images[] = $product_options['video_id'];
			Utils::reposition_array_element( $images, count( $images )-1, 1 );
		}

		return $images;
	}
}