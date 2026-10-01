<?php
/** Native, editable and indexable store information pages. */
defined( 'ABSPATH' ) || exit;

function clz_information_pages() {
	return array(
		'about' => array( 'title' => 'درباره کلوز', 'eyebrow' => 'از سال ۱۴۰۰، همراه انتخاب تو', 'lead' => 'کلوز، فروشگاه اینترنتی تخصصی پیرسینگ و اکسسوری در تهران؛ برای انتخاب از میان مدل‌های متنوع و به‌روز، با عکس واقعی و پشتیبانی در کنار تو.', 'description' => 'با کلوز آشنا شو؛ فروشگاه تخصصی پیرسینگ و اکسسوری از سال ۱۴۰۰، با عکس واقعی محصولات، تنوع به‌روز و ارسال به سراسر ایران.', 'aliases' => array( 'about-us', 'درباره-ما', 'درباره' ) ),
		'contact' => array( 'title' => 'تماس با کلوز', 'eyebrow' => 'یک سؤال داری؟ با ما در تماس باش', 'lead' => 'برای راهنمایی قبل از خرید پیرسینگ و اکسسوری، پیگیری سفارش یا رسیدگی به یک مشکل، از راهی که برایت راحت‌تر است با ما ارتباط بگیر.', 'description' => 'تماس با کلوز، فروشگاه پیرسینگ و اکسسوری؛ هر روز از ۸ تا ۲۲ از طریق تماس، پیامک، تلگرام، بله و پشتیبانی آنلاین پاسخ‌گوی تو هستیم.', 'aliases' => array( 'contact-us', 'تماس-با-ما', 'تماس' ) ),
		'terms' => array( 'title' => 'قوانین خرید از کلوز', 'eyebrow' => 'قبل از ثبت سفارش', 'lead' => 'شرایط ثبت سفارش، پرداخت، ارسال و رسیدگی به درخواست‌ها را اینجا بخوان تا با آگاهی خرید کنی و مسیر پیگیری برایت روشن باشد.', 'description' => 'قوانین خرید پیرسینگ و اکسسوری از کلوز؛ شرایط ثبت سفارش، پرداخت بانکی و اقساطی، لغو، ارسال و حقوق مشتری در خرید اینترنتی.', 'aliases' => array( 'terms-and-conditions', 'قوانین' ) ),
		'faq' => array( 'title' => 'سؤالات متداول', 'eyebrow' => 'جواب‌های روشن برای خرید راحت‌تر', 'lead' => 'از زمان ارسال و پیگیری سفارش تا پرداخت و مرجوعی؛ پاسخ سؤال‌های رایج خرید از فروشگاه پیرسینگ و اکسسوری کلوز را اینجا پیدا کن.', 'description' => 'پاسخ سؤالات رایج خرید از کلوز: ارسال تهران و شهرستان‌ها، هزینه ارسال، کد پیگیری، پرداخت اقساطی، لغو سفارش و رسیدگی به مرجوعی.', 'aliases' => array( 'frequently-asked-questions', 'سوالات-متداول' ) ),
		'shipping' => array( 'title' => 'روش ارسال و پیگیری سفارش', 'eyebrow' => 'از آماده‌سازی تا رسیدن بسته', 'lead' => 'همه سفارش‌های کلوز با سرویس ارسال فوری دیجی‌پی ارسال می‌شوند. زمان آماده‌سازی فروشگاه و زمان حمل مرسوله دو مرحله جدا هستند؛ جزئیات هر دو را اینجا ببین.', 'description' => 'ارسال سفارش‌های کلوز با دیجی‌پی؛ زمان آماده‌سازی، تحویل اعلام‌شده ۲ ساعته تهران و ۷۲ ساعته سراسر ایران، هزینه ثابت و پیگیری پیامکی.', 'aliases' => array( 'shipping-methods', 'روش-های-ارسال' ) ),
		'returns' => array( 'title' => 'شرایط تعویض و مرجوعی', 'eyebrow' => 'اگر سفارشت مشکلی داشت', 'lead' => 'اگر پیرسینگ یا اکسسوری دریافتی معیوب، آسیب‌دیده یا اشتباه است، با ما تماس بگیر. این صفحه مسیر رسیدگی، هزینه‌ها، بازپرداخت و تفاوت آن با انصراف از خرید را توضیح می‌دهد.', 'description' => 'شرایط تعویض و مرجوعی کلوز؛ رسیدگی به کالای معیوب یا اشتباه با هزینه فروشگاه، نحوه بازپرداخت و توضیح حق انصراف قانونی مشتری.', 'aliases' => array( 'refund_returns', 'refund-returns', 'return-policy', 'قوانین-تعویض-و-مرجوعی' ) ),
		'privacy' => array( 'title' => 'حفظ حریم خصوصی', 'eyebrow' => 'اطلاعات تو، برای انجام سفارش تو', 'lead' => 'شفاف می‌گوییم چه اطلاعاتی برای خرید لازم است، چرا از آن استفاده می‌کنیم و چطور می‌توانی درباره اطلاعات شخصی‌ات با ما در تماس باشی.', 'description' => 'سیاست حریم خصوصی کلوز؛ استفاده از اطلاعات برای پردازش و ارسال سفارش، پیامک‌های پیگیری، دسترسی محدود و درخواست اصلاح یا حذف اطلاعات.', 'aliases' => array( 'privacy-policy', 'حفظ-حریم-خصوصی' ) ),
		'licenses' => array( 'title' => 'مجوزها و اعتبار فروشگاه', 'eyebrow' => 'اعتبار را از مرجع آن بررسی کن', 'lead' => 'برای بررسی اعتبار فروشگاه کلوز، روی نشان اعتماد الکترونیکی یا نشان زرین‌پال بزن و اطلاعات را در صفحه رسمی همان مرجع ببین.', 'description' => 'بررسی اعتبار فروشگاه کلوز؛ لینک مستقیم استعلام نماد اعتماد الکترونیکی و نشان زرین‌پال، همراه با راهنمای خرید و تماس با پشتیبانی.', 'aliases' => array( 'مجوز-ها', 'مجوزها' ) ),
	);
}

