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

/** Make the first product image an explicit LCP candidate without eager-loading the rest. */
function cloz_prioritize_first_category_product_image( $attributes, $attachment ) {
	if ( ! is_product_category() || ! is_main_query() ) {
		return $attributes;
	}

	global $wp_query;
	$first_product = isset( $wp_query->posts[0] ) && $wp_query->posts[0] instanceof WP_Post ? $wp_query->posts[0] : null;
	if ( ! $first_product || (int) get_post_thumbnail_id( $first_product ) !== (int) $attachment->ID ) {
		return $attributes;
	}

	$attributes['loading']       = 'eager';
	$attributes['fetchpriority'] = 'high';
	$attributes['decoding']      = 'async';
	$attributes['sizes']         = '(max-width:767px) calc((100vw - 56px)/2),(max-width:1200px) calc((100vw - 88px)/2),260px';

	return $attributes;
}
add_filter( 'wp_get_attachment_image_attributes', 'cloz_prioritize_first_category_product_image', 20, 2 );

function cloz_get_compressx_avif_url( $image_url ) {
	$uploads = wp_get_upload_dir();
	$prefix  = trailingslashit( $uploads['baseurl'] );
	if ( 0 !== strpos( $image_url, $prefix ) ) {
		return '';
	}

	$relative = substr( $image_url, strlen( $prefix ) );
	$path     = WP_CONTENT_DIR . '/compressx-nextgen/uploads/' . rawurldecode( $relative ) . '.avif';
	if ( ! is_readable( $path ) ) {
		return '';
	}

	return trailingslashit( content_url( 'compressx-nextgen/uploads' ) ) . $relative . '.avif';
}

/** Preload the actual AVIF selected by CompressX before the large navigation HTML is parsed. */
function cloz_preload_first_category_product_image() {
	if ( ! is_product_category() ) {
		return;
	}

	global $wp_query;
	$first_product = isset( $wp_query->posts[0] ) && $wp_query->posts[0] instanceof WP_Post ? $wp_query->posts[0] : null;
	$image_id      = $first_product ? get_post_thumbnail_id( $first_product ) : 0;
	$image          = $image_id ? wp_get_attachment_image_src( $image_id, 'woocommerce_thumbnail' ) : false;
	$avif_url       = $image ? cloz_get_compressx_avif_url( $image[0] ) : '';
	if ( ! $avif_url ) {
		return;
	}

	$avif_srcset = [];
	$srcset      = wp_get_attachment_image_srcset( $image_id, 'woocommerce_thumbnail' );
	foreach ( array_filter( array_map( 'trim', explode( ',', (string) $srcset ) ) ) as $candidate ) {
		$parts         = preg_split( '/\s+/', $candidate, 2 );
		$candidate_url = cloz_get_compressx_avif_url( $parts[0] );
		if ( $candidate_url ) {
			$avif_srcset[] = $candidate_url . ( isset( $parts[1] ) ? ' ' . $parts[1] : '' );
		}
	}
	$sizes = '(max-width:767px) calc((100vw - 56px)/2),(max-width:1200px) calc((100vw - 88px)/2),260px';
	?>
	<link rel="preload" as="image" type="image/avif" href="<?php echo esc_url( $avif_url ); ?>"<?php if ( $avif_srcset ) : ?> imagesrcset="<?php echo esc_attr( implode( ', ', $avif_srcset ) ); ?>" imagesizes="<?php echo esc_attr( $sizes ); ?>"<?php endif; ?> fetchpriority="high">
	<?php
}
add_action( 'wp_head', 'cloz_preload_first_category_product_image', 1 );

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
