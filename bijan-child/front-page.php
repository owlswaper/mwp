<?php
/**
 * Fast, product-first homepage for CLOZE.
 *
 * Server-rendered, one stylesheet and no homepage JavaScript. Dataset IDs are
 * cached while live price and stock markup still come from WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

function clz_home_v4_shop_url() {
	$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
	return $url ?: home_url( '/shop/' );
}

function clz_home_v4_blog_url() {
	$page_id = absint( get_option( 'page_for_posts' ) );
	return $page_id ? get_permalink( $page_id ) : home_url( '/blog/' );
}

function clz_home_v4_product_ids( $args = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	try {
		$ids = wc_get_products(
			wp_parse_args(
				$args,
				array(
					'status' => 'publish', 'visibility' => 'visible', 'stock_status' => 'instock',
					'return' => 'ids', 'limit' => 9, 'orderby' => 'date', 'order' => 'DESC',
				)
			)
		);
	} catch ( Throwable $exception ) {
		return array();
	}
	return array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
}

function clz_home_v4_dataset() {
	$cache_key = 'clz_home_v4_dataset_1';
	$cached = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$latest = clz_home_v4_product_ids( array( 'limit' => 9 ) );
	$sale = array();
	if ( function_exists( 'wc_get_product_ids_on_sale' ) ) {
		$on_sale = array_values( array_diff( array_map( 'absint', wc_get_product_ids_on_sale() ), $latest ) );
		if ( $on_sale ) {
			$sale = clz_home_v4_product_ids( array( 'limit' => 4, 'include' => array_slice( $on_sale, 0, 120 ) ) );
		}
	}

	$categories = get_terms(
		array(
			'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 9,
			'orderby' => 'count', 'order' => 'DESC',
			'exclude' => array_filter( array( absint( get_option( 'default_product_cat' ) ) ) ),
		)
	);
	$category_ids = is_wp_error( $categories ) ? array() : wp_list_pluck( $categories, 'term_id' );

	$post_query = new WP_Query(
		array(
			'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3,
			'ignore_sticky_posts' => true, 'no_found_rows' => true, 'fields' => 'ids',
			'orderby' => 'date', 'order' => 'DESC', 'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$data = array(
		'hero' => array_shift( $latest ),
		'products' => array_slice( $latest, 0, 8 ),
		'sale' => array_slice( $sale, 0, 4 ),
		'categories' => array_map( 'absint', $category_ids ),
		'posts' => array_map( 'absint', $post_query->posts ),
	);
	set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

function clz_home_v4_products( $ids ) {
	$products = array();
	foreach ( (array) $ids as $id ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : false;
		if ( $product && $product->is_visible() && $product->is_in_stock() ) {
			$products[] = $product;
		}
	}
	return $products;
}

function clz_home_v4_product_image( $product, $hero = false ) {
	$image_id = $product ? $product->get_image_id() : 0;
	if ( ! $image_id ) {
		return function_exists( 'wc_placeholder_img' ) ? wc_placeholder_img( $hero ? 'medium_large' : 'woocommerce_thumbnail' ) : '';
	}
	$attributes = array(
		'alt' => $product->get_name(), 'loading' => $hero ? 'eager' : 'lazy', 'decoding' => 'async',
		'sizes' => $hero ? '(max-width: 760px) 88vw, 560px' : '(max-width: 640px) 46vw, 280px',
	);
	if ( $hero ) {
		$attributes['fetchpriority'] = 'high';
	}
	return wp_get_attachment_image( $image_id, $hero ? 'medium_large' : 'woocommerce_thumbnail', false, $attributes );
}

function clz_home_v4_term_image( $term ) {
	$image_id = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
	return $image_id ? wp_get_attachment_image(
		$image_id,
		'thumbnail',
		false,
		array( 'alt' => $term->name, 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 640px) 28vw, 150px' )
	) : '';
}

function clz_home_v4_discount( $product ) {
	if ( ! $product || ! $product->is_on_sale() ) {
		return 0;
	}
	$regular = $product->is_type( 'variable' ) ? (float) $product->get_variation_regular_price( 'min', true ) : (float) $product->get_regular_price();
	$sale = $product->is_type( 'variable' ) ? (float) $product->get_variation_sale_price( 'min', true ) : (float) $product->get_sale_price();
	return $regular > 0 && $sale < $regular ? max( 1, (int) round( 100 - ( $sale / $regular * 100 ) ) ) : 0;
}

function clz_home_v4_product_card( $product ) {
	$discount = clz_home_v4_discount( $product );
	?>
	<article class="clz4-product">
		<a class="clz4-product-media" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
			<?php echo clz_home_v4_product_image( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $discount ) : ?><span>٪<?php echo esc_html( number_format_i18n( $discount ) ); ?></span><?php endif; ?>
		</a>
		<div class="clz4-product-copy">
			<h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
			<div><strong><?php echo wp_kses_post( $product->get_price_html() ?: 'تماس بگیرید' ); ?></strong><a href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="مشاهده محصول">←</a></div>
		</div>
	</article>
	<?php
}

$data = clz_home_v4_dataset();
$hero_products = clz_home_v4_products( empty( $data['hero'] ) ? array() : array( $data['hero'] ) );
$hero_product = $hero_products ? reset( $hero_products ) : false;
$products = clz_home_v4_products( $data['products'] ?? array() );
$sale_products = clz_home_v4_products( $data['sale'] ?? array() );
$categories = array_values( array_filter( array_map( 'get_term', $data['categories'] ?? array() ), static function ( $term ) { return $term instanceof WP_Term; } ) );
$posts = array_values( array_filter( array_map( 'get_post', $data['posts'] ?? array() ), static function ( $post ) { return $post instanceof WP_Post; } ) );
$shop_url = clz_home_v4_shop_url();
$blog_url = clz_home_v4_blog_url();
$support_url = apply_filters( 'clz_home_support_url', 'https://www.goftino.com/c/h6q2ir' );

$style_file = trailingslashit( get_stylesheet_directory() ) . 'assets/homepage-v4.css';
wp_enqueue_style( 'clz-home-v4', trailingslashit( get_stylesheet_directory_uri() ) . 'assets/homepage-v4.css', array(), file_exists( $style_file ) ? (string) filemtime( $style_file ) : BIJAN_CHILD_VERSION );

// The custom template does not render the old Elementor document or generic page UI.
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_dequeue_style( 'elementor-post-' . get_queried_object_id() );
		wp_dequeue_style( 'bijan-single' );
	},
	999
);

get_header();
?>

<main id="primary" class="site-main clz4" dir="rtl">
	<section class="clz4-hero" aria-labelledby="clz4-title">
		<div class="clz4-shell clz4-hero-grid">
			<div class="clz4-hero-copy">
				<span class="clz4-kicker"><i></i> فروشگاه تخصصی پیرسینگ و اکسسوری</span>
				<h1 id="clz4-title">جزئیات کوچک،<br><em>استایل کاملاً شخصی.</em></h1>
				<p>مدل مناسب را با مشخصات روشن، دسته‌بندی دقیق و راهنمایی واقعی پیدا کن؛ بدون حدس‌زدن و گشتن بین انتخاب‌های نامرتبط.</p>
				<div class="clz4-actions"><a class="clz4-button clz4-button-main" href="#clz4-products">دیدن انتخاب‌های تازه <span>←</span></a><a class="clz4-button clz4-button-ghost" href="#clz4-categories">انتخاب بر اساس دسته</a></div>
				<ul class="clz4-proof" aria-label="مزیت‌های خرید از کلوز">
					<li><b>01</b><span>مشخصات دقیق<small>قبل از تصمیم</small></span></li>
					<li><b>02</b><span>موجودی واقعی<small>بدون انتخاب سوخته</small></span></li>
					<li><b>03</b><span>راهنمایی انسانی<small>وقتی مطمئن نیستی</small></span></li>
				</ul>
			</div>
			<div class="clz4-hero-visual">
				<div class="clz4-orbit" aria-hidden="true"><span>CLOZE</span></div>
				<?php if ( $hero_product ) : ?>
					<a class="clz4-hero-card" href="<?php echo esc_url( $hero_product->get_permalink() ); ?>">
						<div class="clz4-hero-image"><?php echo clz_home_v4_product_image( $hero_product, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="clz4-hero-meta"><span>انتخاب تازه</span><strong><?php echo esc_html( $hero_product->get_name() ); ?></strong><small><?php echo wp_kses_post( $hero_product->get_price_html() ); ?></small></div>
					</a>
				<?php else : ?><div class="clz4-hero-card clz4-hero-empty"><strong>CLOZE</strong><span>انتخاب دقیق، استایل شخصی</span></div><?php endif; ?>
				<div class="clz4-float-note"><b>✦</b><span>هر انتخاب<br>یک امضای شخصی</span></div>
			</div>
		</div>
	</section>

	<section class="clz4-category-section clz4-lazy" id="clz4-categories" aria-labelledby="clz4-cat-title">
		<div class="clz4-shell">
			<header class="clz4-heading"><div><span>مسیر سریع‌تر</span><h2 id="clz4-cat-title">از کجا شروع می‌کنی؟</h2></div><p>مستقیم برو سراغ بخشی که دنبالش هستی.</p></header>
			<div class="clz4-categories">
				<?php foreach ( $categories as $term ) : $link = get_term_link( $term ); if ( is_wp_error( $link ) ) { continue; } ?>
					<a class="clz4-category" href="<?php echo esc_url( $link ); ?>"><span><?php echo clz_home_v4_term_image( $term ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><strong><?php echo esc_html( $term->name ); ?></strong><small><?php echo esc_html( number_format_i18n( $term->count ) ); ?> محصول</small></a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="clz4-section clz4-lazy" id="clz4-products" aria-labelledby="clz4-products-title">
		<div class="clz4-shell">
			<header class="clz4-heading clz4-heading-row"><div><span>تازه به ویترین رسیده</span><h2 id="clz4-products-title">انتخاب‌های جدید کلوز</h2></div><a href="<?php echo esc_url( $shop_url ); ?>">مشاهده همه محصولات ←</a></header>
			<div class="clz4-products"><?php if ( $products ) : foreach ( $products as $product ) { clz_home_v4_product_card( $product ); } else : ?><p class="clz4-empty">محصول تازه‌ای برای نمایش پیدا نشد.</p><?php endif; ?></div>
		</div>
	</section>

	<?php if ( $sale_products ) : ?>
		<section class="clz4-section clz4-sale-section clz4-lazy" aria-labelledby="clz4-sale-title">
			<div class="clz4-shell clz4-sale">
				<div class="clz4-sale-copy"><span>فرصت‌های واقعی</span><h2 id="clz4-sale-title">قیمت کمتر،<br>همان انتخاب دقیق.</h2><p>فقط مدل‌هایی که همین حالا موجود و تخفیف‌دارند.</p><a href="<?php echo esc_url( $shop_url ); ?>">دیدن همه تخفیف‌ها ←</a></div>
				<div class="clz4-sale-products"><?php foreach ( $sale_products as $product ) { clz_home_v4_product_card( $product ); } ?></div>
			</div>
		</section>
	<?php endif; ?>

	<section class="clz4-section clz4-service-section clz4-lazy" aria-labelledby="clz4-service-title">
		<div class="clz4-shell">
			<header class="clz4-heading"><div><span>قبل، حین و بعد از خرید</span><h2 id="clz4-service-title">کمتر حدس بزن؛ مطمئن‌تر انتخاب کن.</h2></div></header>
			<div class="clz4-services">
				<article><b>01</b><h3>اطلاعاتی که به تصمیم کمک می‌کند</h3><p>جنس، اندازه و ویژگی اصلی هر مدل همان‌جایی است که باید باشد.</p></article>
				<article><b>02</b><h3>دسته‌بندی بر اساس نیاز واقعی</h3><p>به‌جای یک ویترین شلوغ، سریع‌تر به انتخاب مرتبط می‌رسی.</p></article>
				<article><b>03</b><h3>پشتیبانی قبل از انتخاب اشتباه</h3><p>بین دو مدل ماندی؟ لینک‌ها را بفرست تا دقیق‌تر راهنمایی شوی.</p></article>
			</div>
			<div class="clz4-support"><div><span>هنوز مطمئن نیستی؟</span><h2>لینک مدل‌ها را بفرست؛ با هم مقایسه‌شان می‌کنیم.</h2></div><a class="clz4-button clz4-button-light" href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer">از کلوز بپرس ←</a></div>
		</div>
	</section>

	<?php if ( $posts ) : ?>
		<section class="clz4-section clz4-journal-section clz4-lazy" aria-labelledby="clz4-journal-title">
			<div class="clz4-shell">
				<header class="clz4-heading clz4-heading-row"><div><span>مجله کلوز</span><h2 id="clz4-journal-title">قبل از انتخاب، بهتر بدان.</h2></div><a href="<?php echo esc_url( $blog_url ); ?>">همه مطالب ←</a></header>
				<div class="clz4-journal">
					<?php foreach ( $posts as $index => $post_item ) : ?>
						<article class="clz4-article<?php echo 0 === $index ? ' clz4-article-featured' : ''; ?>">
							<a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>"><?php echo get_the_post_thumbnail( $post_item, 0 === $index ? 'medium_large' : 'medium', array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 700px) 92vw, 520px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
							<div><small><?php echo esc_html( get_the_date( '', $post_item ) ); ?></small><h3><a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>"><?php echo esc_html( get_the_title( $post_item ) ); ?></a></h3><p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_item ) ), 16, '…' ) ); ?></p></div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="clz4-section clz4-faq-section clz4-lazy" aria-labelledby="clz4-faq-title">
		<div class="clz4-shell clz4-faq-layout">
			<header><span>جواب‌های کوتاه</span><h2 id="clz4-faq-title">چیزهایی که بهتر است قبل از خرید بدانی.</h2><p>جواب را پیدا نکردی؟ مستقیم از پشتیبانی بپرس.</p></header>
			<div class="clz4-faqs">
				<details open><summary>جنس و اندازه هر محصول کجا نوشته شده؟ <i>＋</i></summary><p>داخل صفحه محصول، بخش مشخصات و توضیحات را ببین. اگر اطلاعات کافی نبود، لینک محصول را برای پشتیبانی بفرست.</p></details>
				<details><summary>بین دو سایز یا دو مدل مرددم؛ چه کار کنم؟ <i>＋</i></summary><p>لینک هر دو محصول و توضیح کوتاهی از نوع استفاده را بفرست تا مقایسه دقیق‌تری دریافت کنی.</p></details>
				<details><summary>برای پیگیری سفارش چه اطلاعاتی لازم است؟ <i>＋</i></summary><p>شماره سفارش، شماره موبایل خریدار و توضیح کوتاه موضوع برای شروع پیگیری کافی است.</p></details>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
