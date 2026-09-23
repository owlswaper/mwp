<?php
/**************************************************************************
***************************************************************************/
/* DON'T REMOVE THIS LINE 👇🏻 */
include( trailingslashit( get_stylesheet_directory() ) . 'ChildInit.php' );
/* DON'T REMOVE THIS LINE 👆🏻 */
/**************************************************************************
***************************************************************************/

// Product reviews and Q&A (kept in the child theme so parent updates are safe).
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/product-community.php';

// Stable 12-hour product shuffle for product category archives.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/category-product-shuffle.php';

// Link-free AJAX filtering and ordering on WooCommerce archives.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/archive-ajax-filters.php';

// One fast AJAX add-to-cart flow for loops, custom sections and product forms.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/ajax-cart.php';

// Streamlined, Iran-only classic WooCommerce checkout.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/checkout-customizations.php';

// Mobile header/menu behavior and the quick support action.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/mobile-header.php';

// Purpose-built, action-first contact page.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/contact-page.php';

// Clearer product attributes plus the fixed shipping and returns guide.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/product-tabs.php';

// Secure customer order tracking, fulfilment workflow and status SMS.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/order-tracking.php';

// One editorial author profile for blog posts and its Rank Math identity.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/blog-author.php';

// Keep the tiny header controller ready everywhere while delaying large,
// unrelated bundles. Commerce pages retain their existing critical scripts.
add_action( 'template_redirect', function() {
	$is_product = function_exists( 'is_product' ) && is_product();
	$is_category = function_exists( 'is_product_category' ) && is_product_category();
	if ( ! class_exists( '\\FlyingPress\\Config' ) ) {
		return;
	}

	$critical = [
		'clz-mobile-header-js',
	];
	if ( $is_product ) {
		$critical = array_merge( $critical, [
			'jquery-core-js',
			'cloz-ajax-cart-js',
			'bijan-swiper-js',
			'bijan-slider-js',
			'bijan-wc-single-js',
			'cloz-product-a11y-js',
			'cloz-product-lightbox-lazy-js',
			'cloz-related-hydrate',
		] );
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_queried_object_id() ) : false;
		if ( $product && $product->is_type( 'variable' ) ) {
			$critical = array_merge( $critical, [
				'underscore-js',
				'wp-util-js',
				'wc-add-to-cart-variation-js',
				'bijan-product-community-js',
			] );
		}
	} elseif ( $is_category ) {
		$critical = array_merge( $critical, [
			'jquery-core-js',
			'cloz-ajax-cart-js',
		] );
		$critical[] = 'cloz-archive-ajax-filters-js';
	}

	if ( $is_product || $is_category ) {
		\FlyingPress\Config::$config['js_delay_method'] = 'user-interaction';
	}
	\FlyingPress\Config::$config['js_delay_excludes'] = array_values( array_unique( array_merge(
		(array) ( \FlyingPress\Config::$config['js_delay_excludes'] ?? [] ),
		$critical
	) ) );
}, 1 );

function cloz_defer_product_details_start() {
	$GLOBALS['cloz_product_details_buffering'] = true;
	ob_start();
}

function cloz_defer_product_details_finish() {
	if ( empty( $GLOBALS['cloz_product_details_buffering'] ) ) {
		return;
	}
	$html = ob_get_clean();
	$GLOBALS['cloz_product_details_buffering'] = false;
	echo '<div id="cloz-details-placeholder" class="cloz-product-lazy" data-template="cloz-details-template" style="min-height:2200px" aria-hidden="true"></div>';
	echo '<template id="cloz-details-template">' . $html . '</template>';
}

add_action( 'wp', function() {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! wp_is_mobile() ) {
		return;
	}
	add_action( 'woocommerce_after_single_product_summary', 'cloz_defer_product_details_start', 9 );
	add_action( 'woocommerce_after_single_product_summary', 'cloz_defer_product_details_finish', PHP_INT_MAX );
}, 100 );

