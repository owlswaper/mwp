<?php
/**
 * Small, category-only front-end performance improvements.
 *
 * Keep these optimizations isolated from product pages and the rest of the
 * site so optional components on those templates continue to work normally.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Iconly is used in the header and first product card, but its @font-face rule
 * is discovered only after the page CSS. Preloading the exact WOFF2 URL turns
 * that late CSS -> font chain into an early parallel request.
 */
function cloz_preload_product_category_icon_font() {
	if ( ! is_product_category() ) {
		return;
	}

	$font_url = get_template_directory_uri() . '/assets/fonts/iconly.woff2?1771714076517';
	?>
	<link rel="preload" href="<?php echo esc_url( $font_url ); ?>" as="font" type="font/woff2" crossorigin fetchpriority="high">
	<?php
}
add_action( 'wp_head', 'cloz_preload_product_category_icon_font', 1 );

/**
 * The parent stylesheet applies will-change to every link. A category archive
 * contains hundreds of links, so asking the browser to prepare a layer for all
 * of them wastes memory and style/compositing work. Transitions still render
 * identically without the permanent hint.
 *
 * Content below the product grid is expensive but initially off-screen. Modern
 * browsers can skip its first layout/paint while preserving the rendered UI
 * when the visitor scrolls to it.
 */
function cloz_product_category_rendering_hints() {
	if ( ! is_product_category() ) {
		return;
	}

	$css = '
		body.tax-product_cat a{will-change:auto}
		body.tax-product_cat .category-faq-section,
		body.tax-product_cat .cloz-related-posts-wrapper{
			content-visibility:auto;
			contain-intrinsic-size:auto 800px;
		}
	';

	wp_add_inline_style( 'bijan-wc-archive', $css );
}
add_action( 'wp_enqueue_scripts', 'cloz_product_category_rendering_hints', 30 );
