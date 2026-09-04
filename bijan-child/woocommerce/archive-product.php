<?php
/**
 * Unified product-category archive.
 *
 * Product categories use a compact, valid document structure. Other product
 * archives keep WooCommerce's current upstream template.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_product_category() ) {
	$woocommerce_archive = WC()->plugin_path() . '/templates/archive-product.php';
	if ( is_readable( $woocommerce_archive ) ) {
		include $woocommerce_archive;
	}
	return;
}

$term       = get_queried_object();
$term_id    = $term instanceof WP_Term ? (int) $term->term_id : 0;
$is_primary = cloz_is_primary_product_category_page();
$acf_value  = static function ( $field_name ) use ( $term_id ) {
	return function_exists( 'get_field' ) ? get_field( $field_name, 'product_cat_' . $term_id ) : '';
};

$intro            = $is_primary ? $acf_value( 'category_intro' ) : '';
$long_description = $is_primary ? $acf_value( 'category_long_description' ) : '';
$subcategories    = $is_primary ? cloz_get_product_category_subcategories( $term_id ) : [];
$faqs             = $is_primary ? cloz_get_product_category_faqs( $term_id ) : [];
$related_posts    = $is_primary ? $acf_value( 'related_posts' ) : [];
$related_posts    = is_array( $related_posts ) ? $related_posts : [];
$show_sidebar     = is_active_sidebar( 'sidebar-shop' );

$orderby_options = apply_filters(
	'woocommerce_catalog_orderby',
	[
		'menu_order' => __( 'Default sorting', 'bijan' ),
		'popularity' => __( 'Popularity', 'bijan' ),
		'rating'     => __( 'Average rating', 'bijan' ),
		'date'       => __( 'Latest', 'bijan' ),
		'price'      => __( 'Price: low to high', 'bijan' ),
		'price-desc' => __( 'Price: high to low', 'bijan' ),
	]
);
$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : 'menu_order';

get_header( 'shop' );
?>
<div id="page-body" class="page-width cloz-product-category">
	<main id="page-main">
		<?php woocommerce_breadcrumb(); ?>

		<header class="cloz-category-header">
			<div class="section-title-wrap woocommerce-products-header__title page-title">
				<h1 class="section-title">
					<i class="bijan-icon-flash section-title-icon" aria-hidden="true"></i>
					<span class="section-title-title"><?php woocommerce_page_title(); ?></span>
					<div class="section-title-divider"></div>
				</h1>
			</div>

			<?php wc_print_notices(); ?>

			<?php if ( $intro || $subcategories ) : ?>
				<section class="cloz-category-intro-section" aria-label="<?php echo esc_attr( $term->name ); ?>">
					<?php if ( $intro ) : ?>
						<div class="cloz-category-intro-text"><?php echo wpautop( wp_kses_post( $intro ) ); ?></div>
					<?php endif; ?>

					<?php if ( $subcategories ) : ?>
						<div class="cloz-subcategories-grid">
							<?php foreach ( $subcategories as $subcategory ) :
								$title = $subcategory['title'] ?: __( 'Untitled', 'bijan' );
								$image = $subcategory['image_id'] ? wp_get_attachment_image(
									$subcategory['image_id'],
									'medium',
									false,
									[
										'class'    => 'cloz-subcat-image',
										'alt'      => wp_strip_all_tags( $title ),
										'loading'  => 'lazy',
										'decoding' => 'async',
										'sizes'    => '(max-width:480px) 33vw,(max-width:768px) 25vw,(max-width:1200px) 16vw,180px',
									]
								) : '';
								?>
								<a href="<?php echo esc_url( $subcategory['link'] ?: '#' ); ?>" class="cloz-subcat-card">
									<span class="cloz-subcat-image-wrapper"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="cloz-subcat-title"><?php echo esc_html( $title ); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<nav class="cloz-category-sort no-scrollbar" aria-label="<?php esc_attr_e( 'Sort products', 'woocommerce' ); ?>">
				<span class="cloz-sort-label"><?php esc_html_e( 'Sort', 'bijan' ); ?>:</span>
				<div class="cloz-sort-links">
					<?php foreach ( $orderby_options as $value => $label ) :
						$query_args = wp_unslash( $_GET );
						unset( $query_args['paged'], $query_args['product-page'] );
						$query_args['orderby'] = $value;
						$url = add_query_arg( array_map( 'wc_clean', $query_args ), get_term_link( $term ) );
						?>
						<a href="<?php echo esc_url( $url ); ?>" class="cloz-sort-item<?php echo $orderby === $value ? ' is-active' : ''; ?>"<?php echo $orderby === $value ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
			</nav>
		</header>

		<div class="cloz-category-layout<?php echo $show_sidebar ? ' has-sidebar' : ''; ?>">
			<?php if ( $show_sidebar ) : ?>
				<aside id="sidebar" class="sidebar sidebar-shop cloz-category-sidebar" aria-label="<?php esc_attr_e( 'Shop Sidebar', 'bijan' ); ?>">
					<section id="widget-area" class="widget-area" role="complementary">
						<?php dynamic_sidebar( 'sidebar-shop' ); ?>
					</section>
				</aside>
			<?php endif; ?>

			<section class="cloz-category-products" aria-label="<?php esc_attr_e( 'Products', 'woocommerce' ); ?>">
				<?php if ( woocommerce_product_loop() ) : ?>
					<?php woocommerce_product_loop_start(); ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php wc_get_template_part( 'content', 'product' ); ?>
					<?php endwhile; ?>
					<?php woocommerce_product_loop_end(); ?>
					<?php woocommerce_pagination(); ?>
				<?php else : ?>
					<?php wc_get_template( 'loop/no-products-found.php' ); ?>
				<?php endif; ?>
			</section>
		</div>

		<?php if ( $long_description ) : ?>
			<section class="cloz-long-desc-section">
				<div class="cloz-desc-container"><?php echo apply_filters( 'the_content', $long_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</section>
		<?php endif; ?>

		<?php if ( $faqs ) : ?>
			<section class="category-faq-section">
				<div class="faq-container">
					<h2 class="faq-title">سوالات متداول</h2>
					<div class="faq-accordion">
						<?php foreach ( $faqs as $index => $faq ) : $answer_id = 'cloz-faq-answer-' . $index; ?>
							<div class="faq-item">
								<button class="faq-question" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $answer_id ); ?>">
									<span><?php echo esc_html( $faq['question'] ); ?></span>
									<svg class="faq-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
								</button>
								<div class="faq-answer" id="<?php echo esc_attr( $answer_id ); ?>"><div class="faq-answer-content"><?php echo wpautop( wp_kses_post( $faq['answer'] ) ); ?></div></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $related_posts ) : ?>
			<section class="cloz-related-posts-wrapper">
				<h2 class="cloz-related-posts-title">مطالب مرتبط</h2>
				<div class="cloz-related-posts-grid">
					<?php foreach ( $related_posts as $related_post ) :
						$post_id = is_object( $related_post ) ? absint( $related_post->ID ) : absint( $related_post );
						$post_object = get_post( $post_id );
						if ( ! $post_object ) continue;
						$permalink = get_permalink( $post_id );
						$excerpt = get_post_meta( $post_id, 'rank_math_description', true );
						$excerpt = $excerpt ?: wp_trim_words( wp_strip_all_tags( $post_object->post_content ), 20, '…' );
						$image_id = get_post_thumbnail_id( $post_id );
						$image = $image_id ? wp_get_attachment_image( $image_id, 'medium', false, [ 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width:768px) calc(100vw - 40px),(max-width:1200px) 33vw,360px' ] ) : '';
						?>
						<article class="cloz-related-post-card">
							<?php if ( $image ) : ?><a href="<?php echo esc_url( $permalink ); ?>" class="cloz-post-thumbnail"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
							<div class="cloz-post-content">
								<h3 class="cloz-post-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
								<p class="cloz-post-excerpt"><?php echo esc_html( $excerpt ); ?></p>
								<div class="cloz-post-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>">📅 <?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></time><a href="<?php echo esc_url( $permalink ); ?>" class="cloz-post-readmore">مشاهده مطلب</a></div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	</main>
</div>
<?php
get_footer( 'shop' );
