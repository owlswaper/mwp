<?php
/** A light, native-scroll ACF category discovery rail. */
defined( 'ABSPATH' ) || exit;

function cloz_enqueue_category_discovery() {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return;
	}
	$id = get_queried_object_id();
	if ( ! get_term_meta( $id, 'sub_categories_0_subcat_image', true ) && ! get_term_meta( $id, 'sub_categories_0_subcat_title', true ) && ! get_term_meta( $id, 'sub_categories_0_subcat_link', true ) ) {
		return;
	}
	$directory = trailingslashit( get_stylesheet_directory() );
	$uri = trailingslashit( get_stylesheet_directory_uri() );
	wp_enqueue_style( 'cloz-category-discovery', $uri . 'assets/category-discovery.css', [ 'cloz-archive-ajax-filters' ], (string) filemtime( $directory . 'assets/category-discovery.css' ) );
	wp_enqueue_script( 'cloz-category-discovery', $uri . 'assets/category-discovery.js', [], (string) filemtime( $directory . 'assets/category-discovery.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'cloz_enqueue_category_discovery', 45 );

function cloz_render_category_discovery( $items ) {
	if ( ! $items ) return;
	$id = wp_unique_id( 'cloz-discovery-' );
	?>
	<section class="cloz-category-discovery" aria-labelledby="<?php echo esc_attr( $id ); ?>-heading">
		<div class="cloz-discovery-header">
			<h2 class="cloz-discovery-heading" id="<?php echo esc_attr( $id ); ?>-heading">دسته‌های مرتبط</h2>
			<div class="cloz-discovery-actions" hidden>
				<button type="button" class="cloz-discovery-prev" aria-label="دسته‌های قبلی" aria-controls="<?php echo esc_attr( $id ); ?>-rail"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
				<button type="button" class="cloz-discovery-pause" aria-label="توقف حرکت خودکار دسته‌ها" aria-pressed="false" aria-controls="<?php echo esc_attr( $id ); ?>-rail"><svg data-discovery-pause-icon viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5v14M15 5v14"/></svg><svg data-discovery-play-icon hidden viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 10 7-10 7Z"/></svg></button>
				<button type="button" class="cloz-discovery-next" aria-label="دسته‌های بعدی" aria-controls="<?php echo esc_attr( $id ); ?>-rail"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
			</div>
		</div>
		<div class="cloz-discovery-rail" id="<?php echo esc_attr( $id ); ?>-rail" tabindex="0" role="group" aria-label="دسته‌های مرتبط؛ برای دیدن موارد بیشتر به صورت افقی پیمایش کنید">
			<?php foreach ( $items as $index => $item ) :
				$title = trim( (string) ( $item['title'] ?? '' ) );
				$title = $title ?: 'مشاهدهٔ دسته';
				$raw_link = $item['link'] ?? '';
				$link = esc_url( is_array( $raw_link ) ? ( $raw_link['url'] ?? '' ) : $raw_link );
				$tag = $link ? 'a' : 'div';
				?>
				<<?php echo $tag; ?> class="cloz-discovery-card"<?php if ( $link ) : ?> href="<?php echo $link; ?>"<?php endif; ?>>
					<span class="cloz-discovery-image-wrap">
						<?php if ( ! empty( $item['image_id'] ) ) {
							echo wp_get_attachment_image( absint( $item['image_id'] ), 'medium', false, [
								'class' => 'cloz-discovery-image', 'alt' => '',
								'loading' => $index < 2 ? 'eager' : 'lazy', 'decoding' => 'async',
								'fetchpriority' => 'low', 'sizes' => '(max-width: 768px) 170px, 208px',
							] );
						} ?>
					</span>
					<span class="cloz-discovery-title"><?php echo esc_html( $title ); ?></span>
				</<?php echo $tag; ?>>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}
