<?php

use Bijan\Utils\Options;
use Bijan\Utils\Product;
use Bijan\Utils\UI;

global $product;

if ( empty( $product ) || ! $product instanceof \WC_Product ) {
	return;
}

$options = Options::get_options( [
	'wc-second-image-hover-show'	=> false,
	'wishlist'						=> true,
] );

$props = Product::get_loop_props();
?>
<li <?php wc_product_class( '', $product ); ?>>
	<div class="product-inner">
		<?php
		UI::curve( 'product', 'product-hover' );
		bijan_wc_template_loop_product_link_open();
		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item' );
		?>
		<div class="bijan-product-image-wrap">
			<?php
			/**
			 * Hook: woocommerce_before_shop_loop_item_title.
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );

			if( $options['wc-second-image-hover-show'] ) {
				$ids = $product->get_gallery_image_ids();
				if( !empty( $ids ) && !empty( $ids[0] ) ) {
					$image_size = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );
					echo wp_get_attachment_image( $ids[0], $image_size );
				}
			}
			?>
		</div>
		<?php

		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
		do_action( 'woocommerce_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_after_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item_title' );

		woocommerce_template_loop_product_link_close();
		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item' );
		?>
		<div class="product-bottom">
			<?php
			if( !$props['special_products'] ) {
				if( $options['wishlist'] ) {
					UI::product_wishlist( $product->get_id() );
				}
				woocommerce_template_loop_price();
				woocommerce_template_loop_add_to_cart();
			} else {
				$instant_discount_details = Product::get_instant_discount( $product->get_id() );
				get_template_part( "templates/components/product_progress", null, [
					'total'		=> $instant_discount_details['total'],
					'remaining'	=> $instant_discount_details['remaining'],
					'title'		=> sprintf( __( "%s remaining", 'bijan' ), number_format( $instant_discount_details['remaining'] ) ),
				] );
			}
			?>
		</div>
	</div>
</li>