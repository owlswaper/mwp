<?php
/**
 * Product information tabs and the fixed Cloz delivery policy.
 *
 * @package BijanChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rename WooCommerce's technical label and append the store-wide delivery tab.
 *
 * @param array $tabs Product tabs.
 * @return array
 */
function cloz_customize_product_information_tabs( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = 'ویژگی‌ها';
	}

	$tabs['cloz_shipping_returns'] = [
		'title'    => 'شیوه ارسال و مرجوعی',
		'priority' => 30,
		'callback' => 'cloz_render_shipping_returns_tab',
	];

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'cloz_customize_product_information_tabs', 30 );

/**
 * Render the same delivery and returns information for every product.
 */
function cloz_render_shipping_returns_tab() {
	?>
	<section class="cloz-fulfilment" aria-labelledby="cloz-fulfilment-title">
		<header class="cloz-fulfilment__hero">
			<div class="cloz-fulfilment__mark" aria-hidden="true">
				<svg viewBox="0 0 24 24" focusable="false"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Zm9 3.9 7-3.5L12 4.4 5 7.9l7 3.5Zm-7.5-2.3v6.5l6.75 3.38v-6.5L4.5 9.1Zm8.25 9.88 6.75-3.38V9.1l-6.75 3.38v6.5Z"/></svg>
			</div>
			<div>
				<span>ارسال اکسپرس کلوز</span>
				<h2 id="cloz-fulfilment-title">سفارش‌تان سریع، مرتب و قابل پیگیری به دستتان می‌رسد</h2>
				<p>از آماده‌سازی بسته تا زمان تحویل، مسیر سفارش شفاف است و هر مرحله را هم از طریق پیامک به شما اطلاع می‌دهیم.</p>
			</div>
		</header>

		<div class="cloz-fulfilment__grid">
			<article class="cloz-policy-card cloz-policy-card--accent">
				<span class="cloz-policy-card__icon" aria-hidden="true">۱</span>
				<div><h3>زمان تحویل اکسپرس</h3><p>در تهران و کرج، بیشتر سفارش‌ها تا <strong>۴۸ ساعت</strong> و در سایر استان‌ها تا <strong>۷۲ ساعت</strong> تحویل می‌شوند.</p></div>
			</article>
			<article class="cloz-policy-card">
				<span class="cloz-policy-card__icon" aria-hidden="true">۲</span>
				<div><h3>پیگیری دقیق سفارش</h3><p>روز و بازه ساعتی تحویل مرسوله در بخش <strong>پیگیری سفارش کلوز</strong> نمایش داده می‌شود؛ تغییر وضعیت هر مرحله نیز برایتان پیامک خواهد شد.</p></div>
			</article>
			<article class="cloz-policy-card">
				<span class="cloz-policy-card__icon" aria-hidden="true">۳</span>
				<div><h3>بسته‌بندی مناسب هدیه</h3><p>محصول‌ها با بسته‌بندی شیک و مرتب آماده می‌شوند؛ بنابراین می‌توانید سفارش را مستقیماً برای هدیه هم استفاده کنید.</p></div>
			</article>
			<article class="cloz-policy-card">
				<span class="cloz-policy-card__icon" aria-hidden="true">۴</span>
				<div><h3>لغو پیش از بسته‌بندی</h3><p>تا زمانی که بسته‌بندی سفارش آغاز نشده باشد، امکان لغو ارسال وجود دارد. برای این کار کافی است سریع با پشتیبانی کلوز در ارتباط باشید.</p></div>
			</article>
		</div>

		<aside class="cloz-return-note">
			<div class="cloz-return-note__icon" aria-hidden="true">↺</div>
			<div>
				<h3>شرایط مرجوعی، کوتاه و شفاف</h3>
				<p>مرجوع کردن سفارش به دلیل تغییر نظر یا پشیمانی از خرید امکان‌پذیر نیست. اگر محصول ایراد داشته باشد یا با توضیحات و تصاویر صفحه محصول مغایرتی ببینید، درخواست شما توسط پشتیبانی بررسی و امکان مرجوعی فراهم می‌شود.</p>
			</div>
		</aside>
	</section>
	<?php
}

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