function clz_information_url( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	return $page && 'publish' === $page->post_status ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}
function clz_information_link( $slug, $label ) {
	return '<a href="' . esc_url( clz_information_url( $slug ) ) . '">' . esc_html( $label ) . '</a>';
}
function clz_information_key() {
	if ( ! is_page() ) { return ''; }
	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) { return ''; }
	foreach ( clz_information_pages() as $slug => $data ) {
		if ( $slug === $page->post_name || in_array( $page->post_name, $data['aliases'], true ) ) { return $slug; }
	}
	return '';
}
function clz_information_seed_content( $slug ) {
	ob_start();
	include trailingslashit( get_stylesheet_directory() ) . 'templates/information/' . $slug . '.php';
	return trim( ob_get_clean() );
}

/** Provision once on an admin visit, reusing IDs and backing up existing content. */
function clz_install_information_pages() {
	$version = '2026-10-01-1';
	if ( ! current_user_can( 'manage_options' ) || get_option( 'clz_information_version' ) === $version ) { return; }
	$lock = (int) get_option( 'clz_information_install_lock' );
	if ( $lock && time() - $lock < 120 ) { return; }
	if ( $lock ) { delete_option( 'clz_information_install_lock' ); }
	if ( ! add_option( 'clz_information_install_lock', time(), '', false ) ) { return; }
	$complete = true;
	foreach ( clz_information_pages() as $slug => $data ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( ! $page ) {
			foreach ( $data['aliases'] as $alias ) {
				$page = get_page_by_path( $alias, OBJECT, 'page' );
				if ( $page ) { break; }
			}
		}
		if ( $page && get_post_meta( $page->ID, '_clz_information_version', true ) === $version ) { continue; }
		if ( $page ) {
			add_post_meta( $page->ID, '_clz_information_original', wp_slash( array( 'title' => $page->post_title, 'slug' => $page->post_name, 'parent' => $page->post_parent, 'status' => $page->post_status, 'content' => $page->post_content ) ), true );
		}
		$post = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => 0, 'post_name' => $slug, 'post_title' => $data['title'], 'post_content' => clz_information_seed_content( $slug ) );
		if ( $page ) { $post['ID'] = $page->ID; }
		$id = wp_insert_post( wp_slash( $post ), true );
		if ( is_wp_error( $id ) || ! $id || get_post_field( 'post_name', $id ) !== $slug ) { $complete = false; continue; }
		$editor = get_post_meta( $id, '_elementor_edit_mode', true );
		if ( $editor ) { add_post_meta( $id, '_clz_information_original_editor', $editor, true ); delete_post_meta( $id, '_elementor_edit_mode' ); }
		update_post_meta( $id, '_clz_information_version', $version );
	}
	if ( $complete ) { update_option( 'clz_information_version', $version, false ); }
	delete_option( 'clz_information_install_lock' );
}
add_action( 'admin_init', 'clz_install_information_pages' );

