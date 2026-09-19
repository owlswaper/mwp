<?php
/**
 * Compact product-discovery drawer for WooCommerce archives.
 *
 * @package BijanChild
 */

defined( 'ABSPATH' ) || exit;

$active_filter_count = 0;
$filter_state_keys   = [ 'min_price', 'max_price', 'rating_filter', 'instock', 'onsale' ];

foreach ( wp_unslash( $_GET ) as $key => $value ) {
	$key = sanitize_key( $key );
	if ( in_array( $key, $filter_state_keys, true ) || 0 === strpos( $key, 'filter_' ) ) {
		if ( '' !== trim( is_array( $value ) ? implode( '', $value ) : (string) $value ) ) {
			$active_filter_count++;
		}
	}
}

?>
<aside id="sidebar" class="sidebar sidebar-shop col-12" aria-label="جست‌وجو و فیلتر محصولات">
	<div class="cloz-filter-shell">
		<div class="cloz-discovery-row">
		<button
			type="button"
			class="cloz-filter-trigger"
			data-cloz-filter-toggle
			aria-label="<?php echo esc_attr( $active_filter_count ? sprintf( 'فیلتر محصولات، %d فیلتر فعال', $active_filter_count ) : 'فیلتر محصولات' ); ?>"
			aria-expanded="false"
			aria-controls="cloz-filter-panel"
		>
			<span class="cloz-filter-trigger-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16M7 12h10M10 18h4"/><path d="M8 4v4M15 10v4M12 16v4"/></svg>
			</span>
			<?php if ( $active_filter_count ) : ?>
				<span class="cloz-filter-count" aria-hidden="true"><?php echo esc_html( $active_filter_count ); ?></span>
			<?php endif; ?>
		</button>
		<form id="cloz-catalog-search" class="cloz-catalog-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="cloz-catalog-search-input">جست‌وجوی محصولات</label>
			<input id="cloz-catalog-search-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="جست‌وجو در محصولات" autocomplete="off" />
			<input type="hidden" name="post_type" value="product" />
			<?php if ( get_search_query() ) : ?>
				<button type="button" class="cloz-search-clear" data-cloz-search-clear aria-label="پاک‌کردن جست‌وجو">×</button>
			<?php endif; ?>
			<button type="submit" class="cloz-search-submit" aria-label="جست‌وجو"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m15.5 15.5 4.5 4.5"/></svg></button>
		</form>
		</div>

		<div id="cloz-filter-panel" class="cloz-filter-panel" role="region" aria-label="فیلتر محصولات" hidden>
			<footer class="cloz-filter-panel-foot">
				<button type="button" class="cloz-filter-reset" data-cloz-filter-reset<?php echo $active_filter_count ? '' : ' disabled'; ?>>پاک‌کردن فیلترها</button>
				<button type="button" class="cloz-filter-done" data-cloz-filter-close>مشاهده محصولات</button>
			</footer>

			<section id="widget-area" class="widget-area cloz-filter-widgets" role="complementary" aria-label="فیلترهای محصولات">
				<?php dynamic_sidebar( 'sidebar-shop' ); ?>
			</section>
		</div>
	</div>
</aside>