add_action( 'wp_footer', function() {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! wp_is_mobile() ) {
		return;
	}
	?>
	<script id="cloz-related-hydrate">
	(() => {
		const placeholders = [...document.querySelectorAll('.cloz-product-lazy')];
		if (!placeholders.length) return;
		const hydrate = placeholder => {
			const template = document.getElementById(placeholder.dataset.template);
			if (!template || !placeholder.isConnected) return;
			placeholder.replaceWith(template.content.cloneNode(true));
			template.remove();
			requestAnimationFrame(() => window.dispatchEvent(new Event('resize')));
		};
		const observer = new IntersectionObserver(entries => {
			entries.forEach(entry => {
				if (!entry.isIntersecting) return;
				observer.unobserve(entry.target);
				hydrate(entry.target);
			});
		}, { rootMargin: '500px 0px' });
		placeholders.forEach(placeholder => observer.observe(placeholder));
		document.addEventListener('click', event => {
			const link = event.target.closest('a[href^="#tab-"],a[href="#reviews"]');
			if (!link) return;
			const details = document.getElementById('cloz-details-placeholder');
			if (details) hydrate(details);
		}, true);
		const replayWhenReady = (selector, ready, acted) => {
			document.addEventListener('click', event => {
				const target = event.target.closest(selector);
				if (!target) return;
				if (target.dataset.clozReplayNow) {
					delete target.dataset.clozReplayNow;
					return;
				}
				if (ready(target) || target.dataset.clozReplayPending) return;
				event.preventDefault();
				target.dataset.clozReplayPending = 'true';
				const started = Date.now();
				const timer = setInterval(() => {
					const isReady = ready(target);
					if (!isReady && Date.now() - started < 10000) return;
					clearInterval(timer);
					delete target.dataset.clozReplayPending;
					if (isReady && !acted(target)) {
						target.dataset.clozReplayNow = 'true';
						target.click();
					}
				}, 50);
			}, true);
		};
		document.addEventListener('click', event => {
			const target = event.target.closest('.product-thumb-slider a.woocommerce-product-gallery__image');
			if (!target) return;
			event.preventDefault();
			const imageId = target.dataset.id;
			const started = Date.now();
			const timer = setInterval(() => {
				const main = document.querySelector('.product-main-slider');
				if (!main?.swiper && Date.now() - started < 10000) return;
				clearInterval(timer);
				if (!main?.swiper) return;
				const ids = [...new Set([...main.querySelectorAll('.swiper-wrapper > a[data-id]')].map(slide => slide.dataset.id))];
				const index = ids.indexOf(imageId);
				if (index >= 0) main.swiper.slideToLoop(index);
			}, 50);
		}, true);
		replayWhenReady(
			'.product-main-slider a.woocommerce-product-gallery__image',
			target => target.closest('.product-main-slider')?.dataset.clozLightboxReady === 'true',
			() => !!document.querySelector('.lg-outer,.lg-container')
		);
		document.addEventListener('click', event => {
			const target = event.target.closest('a[href*="/my-account"]');
			if (!target) return;
			if (target.dataset.clozReplayNow) {
				delete target.dataset.clozReplayNow;
				return;
			}
			if (getComputedStyle(document.getElementById('auth-modal')).display !== 'none') return;
			event.preventDefault();
			if (target.dataset.clozReplayPending) return;
			target.dataset.clozReplayPending = 'true';
			const started = Date.now();
			const timer = setInterval(() => {
				const ready = document.readyState === 'complete' && window.bijanLogin && window.jQuery?._data(target, 'events')?.click;
				if (!ready && Date.now() - started < 10000) return;
				clearInterval(timer);
				delete target.dataset.clozReplayPending;
				if (!ready) return;
				setTimeout(() => {
					target.dataset.clozReplayNow = 'true';
					target.click();
				}, 250);
			}, 50);
		}, true);
		replayWhenReady(
			'.show-compare-popup',
			() => {
				const events = window.jQuery?._data(document, 'events')?.click || [];
				return [...events].some(handler => handler.selector === '.show-compare-popup');
			},
			() => getComputedStyle(document.getElementById('compare-popup')).display !== 'none'
		);
		replayWhenReady(
			'.bijan-show-price-chart',
			target => target.dataset.clozPriceReady === 'true',
			() => getComputedStyle(document.getElementById('price-history-popup')).display !== 'none'
		);
		document.addEventListener('click', event => {
			const target = event.target.closest('.single-product form.cart .quantity button');
			if (!target) return;
			event.preventDefault();
			event.stopImmediatePropagation();
			const input = target.parentElement.querySelector('input.qty');
			if (!input) return;
			const step = Number.parseFloat(input.step) || 1;
			const min = Number.parseFloat(input.min);
			const max = Number.parseFloat(input.max);
			const current = Number.parseFloat(input.value) || 0;
			let next = current + (target.classList.contains('plus-quantity') ? step : -step);
			if (Number.isFinite(min)) next = Math.max(min, next);
			if (Number.isFinite(max)) next = Math.min(max, next);
			input.value = String(next);
			input.dispatchEvent(new Event('change', { bubbles: true }));
		}, true);
	})();
	</script>
	<?php
}, 1 );



add_action( 'wp_enqueue_scripts', function() {
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_add_inline_style( 'bijan-wc-product', '.single-product .woocommerce-product-gallery{opacity:1!important}' );
		$file = get_stylesheet_directory() . '/assets/product-a11y.js';
		wp_enqueue_script( 'cloz-product-a11y', get_stylesheet_directory_uri() . '/assets/product-a11y.js', [ 'bijan-wc-single' ], filemtime( $file ), true );

		// Build the hidden lightbox only when the customer opens the gallery.
		wp_add_inline_script( 'bijan-wc-single', 'window.clozLightboxEnabled=bijanWCSingle.lightbox;bijanWCSingle.lightbox="0";', 'before' );
		$lightbox_file = get_stylesheet_directory() . '/assets/product-lightbox-lazy.js';
		wp_enqueue_script( 'cloz-product-lightbox-lazy', get_stylesheet_directory_uri() . '/assets/product-lightbox-lazy.js', [ 'bijan-wc-single' ], filemtime( $lightbox_file ), true );
		wp_localize_script( 'cloz-product-lightbox-lazy', 'clozLightboxAssets', [
			'styles' => [
				get_template_directory_uri() . '/assets/libs/lightgallery/css/lightgallery-bundle.min.css',
				get_template_directory_uri() . '/assets/libs/lightgallery/css/lg-zoom.min.css',
				get_template_directory_uri() . '/assets/libs/lightgallery/css/lg-thumbnail.min.css',
				get_template_directory_uri() . '/assets/libs/lightgallery/css/lg-fullscreen.min.css',
				get_template_directory_uri() . '/assets/libs/lightgallery/css/lg-rotate.css',
			],
			'scripts' => [
				get_template_directory_uri() . '/assets/libs/lightgallery/lightgallery.umd.min.js',
				get_template_directory_uri() . '/assets/libs/lightgallery/plugins/zoom/lg-zoom.min.js',
				get_template_directory_uri() . '/assets/libs/lightgallery/plugins/video/lg-video.min.js',
				get_template_directory_uri() . '/assets/libs/lightgallery/plugins/thumbnail/lg-thumbnail.min.js',
				get_template_directory_uri() . '/assets/libs/lightgallery/plugins/fullscreen/lg-fullscreen.min.js',
				get_template_directory_uri() . '/assets/libs/lightgallery/plugins/rotate/lg-rotate.min.js',
			],
		] );

		foreach ( [ 'bijan-lightgallery', 'bijan-lightgallery-video', 'bijan-lightgallery-zoom', 'bijan-lightgallery-thumbnail', 'bijan-lightgallery-fullscreen', 'bijan-lightgallery-rotate' ] as $handle ) {
			wp_dequeue_script( $handle );
			wp_dequeue_style( $handle );
		}
	}
}, 99 );

