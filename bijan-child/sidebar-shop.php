<?php
/**
 * Compact product-discovery drawer for WooCommerce archives.
 *
 * @package BijanChild
 */

defined( 'ABSPATH' ) || exit;

$active_filter_count = 0;
$filter_state_keys   = [ 's', 'min_price', 'max_price', 'rating_filter', 'instock', 'onsale' ];

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
		<button
			type="button"
			class="cloz-filter-trigger"
			data-cloz-filter-toggle
			aria-expanded="false"
			aria-controls="cloz-filter-panel"
		>
			<span class="cloz-filter-trigger-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16M7 12h10M10 18h4"/><path d="M8 4v4M15 10v4M12 16v4"/></svg>
			</span>
			<span class="cloz-filter-trigger-copy">
				<strong>جست‌وجو و فیلتر محصولات</strong>
				<small>برای پیدا کردن سریع‌تر، این بخش را باز کنید</small>
			</span>
			<?php if ( $active_filter_count ) : ?>
				<span class="cloz-filter-count" aria-label="<?php echo esc_attr( sprintf( '%d فیلتر فعال', $active_filter_count ) ); ?>"><?php echo esc_html( $active_filter_count ); ?></span>
			<?php endif; ?>
			<span class="cloz-filter-trigger-chevron" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="m8 10 4 4 4-4"/></svg>
			</span>
		</button>

		<div id="cloz-filter-panel" class="cloz-filter-panel" role="region" aria-label="ابزار جست‌وجو و فیلتر محصولات" hidden>
			<footer class="cloz-filter-panel-foot">
				<button type="button" class="cloz-filter-reset" data-cloz-archive-state="{}"<?php echo $active_filter_count ? '' : ' disabled'; ?>>پاک‌کردن فیلترها</button>
				<button type="button" class="cloz-filter-done" data-cloz-filter-close>مشاهده محصولات</button>
			</footer>

			<section id="widget-area" class="widget-area cloz-filter-widgets" role="complementary" aria-label="فیلترهای محصولات">
				<?php dynamic_sidebar( 'sidebar-shop' ); ?>
			</section>
		</div>
	</div>
</aside>