function clz_information_template( $template ) {
	return clz_information_key() ? trailingslashit( get_stylesheet_directory() ) . 'page-information.php' : $template;
}
add_filter( 'template_include', 'clz_information_template', 99 );
function clz_information_assets() {
	if ( ! clz_information_key() ) { return; }
	$file = trailingslashit( get_stylesheet_directory() ) . 'assets/information-pages.css';
	wp_enqueue_style( 'clz-information-pages', trailingslashit( get_stylesheet_directory_uri() ) . 'assets/information-pages.css', array( 'bijan-child-style' ), (string) filemtime( $file ) );
}
add_action( 'wp_enqueue_scripts', 'clz_information_assets', 30 );

/** Redirect only legacy GET/HEAD requests once canonical pages exist. */
function clz_information_legacy_redirect() {
	if ( is_admin() || ( isset( $_SERVER['REQUEST_METHOD'] ) && ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'HEAD' ), true ) ) ) { return; }
	$path = rawurldecode( trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ) );
	$base = rawurldecode( trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' ) );
	$redirects = array();
	foreach ( clz_information_pages() as $slug => $data ) {
		foreach ( $data['aliases'] as $alias ) { $redirects[ $alias ] = $slug; }
	}
	foreach ( $redirects as $old => $new ) {
		if ( $path !== ( $base ? $base . '/' : '' ) . $old ) { continue; }
		$page = get_page_by_path( $new, OBJECT, 'page' );
		if ( ! $page || 'publish' !== $page->post_status ) { return; }
		$url = get_permalink( $page );
		$query = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_QUERY );
		if ( $query ) { $url .= ( strpos( $url, '?' ) === false ? '?' : '&' ) . $query; }
		wp_safe_redirect( $url, 301 ); exit;
	}
}
add_action( 'template_redirect', 'clz_information_legacy_redirect', 1 );
function clz_information_menu_links( $atts ) {
	foreach ( clz_information_pages() as $slug => $data ) {
		foreach ( $data['aliases'] as $alias ) {
			if ( isset( $atts['href'] ) && untrailingslashit( $atts['href'] ) === untrailingslashit( home_url( '/' . $alias . '/' ) ) ) { $atts['href'] = clz_information_url( $slug ); }
		}
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'clz_information_menu_links' );
function clz_information_seo_title( $title ) {
	$key = clz_information_key();
	return $key ? clz_information_pages()[ $key ]['title'] . ' | کلوز' : $title;
}
function clz_information_seo_description( $description ) {
	$key = clz_information_key();
	return $key ? clz_information_pages()[ $key ]['description'] : $description;
}
add_filter( 'pre_get_document_title', 'clz_information_seo_title', 20 );
add_filter( 'rank_math/frontend/title', 'clz_information_seo_title', 20 );
add_filter( 'rank_math/frontend/description', 'clz_information_seo_description', 20 );
add_filter( 'wpseo_title', 'clz_information_seo_title', 20 );
add_filter( 'wpseo_metadesc', 'clz_information_seo_description', 20 );
function clz_information_metadata() {
	$key = clz_information_key();
	if ( ! $key ) { return; }
	$data = clz_information_pages()[ $key ];
	if ( ! defined( 'RANK_MATH_VERSION' ) && ! defined( 'WPSEO_VERSION' ) ) { echo '<meta name="description" content="' . esc_attr( $data['description'] ) . '">' . "\n"; }
	$type = 'about' === $key ? 'AboutPage' : ( 'contact' === $key ? 'ContactPage' : 'WebPage' );
	$schema = array( '@context' => 'https://schema.org', '@type' => $type, '@id' => get_permalink() . '#clz-information', 'url' => get_permalink(), 'name' => $data['title'], 'description' => $data['description'], 'inLanguage' => 'fa-IR', 'isPartOf' => array( '@type' => 'WebSite', 'url' => home_url( '/' ), 'name' => 'کلوز' ) );
	if ( 'faq' === $key ) {
		// Derive questions from the editable page, so schema cannot retain stale answers.
		$page = get_queried_object();
		preg_match_all( '~<details\b[^>]*>\s*<summary\b[^>]*>(.*?)</summary>(.*?)</details>~si', (string) $page->post_content, $matches, PREG_SET_ORDER );
		$questions = array();
		foreach ( $matches as $match ) {
			$question = trim( html_entity_decode( wp_strip_all_tags( $match[1] ), ENT_QUOTES, 'UTF-8' ) );
			$answer = trim( html_entity_decode( wp_strip_all_tags( $match[2] ), ENT_QUOTES, 'UTF-8' ) );
			if ( $question && $answer ) { $questions[] = array( '@type' => 'Question', 'name' => $question, 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $answer ) ); }
		}
		if ( $questions ) { $schema['@type'] = 'FAQPage'; $schema['mainEntity'] = $questions; }
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'clz_information_metadata', 25 );
function clz_information_trust_seals() {
	ob_start(); ?>
	<div class="clz-info-seals">
		<section aria-labelledby="clz-enamad-title"><h2 id="clz-enamad-title">نماد اعتماد الکترونیکی</h2>
			<a href="https://trustseal.enamad.ir/?id=717705&amp;Code=UEnBF1rDytYpVBldZUl8B2x8IG25l7jI" target="_blank" rel="noopener noreferrer" referrerpolicy="origin" aria-label="مشاهده اعتبار نماد اعتماد الکترونیکی فروشگاه">
				<img src="https://trustseal.enamad.ir/logo.aspx?id=717705&amp;Code=UEnBF1rDytYpVBldZUl8B2x8IG25l7jI" alt="نماد اعتماد الکترونیکی فروشگاه" loading="lazy" decoding="async" referrerpolicy="origin" width="120" height="130" style="cursor:pointer;object-fit:contain">
			</a><p>با انتخاب نشان، مشخصات و وضعیت اعتبار را در سامانه رسمی اینماد بررسی کن.</p>
		</section>
		<section aria-labelledby="clz-zarinpal-title"><h2 id="clz-zarinpal-title">نشان زرین‌پال</h2>
			<div id="zarinpal"><script src="https://www.zarinpal.com/webservice/TrustCode" type="text/javascript"></script></div>
			<p>نشان از سرویس رسمی زرین‌پال دریافت می‌شود. برای بررسی اطلاعات، نشان را انتخاب کن.</p>
		</section>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'clz_trust_seals', 'clz_information_trust_seals' );