// The first full-size gallery image is the product-page LCP element.
add_filter( 'wp_get_attachment_image_attributes', function( $attributes, $attachment ) {
	$is_product_lcp =
		function_exists( 'is_product' ) &&
		is_product() &&
		(int) $attachment->ID === (int) get_post_thumbnail_id() &&
		str_contains( $attributes['class'] ?? '', 'wp-post-image' );
	$is_post_lcp =
		is_singular( 'post' ) &&
		(int) $attachment->ID === (int) get_post_thumbnail_id() &&
		str_contains( $attributes['class'] ?? '', 'wp-post-image' );
	$is_site_logo = str_contains( $attributes['class'] ?? '', 'attachment-65x80' );

	if ( $is_product_lcp || $is_post_lcp || $is_site_logo ) {
		$attributes['loading'] = 'eager';
		$attributes['fetchpriority'] = 'high';
		if ( $is_product_lcp && wp_is_mobile() ) {
			$attributes['sizes'] = '(max-width: 600px) calc(100vw - 74px), 600px';
		} elseif ( $is_post_lcp && wp_is_mobile() ) {
			$attributes['sizes'] = '(max-width: 600px) calc(100vw - 32px), 1448px';
		}
	}
	return $attributes;
}, 10, 2 );

// Replace the original-image preload with the AVIF srcset generated by
// CompressX. This lets the preload scanner fetch the actual LCP resource.
add_filter( 'flying_press_optimization:after', function( $html ) {
	if ( ( ! function_exists( 'is_product' ) || ! is_product() ) && ! is_singular( 'post' ) ) {
		return $html;
	}

	$html = preg_replace( '/<link\b(?=[^>]*\brel=["\']preload["\'])(?=[^>]*\bas=["\']image["\'])[^>]*>\s*/i', '', $html );

	if (
		preg_match( '/<picture\b[^>]*\bclass=["\'][^"\']*\bwp-post-image\b[^"\']*["\'][^>]*>.*?<\/picture>/is', $html, $picture ) &&
		preg_match( '/<source\b[^>]*\btype=["\']image\/avif["\'][^>]*\bsrcset=["\']([^"\']+)["\']/i', $picture[0], $srcset ) &&
		preg_match( '/<img\b[^>]*\bsizes=["\']([^"\']+)["\']/i', $picture[0], $sizes )
	) {
		$href = strtok( trim( explode( ',', $srcset[1] )[0] ), ' ' );
		$preload = sprintf(
			'<link rel="preload" as="image" type="image/avif" href="%s" imagesrcset="%s" imagesizes="%s" fetchpriority="high">',
			esc_url( $href ),
			esc_attr( $srcset[1] ),
			esc_attr( $sizes[1] )
		);
		$html = preg_replace( '/<\/head>/i', $preload . "\n</head>", $html, 1 );
	}

	foreach ( [
		'plus-quantity'  => 'افزایش تعداد محصول',
		'minus-quantity' => 'کاهش تعداد محصول',
	] as $class => $label ) {
		$pattern = '/<button\\b(?=[^>]*\\bclass=["\'][^"\']*\\b' . preg_quote( $class, '/' ) . '\\b[^"\']*["\'])[^>]*>/i';
		$html = preg_replace_callback( $pattern, function( $button ) use ( $label ) {
			if ( preg_match( '/\\baria-label=/i', $button[0] ) ) {
				return $button[0];
			}
			return preg_replace( '/^<button\\b/i', '<button aria-label="' . esc_attr( $label ) . '"', $button[0], 1 );
		}, $html );
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$html = preg_replace_callback(
			'#<script\b(?=[^>]*\bsrc=["\x27]https://(?:cdn\.visibilitykit\.ai|www\.zarinpal\.com)/)[^>]*>#i',
			static function ( $tag ) {
				return preg_replace( '/^<script\b/i', '<script async', preg_replace( '/\sdefer(?:=["\x27]defer["\x27])?/i', '', $tag[0] ), 1 );
			},
			$html
		);
		$html = str_replace(
			[
				',{event:"mousemove",target:document}', ',{event:"touchstart",target:document}', ',{event:"touchmove",target:document}', ',{event:"scroll",target:window}',
				'setTimeout(y,1e4)',
			],
			[ '', '', '', '', 'setTimeout(y,3e4)' ],
			$html
		);
		$html = preg_replace(
			'#<script\b(?![^>]*\b(?:async|defer)\b)(?=[^>]*\bid=["\x27](?:clz-mobile-header|bijan-swiper|bijan-slider|bijan-wc-single|bijan-product-community|cloz-product-a11y|cloz-product-lightbox-lazy|underscore|wp-util)-js["\x27])#i',
			'<script defer',
			$html
		);
	}

	return $html;
} );

