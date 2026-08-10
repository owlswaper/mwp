<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\Product;
use Bijan\Utils\UI;
use MJ\Whitebox\Utils\Date;

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
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );

	bijan_wc_template_loop_product_link_open();
	?>
	<div class="product-thumbnail-wrap">
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
		if( $product->is_on_sale() ) {
			if( is_numeric( $product->get_sale_price() ) && is_numeric( $product->get_regular_price() ) ) {
				?>
				<div class="product-discount"><?php echo Product::calc_discount_percentage( $product->get_regular_price(), $product->get_sale_price() ); ?></div>
				<?php
			}
		}
		?>
	</div>
	<?php
	woocommerce_template_loop_product_link_close();
	?>

	<div class="product-content">
		<?php
		bijan_wc_template_loop_product_link_open();
		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
		do_action( 'woocommerce_shop_loop_item_title' );
		woocommerce_template_loop_product_link_close();
		?>
		<div class="product-data">
			<?php
			bijan_wc_template_loop_product_link_open();
			/**
			 * Hook: woocommerce_after_shop_loop_item_title.
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );
			woocommerce_template_loop_price();
			woocommerce_template_loop_product_link_close();
			?>
			<div class="product-actions">
				<?php
				if( $options['wishlist'] ) {
					UI::product_wishlist( $product->get_id() );
				}
				woocommerce_template_loop_add_to_cart();
				?>
			</div>
		</div>
	</div>
	<?php
	if( $product->is_on_sale() ) {
		$sale_price_end = $product->get_date_on_sale_to();
		if( $sale_price_end ) {
			$now = (new \DateTime())->setTimestamp( current_time( 'U' ) );
			$end = (new \DateTime())->setTimestamp( $sale_price_end );
			$diff = $now->diff( $end );
			if( !$diff->invert ) {
				$days		= $diff->format( '%a' );
				$hours		= Utils::add_zero( $diff->format( '%h' ) );
				$minutes	= Utils::add_zero( $diff->format( '%i' ) );
				$seconds	= Utils::add_zero( $diff->format( '%s' ) );
			} else {
				$days		= "0";
				$hours		= "00";
				$minutes	= "00";
				$seconds	= "00";
			}

			$wrap_html_attrs = [
				'classes'	=> ['product-timer'],
				'data-time'	=> $sale_price_end-Date::timezone_offset(),
			];
			?>
			<div <?php echo Utils::get_html_attributes( $wrap_html_attrs ) ?>>
				<div class="product-timer-item product-timer-days">
					<div class="product-timer-item-value"><?php echo esc_html( $days ) ?></div>
					<div class="product-timer-item-label"><?php echo $days === '01' || $days === '00' ? esc_html__( "Day", 'bijan' ) : esc_html__( "Days", 'bijan' ) ?></div>
				</div>

				<div class="product-timer-item product-timer-hours">
					<div class="product-timer-item-value"><?php echo esc_html( $hours ) ?></div>
					<div class="product-timer-item-label"><?php echo $hours === '01' || $hours === '00' ? esc_html__( "Hour", 'bijan' ) : esc_html__( "Hours", 'bijan' ) ?></div>
				</div>

				<div class="product-timer-item product-timer-minutes">
					<div class="product-timer-item-value"><?php echo esc_html( $minutes ) ?></div>
					<div class="product-timer-item-label"><?php echo $minutes === '01' || $minutes === '00' ? esc_html__( "Minute", 'bijan' ) : esc_html__( "Minutes", 'bijan' ) ?></div>
				</div>

				<div class="product-timer-item product-timer-seconds">
					<div class="product-timer-item-value"><?php echo esc_html( $seconds ) ?></div>
					<div class="product-timer-item-label"><?php echo $seconds === '01' || $seconds === '00' ? esc_html__( "Second", 'bijan' ) : esc_html__( "Seconds", 'bijan' ) ?></div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>