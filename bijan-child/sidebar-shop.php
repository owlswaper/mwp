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

$search_value = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
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
				<svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16M7 12h10M10 17h4"/><circle cx="7" cy="7" r="1.7"/><circle cx="15" cy="12" r="1.7"/><circle cx="11" cy="17" r="1.7"/></svg>
			</span>
			<span class="cloz-filter-trigger-copy">
				<strong>راحت‌تر محصولتو پیدا کن</strong>
				<small>جست‌وجو، فیلتر و انتخاب دقیق‌تر</small>
			</span>
			<?php if ( $active_filter_count ) : ?>
				<span class="cloz-filter-count" aria-label="<?php echo esc_attr( sprintf( '%d فیلتر فعال', $active_filter_count ) ); ?>"><?php echo esc_html( $active_filter_count ); ?></span>
			<?php endif; ?>
			<span class="cloz-filter-trigger-chevron" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="m8 10 4 4 4-4"/></svg>
			</span>
		</button>

		<div class="cloz-filter-backdrop" data-cloz-filter-close hidden></div>

		<div id="cloz-filter-panel" class="cloz-filter-panel" role="region" aria-label="ابزار جست‌وجو و فیلتر محصولات" hidden>
			<header class="cloz-filter-panel-head">
				<div>
					<strong>جست‌وجو و فیلتر</strong>
					<span>نتیجه‌ها هم‌زمان به‌روزرسانی می‌شوند</span>
				</div>
				<button type="button" class="cloz-filter-close" data-cloz-filter-close aria-label="بستن فیلترها">&times;</button>
			</header>

			<form class="cloz-archive-search" role="search">
				<label for="cloz-archive-search-field">جست‌وجو در همین دسته</label>
				<div class="cloz-archive-search-control">
					<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
					<input id="cloz-archive-search-field" type="search" name="s" value="<?php echo esc_attr( $search_value ); ?>" placeholder="نام یا مدل محصول را بنویس…" autocomplete="off">
					<button type="submit">جست‌وجو</button>
				</div>
			</form>

			<section id="widget-area" class="widget-area cloz-filter-widgets" role="complementary" aria-label="فیلترهای محصولات">
				<?php dynamic_sidebar( 'sidebar-shop' ); ?>
			</section>

			<footer class="cloz-filter-panel-foot">
				<button type="button" class="cloz-filter-reset" data-cloz-archive-state="{}"<?php echo $active_filter_count ? '' : ' disabled'; ?>>پاک‌کردن فیلترها</button>
				<button type="button" class="cloz-filter-done" data-cloz-filter-close>مشاهده محصولات</button>
			</footer>
		</div>
	</div>
</aside>