add_filter( 'style_loader_tag', function( $html, $handle ) {
	$product_priority =
		function_exists( 'is_product' ) &&
		is_product() &&
		wp_is_mobile() &&
		in_array( $handle, [ 'bijan-bootstrap-rtl', 'bijan-wc-product' ], true );
	$post_priority =
		is_singular( 'post' ) &&
		wp_is_mobile() &&
		in_array( $handle, [ 'bijan-bootstrap', 'bijan-bootstrap-rtl', 'bijan-comments' ], true );
	if ( $product_priority || $post_priority ) {
		$html = preg_replace( '/<link\\b/i', '<link fetchpriority="high"', $html, 1 );
	}
	if (
		function_exists( 'is_product' ) &&
		is_product() &&
		in_array( $handle, [ 'redux-elusive-icon', 'font-awesome-4-shims' ], true )
	) {
		return '';
	}
	return $html;
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function() {
	if ( function_exists( 'is_product' ) && is_product() ) {
	foreach ( [ 'contact-form-7', 'contact-form-7-rtl', 'newsletter' ] as $handle ) {
			wp_dequeue_style( $handle );
		}
		// The RTL grid is a complete build; loading the LTR build duplicates it.
		foreach ( [ 'bijan-bootstrap', 'bijan-single', 'bijan-comments', 'redux-elusive-icon', 'font-awesome-4-shims', 'rank-math-related-posts-style' ] as $handle ) {
			wp_dequeue_style( $handle );
		}
		foreach ( [ 'swv', 'contact-form-7', 'newsletter' ] as $handle ) {
			wp_dequeue_script( $handle );
		}
	}
}, 100 );

add_action( 'wp_enqueue_scripts', function() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$scripts = wp_scripts();
	$price_history = $scripts->registered['bijan-wc-single-price-history'] ?? null;
	$chart = $scripts->registered['bijan-chartjs'] ?? null;
	if ( ! $price_history || ! $chart ) {
		return;
	}

	$settings = $scripts->get_data( 'bijan-wc-single-price-history', 'data' );
	wp_dequeue_script( 'bijan-wc-single-price-history' );
	wp_dequeue_script( 'bijan-chartjs' );

	$file = get_stylesheet_directory() . '/assets/product-price-history.js';
	wp_enqueue_script( 'cloz-product-price-history', get_stylesheet_directory_uri() . '/assets/product-price-history.js', [ 'jquery' ], filemtime( $file ), true );
	if ( $settings ) {
		wp_add_inline_script( 'cloz-product-price-history', $settings, 'before' );
	}
	wp_localize_script( 'cloz-product-price-history', 'clozPriceHistoryAssets', [
		'chart' => $chart->src,
		'history' => $price_history->src,
	] );
}, 101 );

add_filter( 'woocommerce_single_product_image_thumbnail_html', function( $html, $attachment_id ) {
	if ( false !== strpos( $html, 'woocommerce-product-gallery__image' ) && $thumbnail = wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) ) {
		return preg_replace( '/^<a\\b/', '<a data-thumb="' . esc_url( $thumbnail ) . '"', $html, 1 );
	}
	return $html;
}, 10, 2 );

/**************************************************************************
✅ START EDIT FROM HERE 👇🏻
HAPPY CODING 😊
***************************************************************************/








/**
 * ACF category content belongs only to the canonical first archive page.
 * Covers both WordPress /page/2/ pagination and WooCommerce product-page URLs.
 */
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

