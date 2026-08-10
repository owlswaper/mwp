<?php

use Bijan\Utils;
use Bijan\Utils\WC;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'empty-mini-cart-text'	=> esc_html__( 'The cart is empty.', 'bijan' ),
	'cart-icon'				=> 'bijan-icon-shopping-cart',
	'show-mini-cart'		=> true,
] );

$cart_count = WC::get_cart_count();
?>
<div class="header-cart-wrap header-action header-action-cart">
	<a href="<?php echo wc_get_cart_url() ?>" class="header-action-btn header-cart-btn">
		<div class="header-cart-texts">
			<div class="header-cart-count-wrap"<?php echo $cart_count === 0 ? ' style="display:none"' : '' ?>>
				<span class="cart-count"><?php echo $cart_count ?></span>
				<span class="header-cart-count-label"><?php echo esc_html_x( 'Product', 'Header cart count label', 'bijan' ) ?></span>
			</div>
			<div class="header-cart-total"<?php echo $cart_count === 0 ? ' style="display:none"' : '' ?>><?php echo $cart_count === 0 ? '' : WC()->cart->get_cart_subtotal() ?></div>	
			<div class="header-cart-empty"<?php echo $cart_count > 0 ? ' style="display:none"' : '' ?>><?php echo $args['empty-mini-cart-text'] ?></div>
		</div>
		<?php
		if( $args['cart-icon'] ) {
			echo $args['cart-icon'];
		}
		?>
	</a>
	
	<?php if( Utils::to_bool( $args['show-mini-cart'] ) ) { ?>
		<div class="header-mini-cart-wrap bijan-popover">
			<div class="header-mini-cart-content">
				<?php woocommerce_mini_cart() ?>
			</div>
		</div>
	<?php } ?>
</div>