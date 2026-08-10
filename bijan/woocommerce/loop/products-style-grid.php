<?php

use Bijan\Utils\Options;
use Bijan\Utils\Product;

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
	<?php
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
		
		global $product_index;
		echo '<span class="product-index">' . ($product_index+1) . '</span>';
		$product_index++;

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

	echo '<span class="price">' . wc_price( $product->get_price() ) . '</span>';

	woocommerce_template_loop_product_link_close();
	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>