// نمایش معرفی دسته و زیردسته‌ها - بالای لیست محصولات
add_action('woocommerce_before_shop_loop', 'display_category_intro_and_subcats', 15);
function display_category_intro_and_subcats() {
	// محتوای معرفی ACF فقط در صفحهٔ اصلی آرشیو دسته نمایش داده شود.
	if ( ! cloz_is_primary_product_category_page() ) return;
    
    $term = get_queried_object();
    $term_id = $term->term_id;
    
    // خواندن معرفی دسته
    $category_intro = get_field('category_intro', 'product_cat_' . $term_id);
    
    // خواندن زیردسته‌ها به صورت flat
    $subcats = [];
    for ($i = 0; $i < 50; $i++) {
        $image_id = get_term_meta($term_id, "sub_categories_{$i}_subcat_image", true);
        $title = get_term_meta($term_id, "sub_categories_{$i}_subcat_title", true);
        $link = get_term_meta($term_id, "sub_categories_{$i}_subcat_link", true);
        
        if (!$image_id && !$title && !$link) break;
        
        $subcats[] = [
            'image_id' => $image_id,
            'title' => $title,
            'link' => $link
        ];
    }
    
    // اگه هیچکدوم نبود، چیزی نمایش نده
    if (empty($category_intro) && empty($subcats)) return;
    
    ?>
    <div class="cloz-category-intro-section">
        <?php if (!empty($category_intro)): ?>
			<div class="cloz-category-intro-text">
				<div class="cloz-category-intro-content"><?php echo wpautop( wp_kses_post( $category_intro ) ); ?></div>
			</div>
        <?php endif; ?>
        
        <?php if (!empty($subcats)): ?>
            <div class="cloz-subcategories-grid">
                <?php foreach ($subcats as $subcat): ?>
                    <?php 
                    $image_url = '';
                    if (!empty($subcat['image_id'])) {
                        $image_url = wp_get_attachment_image_url($subcat['image_id'], 'medium');
                    }
                    
                    $link = !empty($subcat['link']) ? esc_url($subcat['link']) : '#';
                    $title = !empty($subcat['title']) ? esc_html($subcat['title']) : 'بدون عنوان';
                    ?>
                    
                    <a href="<?php echo $link; ?>" class="cloz-subcat-card">
                        <div class="cloz-subcat-image-wrapper">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" 
                                     alt="<?php echo $title; ?>" 
                                     class="cloz-subcat-image">
                            <?php endif; ?>
                        </div>
                        <div class="cloz-subcat-title"><?php echo $title; ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}























// توضیحات طولانی دسته‌بندی
add_action('woocommerce_after_shop_loop', 'cloz_category_long_description', 20);

