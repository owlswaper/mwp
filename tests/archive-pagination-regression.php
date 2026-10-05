<?php
/** Isolated regression checks for the theme's native archive contract. */
namespace Bijan {
	class Utils {
		public static function prepare_html_classes( $classes, $attr ) { return 'class="' . implode( ' ', $classes ) . '"'; }
	}
}
namespace Bijan\Utils {
	class Options { public static function get_options( $defaults ) { return $defaults; } }
}
namespace {
	define( 'ABSPATH', __DIR__ );
	define( 'HOUR_IN_SECONDS', 3600 );
	$hooks = [];
	$category = true;
	$query_vars = [];
	$loop = [ 'current_page' => 1, 'total_pages' => 4 ];
	$plain = false;
	function add_action( $hook, $callback, $priority = 10, $args = 1 ) { $GLOBALS['hooks'][$hook][] = $callback; }
	function add_filter( $hook, $callback, $priority = 10, $args = 1 ) { add_action( $hook, $callback, $priority, $args ); }
	function is_product_category() { return $GLOBALS['category']; }
	function is_product_taxonomy() { return is_product_category(); }
	function is_shop() { return false; }
	function is_post_type_archive( $type ) { return false; }
	function is_search() { return false; }
	function is_paged() { return get_query_var( 'paged' ) > 1; }
	function get_query_var( $key ) { return $GLOBALS['query_vars'][$key] ?? 0; }
	function wc_get_loop_prop( $key, $default = null ) { return $GLOBALS['loop'][$key] ?? $default; }
	function get_pagenum_link( $page ) {
		$url = 'https://example.test/product-category/shirts/';
		if ( $page > 1 ) $url .= $GLOBALS['plain'] ? '?paged=' . $page : 'page/' . $page . '/';
		if ( isset( $_GET['orderby'] ) ) $url .= ( strpos( $url, '?' ) === false ? '?' : '&' ) . 'orderby=' . $_GET['orderby'];
		return Cloz_Archive_Ajax_Filters::clean_pagination_link( $url );
	}
	function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) ); }
	function sanitize_text_field( $value ) { return strip_tags( $value ); }
	function absint( $value ) { return abs( (int) $value ); }
	function wp_unslash( $value ) { return $value; }
	function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
	function remove_query_arg( $keys, $url ) {
		$parts = parse_url( $url ); parse_str( $parts['query'] ?? '', $query );
		foreach ( $keys as $key ) unset( $query[$key] );
		return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . ( $query ? '?' . http_build_query( $query ) : '' );
	}
	function esc_url( $url ) { return htmlspecialchars( $url, ENT_QUOTES ); }
	function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
	function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
	function __( $text, $domain = '' ) { return $text; }
	function get_template_directory() { return dirname( __DIR__ ) . '/bijan'; }
	function is_active_sidebar( $id ) { return false; }
	function wp_unique_id() { static $id = 0; return ++$id; }
	function selected( $first, $second = true ) { if ( $first === $second ) echo ' selected'; }
	function wc_query_string_form_fields( $value, $exclude ) {}
	function wc_clean( $value ) { return sanitize_text_field( $value ); }
	function do_action( $hook ) {}
	function wc_get_template( $file, $args ) { extract( $args ); include dirname( __DIR__ ) . '/bijan-child/woocommerce/' . $file; }
	function check( $condition, $message ) { if ( ! $condition ) throw new \RuntimeException( $message ); }
	require dirname( __DIR__ ) . '/bijan-child/inc/archive-ajax-filters.php';

	ob_start(); Cloz_Archive_Ajax_Filters::print_page_data(); $html = ob_get_clean();
	check( strpos( $html, 'data-page="1"' ) !== false && strpos( $html, 'data-next="https://example.test/product-category/shirts/page/2/"' ) !== false, 'First page points at a real next page.' );
	$loop['current_page'] = 4;
	ob_start(); Cloz_Archive_Ajax_Filters::print_page_data(); $html = ob_get_clean();
	check( strpos( $html, 'data-next=""' ) !== false, 'Last page terminates loading.' );
	$query_vars['paged'] = 3;
	$_GET['orderby'] = 'price';
	check( Cloz_Archive_Ajax_Filters::category_canonical( 'wrong' ) === 'https://example.test/product-category/shirts/page/3/', 'Canonical keeps page 3 and removes filter parameters.' );
	$plain = true;
	check( Cloz_Archive_Ajax_Filters::category_canonical( 'wrong' ) === 'https://example.test/product-category/shirts/?paged=3', 'Plain pagination remains self-canonical.' );
	$category = false;
	check( Cloz_Archive_Ajax_Filters::category_canonical( 'original' ) === 'original', 'Other archives retain their SEO canonical.' );
	$category = true; $plain = false; $_GET = [];
	$source = file_get_contents( dirname( __DIR__ ) . '/bijan-child/functions.php' );
	$start = strpos( $source, 'function cloz_is_primary_product_category_page()' );
	$end = strpos( $source, '// نمایش معرفی', $start );
	eval( substr( $source, $start, $end - $start ) );
	check( ! cloz_is_primary_product_category_page(), 'Paged category omits editorial content.' );
	$query_vars = [ 'paged' => 1 ];
	check( cloz_is_primary_product_category_page(), 'AJAX page 1 retains editorial content.' );
	$_GET['product-page'] = 2;
	check( ! cloz_is_primary_product_category_page(), 'WooCommerce product-page omits editorial content.' );
	$_GET = [];

	foreach ( [ 'products', 'empty', 'categories' ] as $mode ) {
		ob_start();
		include dirname( __DIR__ ) . '/bijan-child/woocommerce/global/wrapper-start.php';
		echo '<header class="woocommerce-products-header"><h1>Shirts</h1>';
		if ( 'empty' === $mode ) include dirname( __DIR__ ) . '/bijan-child/woocommerce/loop/no-products-found.php';
		else { if ( 'categories' === $mode ) Cloz_Archive_Ajax_Filters::ensure_archive_container(); else { $orderby = ''; include dirname( __DIR__ ) . '/bijan-child/woocommerce/loop/orderby.php'; } echo '<ul class="products"><li class="product">Product</li></ul>'; }
		include dirname( __DIR__ ) . '/bijan-child/woocommerce/global/wrapper-end.php';
		$html = ob_get_clean();
		check( substr_count( $html, 'id="primary"' ) === 1, 'Archive contains exactly one primary ID.' );
		check( substr_count( $html, '<div' ) === substr_count( $html, '</div>' ), 'Populated and empty wrapper tags are balanced.' );
		check( strpos( $html, 'entry-container' ) !== false, 'Empty results keep the AJAX replacement target.' );
	}
	echo "PASS page metadata, terminal page, clean self-canonicals, content gating, populated/empty/category-only wrapper rendering\n";
}
