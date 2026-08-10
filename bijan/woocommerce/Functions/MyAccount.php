<?php

use Bijan\Utils;
use Bijan\Utils\User;
use Bijan\Utils\Notifications;
use Bijan\Utils\Options;
use Bijan\Utils\UI;
use Bijan\Utils\WC;
use MJ\Whitebox\Utils\WC as WhiteboxWC;

if( !function_exists( "bijan_wc_my_account_redirect_guest" ) ) {
	function bijan_wc_my_account_redirect_guest() {
		$options = Options::get_options( [
			'auth-modal'	=> true,
			'auth-email'	=> true,
			'auth_sms'		=> true,
		] );
		
		if( !Utils::to_bool( $options['auth-modal'] ) ) return;
		if( !Utils::to_bool( $options['auth-email'] ) && !Utils::to_bool( $options['auth_sms'] ) ) return;

		if( is_account_page() ) {
			wp_redirect( home_url( "?login" ) );
			die;
		}
	}
}
if( !is_user_logged_in() ) {
	add_action( 'template_redirect', 'bijan_wc_my_account_redirect_guest' );
}

if( !function_exists( "bijan_wc_my_account_avatar" ) ) {
	function bijan_wc_my_account_avatar() {
		$user = wp_get_current_user();
		$user_custom_avatar = User::get_avatar_id( $user );
		?>
		<div class="woocommerce-form-row woocommerce-form-row--wide bijan-edit-avatar-wrap-row">
			<input type="hidden" name="bijan_account_avatar_id" id="account_avatar_id" value="<?php echo esc_attr( $user_custom_avatar ) ?>">
			<div class="bijan-edit-avatar-wrap" id="bijan-edit-avatar">
				<?php echo get_avatar( $user->ID, 96 ) ?>
				<i class="bijan-icon-edit bijan-edit-avatar-icon"></i>
				<?php if( !empty( $user_custom_avatar ) ) { ?>
					<i class="bijan-icon-trash bijan-delete-avatar-icon"></i>
				<?php } ?>
			</div>
		</div>
		<?php
	}
}
add_action( 'woocommerce_edit_account_form_start', 'bijan_wc_my_account_avatar' );

if( !function_exists( "bijan_wc_save_account_details" ) ) {
	function bijan_wc_save_account_details( $user_id ) {
		if( isset( $_POST['bijan_account_avatar_id'] ) ) {
			User::save_avatar_id( Utils::convert_chars( $_POST['bijan_account_avatar_id'], true, 'absint' ), $user_id );
		}
	}
}
add_action( 'woocommerce_save_account_details', 'bijan_wc_save_account_details' );

if( !function_exists( "bijan_wc_my_account_items" ) ) {
	function bijan_wc_my_account_items( $menu_links ) {
		$menu_links = array_merge( $menu_links, WC::my_account_custom_links() );

		Utils::reposition_array_element( $menu_links, 'customer-logout', 99 );

		return $menu_links;
	}
}
add_filter( 'woocommerce_account_menu_items', 'bijan_wc_my_account_items' );

if( !function_exists( "bijan_wc_my_account_links_endpoint" ) ) {
	function bijan_wc_my_account_links_endpoint() {
		foreach( array_keys( WC::my_account_custom_links() ) as $link ) {
			add_rewrite_endpoint( $link, EP_PAGES );
		}
	}
}
add_action( 'init', 'bijan_wc_my_account_links_endpoint' );

if( !function_exists( "bijan_wc_my_account_head" ) ) {
	function bijan_wc_my_account_head() {
		$options = Options::get_options( [
			'my-account-welcome'	=> esc_html__( "Welcome to Bijan store.", 'bijan' ),
			'notifications'			=> true,
		] );

		$user = wp_get_current_user();
		?>
		<div id="myaccount-head">
			<?php UI::curve( 'product' ) ?>
			<div id="myaccount-head-user">
				<?php echo get_avatar( $user->ID, 92 ) ?>
				<div id="myaccount-head-user-name"><?php echo esc_html( $user->display_name ) ?></div>
				<?php if( $options['notifications'] ) { ?>
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'notifications' ) ); ?>" id="myaccount-head-notifs">
						<div class="notifications-count bijan-count-badge"><?php echo Notifications::count_user_unread() ?></div>
						<i class="bijan-icon-notification"></i>
					</a>
				<?php } ?>
			</div>

			<div id="myaccount-head-welcome"><?php echo wp_kses_post( $options['my-account-welcome'] ) ?></div>
		</div>
		<?php
	}
}
add_action( 'woocommerce_account_content', 'bijan_wc_my_account_head', 6 );

if( !function_exists( "bijan_wc_my_account_start" ) ) {
	function bijan_wc_my_account_start() {
		$endpoint = WhiteboxWC::get_account_endpoint();
		if( $endpoint != 'dashboard' ) {
			echo '<div class="bijan-section">';
		}
	}
}
add_action( 'woocommerce_account_content', 'bijan_wc_my_account_start', 7 );

if( !function_exists( "bijan_wc_my_account_end" ) ) {
	function bijan_wc_my_account_end() {
		$endpoint = WhiteboxWC::get_account_endpoint();
		if( $endpoint != 'dashboard' ) {
			echo '</div>';
		}
	}
}
add_action( 'woocommerce_account_content', 'bijan_wc_my_account_end', 999 );

if( !function_exists( "bijan_wc_my_account_wishlist_content" ) ) {
	function bijan_wc_my_account_wishlist_content() {
		include( BIJAN_DIR . "woocommerce/myaccount/wishlist.php" );
	}
}

if( !function_exists( "bijan_wc_my_account_wishlist_content" ) ) {
	function bijan_wc_my_account_wishlist_content() {
		include( BIJAN_DIR . "woocommerce/myaccount/wishlist.php" );
	}
}

if( !function_exists( "bijan_wc_my_account_coupons_content" ) ) {
	function bijan_wc_my_account_coupons_content() {
		include( BIJAN_DIR . "woocommerce/myaccount/coupons.php" );
	}
}

if( !function_exists( "bijan_wc_my_account_notifications_content" ) ) {
	function bijan_wc_my_account_notifications_content() {
		include( BIJAN_DIR . "woocommerce/myaccount/notifications.php" );
	}
}

foreach( array_keys( WC::my_account_custom_links() ) as $my_account_link ) {
	if( function_exists( "bijan_wc_my_account_{$my_account_link}_content" ) ) {
		add_action( "woocommerce_account_{$my_account_link}_endpoint", "bijan_wc_my_account_{$my_account_link}_content" );
	}
}

if( !function_exists( "bijan_wc_my_account_my_orders_actions" ) ) {
	function bijan_wc_my_account_my_orders_actions( $actions ) {
		$actions = Utils::unset( $actions, ['view'] );
		return $actions;
	}
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'bijan_wc_my_account_my_orders_actions' );