<?php
/**
 * Related products using the parent theme's original presentation, with its
 * native slider enabled on every breakpoint.
 *
 * @package WooCommerce\Templates
 * @version 10.3.0
 */

use Bijan\Utils\Options;

defined( 'ABSPATH' ) || exit;

if ( $related_products ) :
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}
	?>
	<section class="related">
		<?php
		$heading   = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );
		$shop_page = get_permalink( wc_get_page_id( 'shop' ) );
		if ( $heading ) {
			$options = Options::get_options( [
				'wc-single-end-products-title_icon' => 'bijan-icon-flash',
				'wc-single-end-products-title_tag'  => 'h3',
			] );
			echo '<div class="related-product-title">';
			get_template_part( 'templates/components/section_title', null, [
				'icon'  => $options['wc-single-end-products-title_icon'],
				'tag'   => $options['wc-single-end-products-title_tag'],
				'title' => $heading,
				'link'  => $shop_page,
			] );
			get_template_part( 'templates/components/button', null, [
				'type'  => 'action',
				'small' => true,
				'text'  => esc_html__( 'View all', 'bijan' ),
				'link'  => $shop_page,
			] );
			echo '</div>';
		}

		wc_set_loop_prop( 'bijan_loop_props', [
			'style'                 => 'products-style-2',
			'desktop_slider'        => true,
			'desktop_slides_type'   => 'count',
			'desktop_slides'        => 5,
			'desktop_slides_space'  => 24,
			'tablet_slider'         => true,
			'tablet_slides_type'    => 'auto',
			'tablet_slides_space'   => 24,
			'mobile_slider'         => true,
			'mobile_slides_type'    => 'auto',
			'mobile_slides_space'   => 24,
		] );
		woocommerce_product_loop_start();

		foreach ( $related_products as $related_product ) {
			$post_object = get_post( $related_product->get_id() );
			if ( ! $post_object ) {
				continue;
			}
			setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();

		get_template_part( 'templates/components/button', null, [
			'type'    => 'action',
			'small'   => true,
			'text'    => esc_html__( 'View all', 'bijan' ),
			'link'    => $shop_page,
			'align'   => 'center',
			'id'      => 'related-products-view-mobile',
			'classes' => [ 'hide-desktop-1024' ],
		] );
		?>
	</section>
	<?php
endif;

wp_reset_postdata();
