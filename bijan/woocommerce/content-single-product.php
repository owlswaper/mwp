<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\Product;
use Bijan\Utils\WC;

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

$product_options = Product::get_options( $product->get_id() );
$options = Options::get_options( [
	'wc-show-stock-status-single'	=> true,
	'wc-single-short-description'	=> true,
	'wc-single-short-pos'			=> 'after_add_to_cart',
] );

$has_gallery = !empty( WC::get_gallery_ids( $product ) );
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<div id="product-head">
		<div class="product-section<?php echo $has_gallery ? '' : ' product-without-gallery' ?>" id="product-head-main">
			<div class="product_title entry-title product-mobile-title hide-desktop-1024"><?php the_title() ?></div>
			<?php if( $has_gallery ) { ?>
				<div class="product-head-gallery-wrap">
					<?php
					/**
					 * Hook: woocommerce_before_single_product_summary.
					 *
					 * @hooked woocommerce_show_product_sale_flash - 10
					 * @hooked woocommerce_show_product_images - 20
					 */
					do_action( 'woocommerce_before_single_product_summary' );
					?>				
				</div>
			<?php } ?>

			<div class="product-head-texts">
				<?php
				/**
				 * Hook: woocommerce_single_product_summary.
				 *
				 * @hooked woocommerce_template_single_title - 5
				 * @hooked woocommerce_template_single_rating - 10
				 * @hooked woocommerce_template_single_price - 10
				 * @hooked woocommerce_template_single_excerpt - 20
				 * @hooked woocommerce_template_single_add_to_cart - 30
				 * @hooked woocommerce_template_single_meta - 40
				 * @hooked woocommerce_template_single_sharing - 50
				 * @hooked WC_Structured_Data::generate_product_data() - 60
				 */
				do_action( 'woocommerce_single_product_summary' );
				?>
			</div>

			<?php
			if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'under_gallery' ) {
				woocommerce_template_single_excerpt();
			}
			?>
		</div>

		<div class="product-section" id="product-head-secondary">
			<div id="product-head-secondary-top">
				<?php woocommerce_template_single_meta() ?>
				<?php woocommerce_template_single_add_to_cart() ?>
				<?php
				if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'after_add_to_cart' ) {
					woocommerce_template_single_excerpt();
				}
				?>
			</div>

			<?php if( $product_options['notes'] || Utils::to_bool( $options['wc-show-stock-status-single'] ) ) { ?>
				<div id="product-head-secondary-bottom">
					<?php
					if( $product_options['notes'] ) {
						echo wpautop( $product_options['notes'] );
					}

					if( Utils::to_bool( $options['wc-show-stock-status-single'] ) ) {
						?>
						<span id="product-head-stock-status">
							<?php echo wc_get_stock_html( $product ); // WPCS: XSS ok. ?>
						</span>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
