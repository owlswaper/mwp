<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

use Bijan\Utils\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) :
	/**
	 * Ensure all images of related products are lazy loaded by increasing the
	 * current media count to WordPress's lazy loading threshold if needed.
	 * Because wp_increase_content_media_count() is a private function, we
	 * check for its existence before use.
	 */
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}
	?>

	<section class="related">

		<?php
		$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );

		$shop_page = get_permalink( wc_get_page_id( 'shop' ) );
		if ( $heading ) {
			$options = Options::get_options( [
				'wc-single-end-products-title_icon'	=> 'bijan-icon-flash',
				'wc-single-end-products-title_tag'	=> 'h3',
			] );
			echo '<div class="related-product-title">';
			get_template_part( "templates/components/section_title", null, [
				'icon'	=> $options['wc-single-end-products-title_icon'],
				'tag'	=> $options['wc-single-end-products-title_tag'],
				'title'	=> $heading,
				'link'	=> $shop_page,
			] );
			get_template_part( "templates/components/button", null, [
				'type'	=> 'action',
				'small'	=> true,
				'text'	=> esc_html__( "View all", 'bijan' ),
				'link'	=> $shop_page,
			] );
			echo '</div>';
		}
		?>
		
		<?php
		wc_set_loop_prop( 'bijan_loop_props', [
			'style'					=> 'products-style-2',
			'desktop_cols'			=> 5,
			'tablet_slider'			=> true,
			'tablet_slides_type'	=> 'auto',
			'tablet_slides_space'	=> 24,
			'mobile_slider'			=> true,
			'mobile_slides_type'	=> 'auto',
			'mobile_slides_space'	=> 24,
		] );
		woocommerce_product_loop_start();
		?>

			<?php foreach ( $related_products as $related_product ) : ?>

					<?php
					$post_object = get_post( $related_product->get_id() );

					setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

					wc_get_template_part( 'content', 'product' );
					?>

			<?php endforeach; ?>

		<?php woocommerce_product_loop_end(); ?>

		<?php
		get_template_part( "templates/components/button", null, [
			'type'		=> 'action',
			'small'		=> true,
			'text'		=> esc_html__( "View all", 'bijan' ),
			'link'		=> $shop_page,
			'align'		=> 'center',
			'id'		=> 'related-products-view-mobile',
			'classes'	=> ['hide-desktop-1024'],
		] );
		?>

	</section>
	<?php
endif;

wp_reset_postdata();
