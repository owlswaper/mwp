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
		]
		as $handle
	) {
		wp_dequeue_style( $handle );
	}

	foreach ( [ 'contact-form-7', 'swv' ] as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'cloz_dequeue_unused_product_category_assets', PHP_INT_MAX );

/** Start the critical icon request before the CSS is parsed. */
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
