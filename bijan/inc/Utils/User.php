<?php
namespace Bijan\Utils;

use Bijan\Utils;
use MJ\Whitebox\Utils\Users as WhiteboxUsers;

class User extends WhiteboxUsers {
	private static $user_wishlist_list = [];

	public static function get_wishlist_products( $user_id = 0, $retrieve = 'ids', array $db_args = [], array $get_products_args = [] ) {
		$user_id = parent::get_user_id( $user_id );

		// Local cache
		$cache_key = "{$user_id}-{$retrieve}-" . crc32( json_encode( $db_args ) ) . "-" . crc32( json_encode( $get_products_args ) );
		if( isset( self::$user_wishlist_list[$cache_key] ) ) {
			return self::$user_wishlist_list[$cache_key];
		}

		$products = Wishlist::get_user_wishlist( $user_id, $db_args );
		if( !$products->isEmpty() ) {
			$products = $products->pluck( 'product_id' );
			if( $retrieve != 'ids' ) {
				$get_products_args['include'] = $products;
				$get_products = wc_get_products( $get_products_args );
				$products = clone $get_products;
				$products->products = [];
				foreach( $get_products_args['include'] as $product_id ) {
					foreach( $get_products->products as $product ) {
						if( $product->get_id() == $product_id ) {
							$products->products[] = $product;
							break;
						}
					}
				}
			}
		} else {
			$products = [];
		}
		self::$user_wishlist_list[$cache_key] = $products;

		return $products;
	}

	public static function get_account_menu_items() {
		$home_url = home_url();
		
		$options = Options::get_options( [
			'show-login-item'	=> true,
			'login-text'		=> esc_html__( "Login", 'bijan' ),
			'login-icon'		=> "bijan-icon-login",
			'show-signup-item'	=> true,
			'signup-text'		=> esc_html__( "Signup", 'bijan' ),
			'signup-icon'		=> "bijan-icon-author",
		] );
		$account_btn_items = [];
		if( is_user_logged_in() ) {
			$my_account_link = Utils::is_wc_active() ? home_url( 'my-account' ) : $home_url;
			if( has_nav_menu( 'account-menu' ) ) {
				foreach( Utils::get_nav_menu_items_by_location( 'account-menu' ) as $menu_item ) {
					$account_btn_items[] = [
						'label'	=> $menu_item->post_title,
						'link'	=> $menu_item->url,
						'icon'	=> UI::get_menu_icon( $menu_item->ID, 'icon' ),
					];
				}
			} else {
				$account_btn_items = [
					[
						'label'	=> __( 'Dashboard', 'bijan' ),
						'link'	=> $my_account_link,
						'icon'	=> 'bijan-icon-home',
					],
					[
						'label'	=> _x( 'Wishlist', 'Plural', 'bijan' ),
						'link'	=> "{$my_account_link}/wishlist",
						'icon'	=> 'bijan-icon-heart',
					],
					[
						'label'	=> __( 'Coupons', 'bijan' ),
						'link'	=> "{$my_account_link}/coupons",
						'icon'	=> 'bijan-icon-ticket',
					],
					[
						'label'	=> __( 'Logout', 'bijan' ),
						'link'	=> Utils::is_wc_active() ? wc_logout_url( $home_url ) : wp_logout_url( $home_url ),
						'icon'	=> 'bijan-icon-logout',
					],
				];
			}
		} else {
			$account_btn_items = [
				'login'	=> [
					'label'	=> $options['login-text'],
					'link'	=> '#',
					'icon'	=> $options['login-icon'],
				],
				'signup'	=> [
					'label'	=> $options['signup-text'],
					'link'	=> '#',
					'icon'	=> $options['signup-icon'],
				],
			];

			if( !parent::to_bool( $options['show-login-item'] ) ) {
				unset( $account_btn_items['login'] );
			}
			if( !parent::to_bool( $options['show-signup-item'] ) ) {
				unset( $account_btn_items['signup'] );
			}
		}
		return $account_btn_items;
	}

	public static function change_display_name( $user_object, $sms_settings = [] ) {
		$options = Options::get_options( [
			'sms'	=> true,
		] );
		if( !$options['sms'] ) return false;
		if( empty( $sms_settings ) ) {
			$sms_settings = SMS::get_settings();
		}
		$display_name = '';
		if( $sms_settings['security']['hide_mobile'] == 'mid_star' ) {
			$display_name = substr( $user_object->display_name, 0, 4 ) . "***" . substr( $user_object->display_name, 7, 4 );
		} else if( $sms_settings['security']['hide_mobile'] == 'end_star' ) {
			$display_name = substr( $user_object->display_name, 0, 7 ) . "****";
		} else if( $sms_settings['security']['hide_mobile'] == 'sitename' ) {
			$display_name = get_bloginfo( 'blogname' );
		} else if( $sms_settings['security']['hide_mobile'] == 'custom' && !empty( $sms_settings['security']['hide_mobile_custom'] ) ) {
			$display_name = parent::apply_general_variables( $sms_settings['security']['hide_mobile_custom'] );
		}
		$update = wp_update_user( [
			'ID'			=> $user_object->ID,
			'display_name'	=> $display_name,
		] );
		if( !$update || is_wp_error( $update ) ) {
			return false;
		}
		return true;
	}
}