<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use MJ\Whitebox\Utils\WC as WhiteboxWC;

// Cart
if( !function_exists( 'bijan_wc_add_to_cart_fragments' ) ) {
	function bijan_wc_add_to_cart_fragments( $fragments ) {
		$options = Options::get_options( [
			'show_bottom_nav'	=> true,
		] );
		ob_start();
		?>
		<div class="header-mini-cart-content">
			<?php woocommerce_mini_cart() ?>
		</div>
		<?php
		$fragments['.header-mini-cart-content'] = ob_get_clean();

		$cart_count = WhiteboxWC::get_cart_count();
		ob_start();
		?>
		<div class="header-cart-texts">
			<div class="header-cart-count-wrap"<?php echo $cart_count === 0 ? ' style="display:none"' : '' ?>>
				<span class="cart-count"><?php echo $cart_count ?></span>
				<span class="header-cart-count-label"><?php echo esc_html_x( 'Product', 'Header cart count label', 'bijan' ) ?></span>
			</div>
			<div class="header-cart-total"<?php echo $cart_count === 0 ? ' style="display:none"' : '' ?>><?php echo WC()->cart->get_cart_subtotal() ?></div>	
			<div class="header-cart-empty"<?php echo $cart_count > 0 ? ' style="display:none"' : '' ?>><?php esc_html_e( 'The cart is empty.', 'bijan' ) ?></div>
		</div>
		<?php
		$fragments['.header-cart-texts'] = ob_get_clean();
		$fragments['.bottom-nav-cart-count'] = '<div class="bottom-nav-cart-count cart-count bijan-count-badge">' . $cart_count . '</div>';

		// Bottom nav
		if( Utils::to_bool( $options['show_bottom_nav'] ) ) {
			ob_start();
			?>
			<div class="bottom-nav-cart-wrap">
				<?php woocommerce_mini_cart() ?>
			</div>
			<?php
			$fragments['.bottom-nav-cart-wrap'] = ob_get_clean();
		}

		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'bijan_wc_add_to_cart_fragments' );

remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 ); // Remove default WooCommerce message
if( !function_exists( "bijan_wc_empty_cart_message" ) ) {
	function bijan_wc_empty_cart_message() {
		echo Options::get_options( [
			'wc_empty_cart_text'	=> __( 'Your cart is empty!', 'bijan' ),
		] )['wc_empty_cart_text'];
	}
}
add_action( 'woocommerce_cart_is_empty', 'bijan_wc_empty_cart_message' );

/////////////////// Mini cart
remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

if( !function_exists( "bijan_wc_mini_cart_checkout_btn" ) ) {
	function bijan_wc_mini_cart_checkout_btn() {
		$options = Options::get_options( [
			'wc_checkout_text'	=> __( 'Proceed to checkout', 'woocommerce' )
		] );

		$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="button rounded fullwidth checkout small wc-forward' . esc_attr( $wp_button_class ) . '">' . esc_html( $options['wc_checkout_text'] ) . '</a>';
	}
}
add_action( 'woocommerce_widget_shopping_cart_buttons', 'bijan_wc_mini_cart_checkout_btn' );