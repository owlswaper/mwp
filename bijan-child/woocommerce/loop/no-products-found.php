<?php
/** Empty AJAX results keep the same toolbar and replaceable archive container. */
defined( 'ABSPATH' ) || exit;
$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : '';
wc_get_template( 'loop/orderby.php', [ 'orderby' => $orderby ] );
$options = \Bijan\Utils\Options::get_options( [ 'wc_empty_shop_text' => __( 'No product was found.', 'bijan' ) ] );
?>
<div class="woocommerce-no-products-found woocommerce-page-content empty-page" role="status">
	<i class="empty-page-icon empty-shop-icon bijan-icon-shopping-cart" aria-hidden="true"></i>
	<p class="empty-page-text empty-shop-text"><?php echo esc_html( $options['wc_empty_shop_text'] ); ?></p>
</div>
