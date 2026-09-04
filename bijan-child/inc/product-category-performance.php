<?php
/**
 * Product-category performance layer.
 *
 * The category archive owns one template, one stylesheet and one tiny script.
 * Shared header/footer and WooCommerce product-card assets remain available so
 * cart, filters, account actions and structured data continue to work.
 */

defined( 'ABSPATH' ) || exit;

function cloz_is_primary_product_category_page() {
	if ( ! is_product_category() || is_paged() ) {
		return false;
	}

	$page_numbers = [
		absint( get_query_var( 'paged' ) ),
		absint( get_query_var( 'page' ) ),
		absint( get_query_var( 'product-page' ) ),
	];

	if ( isset( $_GET['product-page'] ) ) {
		$page_numbers[] = absint( wp_unslash( $_GET['product-page'] ) );
	}

	return max( $page_numbers ) <= 1;
}

function cloz_enqueue_product_category_assets() {
	if ( ! is_product_category() ) {
		return;
	}

	$css_path = BIJAN_CHILD_DIR . 'assets/product-category.min.css';
	$js_path  = BIJAN_CHILD_DIR . 'assets/product-category.min.js';

	wp_enqueue_style(
		'cloz-product-category',
		BIJAN_CHILD_URI . 'assets/product-category.min.css',
		[ 'bijan-wc' ],
		file_exists( $css_path ) ? filemtime( $css_path ) : BIJAN_CHILD_VERSION
	);

	wp_enqueue_script(
		'cloz-product-category',
		BIJAN_CHILD_URI . 'assets/product-category.min.js',
		[],
		file_exists( $js_path ) ? filemtime( $js_path ) : BIJAN_CHILD_VERSION,
		true
	);

	$masonry_path = get_template_directory() . '/assets/libs/masonry.pkgd.min.js';
	wp_localize_script(
		'cloz-product-category',
		'clozCategoryAssets',
		[
			'masonryUrl' => add_query_arg(
				'ver',
				file_exists( $masonry_path ) ? filemtime( $masonry_path ) : BIJAN_CHILD_VERSION,
				get_template_directory_uri() . '/assets/libs/masonry.pkgd.min.js'
			),
			'rtl' => is_rtl(),
		]
	);
}
add_action( 'wp_enqueue_scripts', 'cloz_enqueue_product_category_assets', 40 );

/** The small above-the-fold contract is inlined; the full visual layer is async. */
function cloz_print_product_category_critical_css() {
	if ( ! is_product_category() ) {
		return;
	}

	$critical_paths = [
		BIJAN_CHILD_DIR . 'assets/product-category-critical.min.css',
		BIJAN_CHILD_DIR . 'assets/product-category-filter-critical.min.css',
	];
	$font_path = BIJAN_CHILD_DIR . 'assets/product-category-icons.woff2';
	if ( ! is_readable( $font_path ) ) {
		return;
	}

	$font_url = add_query_arg( 'ver', filemtime( $font_path ), BIJAN_CHILD_URI . 'assets/product-category-icons.woff2' );
	$css      = '';
	foreach ( $critical_paths as $critical_path ) {
		if ( is_readable( $critical_path ) ) {
			$css .= file_get_contents( $critical_path );
		}
	}
	$css = str_replace( '__CLOZ_FONT_URL__', esc_url_raw( $font_url ), $css );

	echo '<style id="cloz-product-category-critical">' . $css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'cloz_print_product_category_critical_css', 2 );

function cloz_defer_product_category_stylesheet( $html, $handle ) {
	if ( 'cloz-product-category' !== $handle || ! is_product_category() ) {
		return $html;
	}

	$deferred = str_replace(
		[ "media='all'", 'media="all"' ],
		[ "media='print' onload=\"this.onload=null;this.media='all'\"", "media=\"print\" onload=\"this.onload=null;this.media='all'\"" ],
		$html
	);

	return $deferred . '<noscript>' . $html . '</noscript>';
}
add_filter( 'style_loader_tag', 'cloz_defer_product_category_stylesheet', 20, 2 );

/** Remove bundles that have no component in the dedicated archive. */
function cloz_dequeue_unused_product_category_assets() {
	if ( ! is_product_category() ) {
		return;
	}

	foreach (
		[
			'contact-form-7',
			'contact-form-7-rtl',
			'wc-blocks-style',
			'wc-blocks-style-rtl',
			'bijan-bootstrap',
			'bijan-bootstrap-rtl',
			'bijan-wc-archive',
			'bijan-icons',
		]
		as $handle
	) {
		wp_dequeue_style( $handle );
	}

	foreach (
		[
			'contact-form-7',
			'swv',
			'bijan-masonry',
			'bijan-megamenu',
			'wc-price-slider',
			'jquery-ui-slider',
			'jquery-ui-mouse',
			'jquery-ui-touch-punch',
			'accounting',
		]
		as $handle
	) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'cloz_dequeue_unused_product_category_assets', PHP_INT_MAX );
// The price widget can enqueue these scripts while the sidebar is rendered.
add_action( 'wp_footer', 'cloz_dequeue_unused_product_category_assets', 0 );

/** Start the critical icon request before the CSS is parsed. */
function cloz_preload_product_category_icon_font() {
	if ( ! is_product_category() ) {
		return;
	}

	$font_path = BIJAN_CHILD_DIR . 'assets/product-category-icons.woff2';
	if ( ! is_readable( $font_path ) ) {
		return;
	}

	$font_url  = add_query_arg( 'ver', filemtime( $font_path ), BIJAN_CHILD_URI . 'assets/product-category-icons.woff2' );
	?>
	<link rel="preload" href="<?php echo esc_url( $font_url ); ?>" as="font" type="font/woff2" crossorigin fetchpriority="high">
	<?php
}
add_action( 'wp_head', 'cloz_preload_product_category_icon_font', 1 );

function cloz_product_category_link_url( $link ) {
	if ( is_array( $link ) ) {
		$link = $link['url'] ?? '';
	}

	return is_string( $link ) ? $link : '';
}

function cloz_get_product_category_subcategories( $term_id ) {
	$subcategories = [];

	for ( $index = 0; $index < 50; $index++ ) {
		$image_id = get_term_meta( $term_id, "sub_categories_{$index}_subcat_image", true );
		$title    = get_term_meta( $term_id, "sub_categories_{$index}_subcat_title", true );
		$link     = get_term_meta( $term_id, "sub_categories_{$index}_subcat_link", true );

		if ( ! $image_id && ! $title && ! $link ) {
			break;
		}

		$subcategories[] = [
			'image_id' => absint( $image_id ),
			'title'    => (string) $title,
			'link'     => cloz_product_category_link_url( $link ),
		];
	}

	return $subcategories;
}

function cloz_get_product_category_faqs( $term_id ) {
	$faqs  = [];
	$count = min( 50, absint( get_term_meta( $term_id, 'faq_list', true ) ) );

	for ( $index = 0; $index < $count; $index++ ) {
		$question = get_term_meta( $term_id, "faq_list_{$index}_faq_question", true );
		$answer   = get_term_meta( $term_id, "faq_list_{$index}_faq_answer", true );

		if ( $question && $answer ) {
			$faqs[] = [ 'question' => $question, 'answer' => $answer ];
		}
	}

	return $faqs;
}
