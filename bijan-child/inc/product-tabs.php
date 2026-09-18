<?php
/**
 * Product information tabs and the fixed Cloz delivery policy.
 *
 * @package BijanChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rename WooCommerce's technical label. Delivery information is intentionally
 * rendered below the complete tab box so the tabs stay focused on the product.
 *
 * @param array $tabs Product tabs.
 * @return array
 */
function cloz_customize_product_information_tabs( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = 'ویژگی‌ها';
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'cloz_customize_product_information_tabs', 30 );

/**
 * Show every category assigned to the product at the quiet end of the tabs.
 */
function cloz_render_product_categories_after_tabs() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$terms = wp_get_post_terms( $product->get_id(), 'product_cat' );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}

	$links = [];
	foreach ( $terms as $term ) {
		$url = get_term_link( $term );
		if ( ! is_wp_error( $url ) ) {
			$links[] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $url ),
				esc_html( $term->name )
			);
		}
	}

	if ( ! $links ) {
		return;
	}
	?>
	<div class="cloz-product-categories" aria-label="دسته‌بندی‌های محصول">
		<span>دسته محصول:</span>
		<?php echo wp_kses_post( implode( '<i aria-hidden="true">،</i>', $links ) ); ?>
	</div>
	<?php
}
add_action( 'woocommerce_product_after_tabs', 'cloz_render_product_categories_after_tabs', 20 );

/**
 * Render a short, store-wide delivery and returns summary below product tabs.
 */
function cloz_render_shipping_returns_summary() {
	?>
	<section class="cloz-fulfilment-summary" aria-labelledby="cloz-fulfilment-title">
		<header class="cloz-fulfilment-summary__head">
			<span class="cloz-fulfilment-summary__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" focusable="false"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Zm9 3.9 7-3.5L12 4.4 5 7.9l7 3.5Zm-7.5-2.3v6.5l6.75 3.38v-6.5L4.5 9.1Zm8.25 9.88 6.75-3.38V9.1l-6.75 3.38v6.5Z"/></svg>
			</span>
			<div><h2 id="cloz-fulfilment-title">ارسال و شرایط مرجوعی</h2><p>اطلاعات ضروری خرید، کوتاه و شفاف</p></div>
		</header>

		<div class="cloz-fulfilment-summary__items" role="list">
			<div role="listitem"><strong>زمان تحویل</strong><span>تهران و کرج تا ۴۸ ساعت، سایر استان‌ها تا ۷۲ ساعت</span></div>
			<div role="listitem"><strong>پیگیری سفارش</strong><span>زمان تحویل و تغییر وضعیت سفارش از طریق پیامک اطلاع‌رسانی می‌شود</span></div>
			<div role="listitem"><strong>شرایط مرجوعی</strong><span>در صورت ایراد یا مغایرت محصول، درخواست توسط پشتیبانی بررسی می‌شود</span></div>
		</div>
		<p class="cloz-fulfilment-summary__note">مرجوعی به دلیل تغییر نظر یا پشیمانی از خرید امکان‌پذیر نیست؛ لغو سفارش تا پیش از شروع بسته‌بندی با هماهنگی پشتیبانی امکان دارد.</p>
	</section>
	<?php
}
add_action( 'woocommerce_after_single_product_summary', 'cloz_render_shipping_returns_summary', 18 );

/**
 * Load the tab styles only where they are used.
 */
function cloz_enqueue_product_tabs_styles() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$file = trailingslashit( get_stylesheet_directory() ) . 'assets/product-tabs.css';
	$uri  = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/product-tabs.css';

	wp_enqueue_style(
		'cloz-product-tabs',
		$uri,
		[],
		is_readable( $file ) ? (string) filemtime( $file ) : null
	);
}
add_action( 'wp_enqueue_scripts', 'cloz_enqueue_product_tabs_styles', 25 );