function cloz_category_long_description() {
    if ( ! cloz_is_primary_product_category_page() ) return;

    $term = get_queried_object();
    $content = get_field('category_long_description', 'product_cat_' . $term->term_id);

    if (!$content) return;

    // بستن divهای احتمالی
    echo '</div></div></div>';

    echo '<section class="cloz-long-desc-section">';

    echo '<style>
    .cloz-long-desc-section {
        width: 100% !important;
        max-width: 100% !important;
        margin: 60px 0 !important;
        padding: 0 20px !important;
        clear: both !important;
        float: none !important;
        position: relative !important;
        right: auto !important;
        left: auto !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }
    
    .cloz-desc-container {
        max-width: 1200px !important;
        width: 100% !important;
        margin: 0 auto !important;
        background: #fff !important;
        padding: 50px 60px !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
        line-height: 1.8 !important;
        font-size: 17px !important;
        color: #444 !important;
        clear: both !important;
        float: none !important;
        position: relative !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }
    
    .cloz-desc-container * {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .cloz-desc-container h2 {
        font-size: 32px !important;
        margin: 0 0 25px 0 !important;
        line-height: 1.3 !important;
        font-weight: 700 !important;
        color: #222 !important;
        border-bottom: 3px solid #7dd3fc !important;
        padding-bottom: 15px !important;
    }
    
    .cloz-desc-container h3 {
        font-size: 26px !important;
        margin: 40px 0 20px 0 !important;
        line-height: 1.4 !important;
        font-weight: 700 !important;
        color: #333 !important;
    }
    
    .cloz-desc-container h4 {
        font-size: 22px !important;
        margin: 30px 0 15px 0 !important;
        line-height: 1.4 !important;
        font-weight: 600 !important;
        color: #444 !important;
    }
    
    .cloz-desc-container p {
        margin-bottom: 20px !important;
        text-align: justify !important;
    }
    
    .cloz-desc-container ul,
    .cloz-desc-container ol {
        margin: 25px 0 !important;
        padding-right: 30px !important;
    }
    
    .cloz-desc-container li {
        margin-bottom: 12px !important;
        line-height: 1.7 !important;
    }
    
    .cloz-desc-container strong {
        color: #222 !important;
        font-weight: 600 !important;
    }
    
    .cloz-desc-container a {
        color: #0284c7 !important;
        text-decoration: none !important;
        border-bottom: 1px solid transparent !important;
        transition: all 0.3s ease !important;
    }
    
    .cloz-desc-container a:hover {
        border-bottom-color: #0284c7 !important;
    }
    
    @media (max-width: 1024px) {
        .cloz-desc-container {
            padding: 40px 40px !important;
        }
    }
    
    @media (max-width: 768px) {
        .cloz-long-desc-section {
            margin: 30px 0 !important;
            padding: 0 10px !important;
            overflow-x: hidden !important;
        }
        
        .cloz-desc-container {
            padding: 20px 15px !important;
            font-size: 15px !important;
            line-height: 1.6 !important;
            border-radius: 4px !important;
        }
        
        .cloz-desc-container h2 {
            font-size: 20px !important;
            margin-bottom: 15px !important;
            line-height: 1.2 !important;
            padding-bottom: 10px !important;
            border-bottom-width: 2px !important;
        }
        
        .cloz-desc-container h3 {
            font-size: 18px !important;
            margin: 20px 0 12px 0 !important;
            line-height: 1.2 !important;
        }
        
        .cloz-desc-container h4 {
            font-size: 16px !important;
            margin: 18px 0 10px 0 !important;
            line-height: 1.2 !important;
        }
        
        .cloz-desc-container p {
            margin-bottom: 12px !important;
        }
        
        .cloz-desc-container ul,
        .cloz-desc-container ol {
            margin: 15px 0 !important;
            padding-right: 20px !important;
        }
        
        .cloz-desc-container li {
            margin-bottom: 8px !important;
            line-height: 1.5 !important;
        }
    }
    
    @media (max-width: 480px) {
        .cloz-long-desc-section {
            padding: 0 5px !important;
        }
        
        .cloz-desc-container {
            padding: 15px 12px !important;
            font-size: 14px !important;
        }
        
        .cloz-desc-container h2 {
            font-size: 18px !important;
        }
        
        .cloz-desc-container h3 {
            font-size: 16px !important;
        }
        
        .cloz-desc-container h4 {
            font-size: 15px !important;
        }
    }
    </style>';

    echo '<div class="cloz-desc-container">';
    echo apply_filters('the_content', $content);
    echo '</div>';

    echo '</section>';

    // بازکردن divها
    echo '<div><div><div>';
}


// سوالات متداول (زیر توضیحات)
add_action('woocommerce_after_shop_loop', 'display_category_faq_manual', 25);

function display_category_faq_manual() {
    if ( ! cloz_is_primary_product_category_page() ) return;
    
    $term = get_queried_object();
    $term_id = $term->term_id;
    
    global $wpdb;
    $faq_count = get_term_meta($term_id, 'faq_list', true);
    
    if (!$faq_count || $faq_count < 1) return;
    
    $faqs = [];
    for ($i = 0; $i < $faq_count; $i++) {
        $question = get_term_meta($term_id, "faq_list_{$i}_faq_question", true);
        $answer = get_term_meta($term_id, "faq_list_{$i}_faq_answer", true);
        
        if ($question && $answer) {
            $faqs[] = [
                'question' => $question,
                'answer' => $answer
            ];
        }
    }
    
    if (empty($faqs)) return;
    
    // بستن divها
    echo '</div></div></div>';
    
    ?>
    <section class="category-faq-section">
        <div class="faq-container">
            <h2 class="faq-title">سوالات متداول</h2>
            <div class="faq-accordion">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <span><?php echo esc_html($faq['question']); ?></span>
                            <svg class="faq-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <?php echo wpautop(wp_kses_post($faq['answer'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
        .category-faq-section {
            width: 100% !important;
            max-width: 100% !important;
            margin: 40px 0 60px 0 !important;
            padding: 0 20px !important;
            clear: both !important;
            float: none !important;
            position: relative !important;
            right: auto !important;
            left: auto !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }
        
        .faq-container {
            max-width: 1200px !important;
            width: 100% !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
        }
        
        .faq-title {
            font-size: 32px !important;
            font-weight: 700 !important;
            color: #222 !important;
            margin-bottom: 30px !important;
            text-align: right !important;
            border-bottom: 3px solid #7dd3fc !important;
            padding-bottom: 15px !important;
        }
        
        .faq-accordion {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            box-sizing: border-box !important;
        }
        
        .faq-item {
            background: #ffffff !important;
            border: 1px solid #e0f2fe !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            transition: all 0.3s ease !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .faq-item:hover {
            border-color: #7dd3fc !important;
            box-shadow: 0 4px 12px rgba(125, 211, 252, 0.15) !important;
        }
        
        .faq-question {
            width: 100% !important;
            padding: 20px 24px !important;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            border: none !important;
            text-align: right !important;
            cursor: pointer !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 16px !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            color: #0c4a6e !important;
            transition: all 0.3s ease !important;
            box-sizing: border-box !important;
        }
        
        .faq-question:hover {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%) !important;
        }
        
        .faq-question span {
            flex: 1 !important;
            text-align: right !important;
        }
        
        .faq-icon {
            flex-shrink: 0 !important;
            transition: transform 0.3s ease !important;
            color: #0284c7 !important;
        }
        
        .faq-item.active .faq-icon {
            transform: rotate(180deg) !important;
        }
        
        .faq-answer {
            max-height: 0 !important;
            overflow: hidden !important;
            transition: max-height 0.4s ease !important;
        }
        
        .faq-answer-content {
            padding: 0 24px 20px 24px !important;
            color: #334155 !important;
            font-size: 16px !important;
            line-height: 1.7 !important;
        }
        
        .faq-item.active .faq-answer {
            max-height: 1000px !important;
        }
        
        @media (max-width: 768px) {
            .category-faq-section {
                padding: 0 10px !important;
                margin: 30px 0 40px 0 !important;
                overflow-x: hidden !important;
            }
            
            .faq-title {
                font-size: 20px !important;
                margin-bottom: 20px !important;
                padding-bottom: 10px !important;
                border-bottom-width: 2px !important;
            }
            
            .faq-accordion {
                gap: 10px !important;
            }
            
            .faq-question {
                padding: 16px 18px !important;
                font-size: 16px !important;
            }
            
            .faq-answer-content {
                padding: 0 18px 16px 18px !important;
                font-size: 15px !important;
            }
        }
        
        @media (max-width: 480px) {
            .category-faq-section {
                padding: 0 5px !important;
            }
            
            .faq-title {
                font-size: 18px !important;
            }
            
            .faq-question {
                padding: 14px 15px !important;
                font-size: 15px !important;
            }
            
            .faq-answer-content {
                padding: 0 15px 14px 15px !important;
                font-size: 14px !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', function() {
                    const isActive = item.classList.contains('active');
                    
                    faqItems.forEach(i => {
                        i.classList.remove('active');
                        i.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
                    });
                    
                    if (!isActive) {
                        item.classList.add('active');
                        question.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        });
    </script>
    <?php
    
    // بازکردن divها
    echo '<div><div><div>';
}



























add_action('woocommerce_after_shop_loop', 'display_category_related_posts', 30);
function display_category_related_posts() {
    if ( ! cloz_is_primary_product_category_page() ) return;
    
    $term = get_queried_object();
    $term_id = $term->term_id;
    
    $related_post_ids = get_field('related_posts', 'product_cat_' . $term_id);
    
    if (empty($related_post_ids) || !is_array($related_post_ids)) return;
    
    echo '<div class="cloz-related-posts-wrapper">';
    echo '<h2 class="cloz-related-posts-title">مطالب مرتبط</h2>';
    echo '<div class="cloz-related-posts-grid">';
    
    foreach ($related_post_ids as $post_id) {
        $post = get_post($post_id);
        if (!$post) continue;
        
        $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
        
        $excerpt = get_post_meta($post_id, 'rank_math_description', true);
        if (empty($excerpt)) {
            $excerpt = wp_trim_words($post->post_content, 20, '...');
        }
        
        $date = get_the_date('j F Y', $post_id);
        $date_iso = get_the_date(DATE_W3C, $post_id);
        $permalink = get_permalink($post_id);
        
        echo '<article class="cloz-related-post-card">';
        if ($thumbnail) {
            echo '<a href="' . esc_url($permalink) . '" class="cloz-post-thumbnail">';
            echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($post->post_title) . '">';
            echo '</a>';
        }
        echo '<div class="cloz-post-content">';
        echo '<h3 class="cloz-post-title"><a href="' . esc_url($permalink) . '">' . esc_html($post->post_title) . '</a></h3>';
        echo '<p class="cloz-post-excerpt">' . esc_html($excerpt) . '</p>';
        echo '<div class="cloz-post-meta">';
        echo '<time class="cloz-post-date" datetime="' . esc_attr($date_iso) . '">📅 ' . esc_html($date) . '</time>';
        echo '<a href="' . esc_url($permalink) . '" class="cloz-post-readmore" aria-label="' . esc_attr('مشاهده مطلب: ' . $post->post_title) . '">مشاهده مطلب</a>';
        echo '</div>';
        echo '</div>';
        echo '</article>';
    }
    
    echo '</div>';
    echo '</div>';
}

add_action('wp_head', 'cloz_related_posts_styles');
function cloz_related_posts_styles() {
    if ( ! cloz_is_primary_product_category_page() ) return;
    ?>
    <style>
    .cloz-related-posts-wrapper {
        max-width: 1200px !important;
        margin: 50px auto !important;
        padding: 0 20px !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }
    .cloz-related-posts-title {
        font-size: 32px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin-bottom: 35px !important;
        text-align: right !important;
        padding-bottom: 20px !important;
        border-bottom: 4px solid transparent !important;
        background: linear-gradient(to left, #0ea5e9, #06b6d4) !important;
        background-clip: text !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        position: relative !important;
    }
    .cloz-related-posts-title::after {
        content: '' !important;
        position: absolute !important;
        bottom: 0 !important;
        right: 0 !important;
        width: 120px !important;
        height: 4px !important;
        background: linear-gradient(to left, #0ea5e9, #06b6d4) !important;
        border-radius: 2px !important;
    }
    .cloz-related-posts-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
        gap: 30px !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .cloz-related-post-card {
        background: #ffffff !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: flex !important;
        flex-direction: column !important;
        border: 1px solid #f1f5f9 !important;
    }
    .cloz-related-post-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 40px rgba(14,165,233,0.2) !important;
        border-color: #e0f2fe !important;
    }
    .cloz-post-thumbnail {
        display: block !important;
        width: 100% !important;
        height: 220px !important;
        overflow: hidden !important;
        position: relative !important;
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%) !important;
    }
    .cloz-post-thumbnail::after {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.1) 100%) !important;
        opacity: 0 !important;
        transition: opacity 0.3s ease !important;
    }
    .cloz-related-post-card:hover .cloz-post-thumbnail::after {
        opacity: 1 !important;
    }
    .cloz-post-thumbnail img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .cloz-related-post-card:hover .cloz-post-thumbnail img {
        transform: scale(1.08) !important;
    }
    .cloz-post-content {
        padding: 25px !important;
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        background: #ffffff !important;
    }
    .cloz-post-title {
        font-size: 20px !important;
        font-weight: 700 !important;
        margin: 0 0 15px 0 !important;
        line-height: 1.5 !important;
        text-align: right !important;
    }
    .cloz-post-title a {
        color: #0f172a !important;
        text-decoration: none !important;
        transition: color 0.3s ease !important;
        display: block !important;
    }
    .cloz-post-title a:hover {
        color: #0ea5e9 !important;
    }
    .cloz-post-excerpt {
        color: #64748b !important;
        font-size: 15px !important;
        line-height: 1.7 !important;
        margin: 0 0 20px 0 !important;
        flex: 1 !important;
        text-align: right !important;
    }
    .cloz-post-meta {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding-top: 20px !important;
        border-top: 1px solid #f1f5f9 !important;
        direction: rtl !important;
    }
    .cloz-post-date {
        color: #64748b !important;
        font-size: 14px !important;
        font-weight: 500 !important;
    }
    .cloz-post-readmore {
        color: #ffffff !important;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%) !important;
        padding: 8px 20px !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 8px rgba(14,165,233,0.3) !important;
    }
    .cloz-post-readmore:hover {
        background: linear-gradient(135deg, #0284c7 0%, #0891b2 100%) !important;
        box-shadow: 0 4px 12px rgba(14,165,233,0.4) !important;
        transform: translateY(-2px) !important;
    }
    @media (max-width: 768px) {
        .cloz-related-posts-wrapper {
            margin: 30px auto !important;
        }
        .cloz-related-posts-title {
            font-size: 26px !important;
        }
        .cloz-related-posts-grid {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
        .cloz-post-thumbnail {
            height: 200px !important;
        }
    }
    </style>
    <?php
}

// Shared catalog card geometry, also loaded on single-product related lists.
add_action( 'wp_enqueue_scripts', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/catalog-ui.css';
	wp_enqueue_style( 'cloz-catalog-ui', get_stylesheet_directory_uri() . '/assets/catalog-ui.css', [ 'bijan-child-style' ], filemtime( $path ) );
}, 60 );

// Resolve these child overrides even when WooCommerce cached a parent path
// before the child template files were introduced.
add_filter( 'wc_get_template', function ( $template, $template_name ) {
	$catalog_templates = [ 'loop/orderby.php', 'loop/products-style-1.php', 'single-product/related.php' ];
	if ( in_array( $template_name, $catalog_templates, true ) ) {
		$child_template = get_stylesheet_directory() . '/woocommerce/' . $template_name;
		if ( is_readable( $child_template ) ) {
			return $child_template;
		}
	}
	return $template;
}, 20, 2 );

// Accessibility corrections for single blog posts.
add_action( 'wp_head', function() {
	if ( ! is_singular( 'post' ) ) return;
	$fonts = get_template_directory_uri() . '/assets/fonts/';
	echo '<link rel="preload" href="' . esc_url( $fonts . 'iranyekanxfanum-regular.woff2' ) . '" as="font" type="font/woff2" crossorigin>';
	echo '<link rel="preload" href="' . esc_url( $fonts . 'iranyekanxfanum-black.woff2' ) . '" as="font" type="font/woff2" crossorigin>';
}, 1 );

add_action( 'wp_head', function() {
	if ( ! is_singular( 'post' ) ) return;
	?>
	<style id="cloz-post-a11y">
	.single-post #bijan-breadcrumbs a,
	.single-post #post-categories a,
	.single-post .required-field-message,
	.single-post .bijan_comment_star-title,
	.single-post #commentform label { color:#50525b !important; }
	.single-post #commentform input:not([type="submit"]),
	.single-post #commentform textarea { color:#30323a !important; }
	.single-post #commentform input::placeholder,
	.single-post #commentform textarea::placeholder { color:#555861 !important; opacity:1; }
	.single-post #commentform #submit { background:#087b79 !important; color:#fff !important; }
	.single-post .postTitle { color:#08725f !important; }
	.single-post .list-posts .post-texts { background:#484a50 !important; }
	.single-post #footer-more-info-subtitle { color:#b8bac1 !important; }
	</style>
	<?php
}, 2 );

// CompressX converts images after FlyingPress has optimized the page. This
// outer buffer therefore runs after that conversion, but before FlyingPress
// writes its cache, preserving the top image's reserved space and priority.
add_action( 'template_redirect', function() {
	if ( ! is_singular( 'post' ) ) return;
	ob_start( function( $html ) {
		return preg_replace_callback(
			'/(<figure\\b[^>]*\\bclass=["\'][^"\']*\\bpost-thumbnail\\b[^"\']*["\'][^>]*>.*?<picture\\b)([^>]*)(>.*?<img\\b)([^>]*)(>.*?<\\/picture>)/is',
			function( $match ) {
				$picture_attributes = preg_replace( '/\\sloading=["\']lazy["\']/i', '', $match[2] );
				$image_attributes   = preg_replace( '/\\sloading=["\']lazy["\']/i', ' loading="eager"', $match[4] );
				$image_attributes   = preg_replace( '/\\sfetchpriority=["\'](?:low|auto)["\']/i', ' fetchpriority="high"', $image_attributes );
				if ( ! preg_match( '/\\sfetchpriority=/i', $image_attributes ) ) $match[3] = preg_replace( '/(<img\\b)/i', '$1 fetchpriority="high"', $match[3], 1 );
				return $match[1] . $picture_attributes . $match[3] . $image_attributes . $match[5];
			},
			$html,
			1
		);
	} );
}, 999 );

add_filter( 'flying_press_optimization:after', function( $html ) {
	if ( ! is_singular( 'post' ) ) return $html;
	$html = preg_replace(
		'/(<figure\\b[^>]*\\bclass=["\'][^"\']*\\bpost-thumbnail\\b[^"\']*["\'][^>]*>\\s*<a\\b[^>]*)\\s+aria-hidden=["\']true["\']/i',
		'$1',
		$html,
		1
	);
	$html = preg_replace( '/<textarea\\b(?![^>]*\\baria-label=)(?=[^>]*\\bid=["\']comment["\'])/i', '<textarea aria-label="متن دیدگاه"', $html, 1 );
	return $html;
}, 40 );
