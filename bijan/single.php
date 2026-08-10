<?php

use Bijan\Utils;
use Bijan\Utils\Archive;
use Bijan\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$primary_classes = ['content-area', 'site-content', 'row'];
$show_sidebar = false;
if( $show_sidebar ) {
	$primary_classes[] = 'content-area-with-sidebar';
}

$options = Options::get_options( [
	'post-title-tag'	=> 'h1'
] );

/**
 * تصاویر داخل محتوای پست را از سایز medium/thumbnail به large ارتقا می‌دهد.
 * فقط تصاویری که آی‌دی attachment معتبر دارند (کلاس wp-image-{id}) پردازش می‌شوند.
 * این تابع فقط زمانی اجرا می‌شود که کش صفحه (FlyingPress) در حال ساخت کش است،
 * نه در هر بازدید — بنابراین هزینه‌ی دیتابیس فقط یک‌بار پرداخت می‌شود.
 */
function bijan_upgrade_content_images( $content ) {
	if ( empty( $content ) || strpos( $content, 'wp-image-' ) === false ) {
		return $content;
	}

	libxml_use_internal_errors( true );
	$dom = new DOMDocument( '1.0', 'UTF-8' );

	$html = mb_convert_encoding(
		'<!DOCTYPE html><html><body>' . $content . '</body></html>',
		'HTML-ENTITIES',
		'UTF-8'
	);

	$loaded = $dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();

	if ( ! $loaded ) {
		return $content;
	}

	$images = $dom->getElementsByTagName( 'img' );

	// چون هنگام replace کردن، NodeList زنده تغییر می‌کند، ابتدا یک آرایه‌ی ثابت می‌سازیم.
	$image_nodes = [];
	foreach ( $images as $img ) {
		$image_nodes[] = $img;
	}

	foreach ( $image_nodes as $img ) {
		$class = $img->getAttribute( 'class' );

		if ( ! preg_match( '/wp-image-(\d+)/', $class, $m ) ) {
			continue;
		}

		$attachment_id = (int) $m[1];

		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			continue;
		}

		$new_img_html = wp_get_attachment_image(
			$attachment_id,
			'large',
			false,
			[
				'loading'       => $img->getAttribute( 'loading' ) ?: 'lazy',
				'fetchpriority' => $img->getAttribute( 'fetchpriority' ) ?: 'low',
				'decoding'      => $img->getAttribute( 'decoding' ) ?: 'async',
				'class'         => trim( preg_replace( '/size-\S+/', 'size-large', $class ) ),
				'alt'           => $img->getAttribute( 'alt' ),
			]
		);

		if ( ! $new_img_html ) {
			continue;
		}

		// یک DOM موقت جدا برای پارس امن HTML (نه XML strict) — از appendXML خطرناک صرف‌نظر شده.
		$tmp_dom = new DOMDocument();
		libxml_use_internal_errors( true );
		$tmp_dom->loadHTML(
			mb_convert_encoding( '<div>' . $new_img_html . '</div>', 'HTML-ENTITIES', 'UTF-8' ),
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();

		$tmp_img = $tmp_dom->getElementsByTagName( 'img' )->item( 0 );

		if ( ! $tmp_img ) {
			continue;
		}

		$new_node = $dom->importNode( $tmp_img, true );

		if ( $img->parentNode ) {
			$img->parentNode->replaceChild( $new_node, $img );
		}
	}

	$body = $dom->getElementsByTagName( 'body' )->item( 0 );

	if ( ! $body ) {
		return $content;
	}

	$result = '';
	foreach ( $body->childNodes as $child ) {
		$result .= $dom->saveHTML( $child );
	}

	return $result;
}

/**
 * جدول‌ها را در یک wrapper واکنشگرا می‌پیچد.
 */
function bijan_wrap_tables( $content ) {
	if ( empty( $content ) || stripos( $content, '<table' ) === false ) {
		return $content;
	}

	return preg_replace_callback(
		'/<table([^>]*)>(.*?)<\/table>/is',
		function( $m ) {
			return '<div class="table-responsive-wrap"><table' . $m[1] . '>' . $m[2] . '</table></div>';
		},
		$content
	);
}

get_header();

while ( have_posts() ) :
	the_post();

	$shares = bijan_single_share();

	$categories = get_the_category();
	$tags = get_the_tag_list( '', ' ' );

	$views = bijan_get_post_views();
	?>

	<style>
	/* ─── Post Layout ──────────────────────────────────────── */
	#primary { max-width: 100%; padding: 0; margin: 0; }
	.entry-container { padding: 0 !important; }
	#page-content.single { padding: 0 !important; background: transparent; }

	article.entry-content {
		max-width: 800px;
		margin: 0 auto;
		padding: 0 20px 60px;
		background: transparent;
		box-shadow: none;
	}
	@media (min-width: 768px)  { article.entry-content { padding: 0 28px 80px; } }
	@media (min-width: 1024px) { article.entry-content { padding: 0 0 80px; } }

	/* ─── Post Header ──────────────────────────────────────── */
	#post-header {
		padding: 32px 0 24px;
		border-bottom: 1px solid rgba(0,0,0,0.08);
		margin-bottom: 24px;
	}
	#post-title {
		font-size: clamp(1.55rem, 4vw, 2.4rem);
		font-weight: 800;
		line-height: 1.35;
		color: #111827;
		margin: 0 0 18px;
		letter-spacing: -0.02em;
		word-break: keep-all;
		overflow-wrap: break-word;
		direction: rtl;
	}
	@media (max-width: 600px) {
		#post-title { font-size: clamp(1.25rem, 5.5vw, 1.65rem); line-height: 1.45; }
	}

	/* ─── Post Meta ────────────────────────────────────────── */
	#post-subheader { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; direction: rtl; }
	.post-meta-wrap { display: flex; flex-wrap: wrap; gap: 10px 16px; align-items: center; }
	.post-meta { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #6b7280; }
	.post-meta-icon { color: #9ca3af; font-size: 0.88rem; }
	.post-meta a { color: #6b7280; text-decoration: none; }
	.post-meta a:hover { color: #2563eb; }
	.post-views-texts { display: flex; gap: 3px; }

	/* ─── Share: hidden ────────────────────────────────────── */
	#post-share-wrap { display: none !important; }

	/* ─── Thumbnail ─────────────────────────────────────────── */
	.post-thumbnail,
	figure.post-thumbnail,
	#post-thumbnail-wrap {
		width: 100% !important;
		max-width: 100% !important;
		margin: 0 0 28px !important;
		border-radius: 12px;
		overflow: hidden;
		display: block;
		background: #f3f4f6;
	}
	.post-thumbnail img,
	figure.post-thumbnail img,
	#post-thumbnail-wrap img {
		width: 100% !important;
		max-width: 100% !important;
		height: auto !important;
		display: block;
		border-radius: 12px;
	}
	@media (max-width: 768px) {
		.post-thumbnail, figure.post-thumbnail, #post-thumbnail-wrap { border-radius: 8px; margin: 0 0 20px !important; }
		.post-thumbnail img, figure.post-thumbnail img, #post-thumbnail-wrap img { border-radius: 8px; }
	}

	/* ─── Table of Contents ────────────────────────────────── */
	#bijan-toc {
		background: #f8f9ff;
		border: 1px solid #e0e4f8;
		border-right: 4px solid #3b5bdb;
		border-radius: 10px;
		padding: 18px 20px;
		margin: 0 0 36px;
		direction: rtl;
	}
	#bijan-toc-header { display: flex; align-items: center; justify-content: space-between; user-select: none; }
	#bijan-toc-title { display: flex; align-items: center; gap: 7px; font-size: 0.97rem; font-weight: 700; color: #1e3a8a; margin: 0; }
	#bijan-toc-title::before { content: "≡"; font-size: 1.1rem; color: #3b5bdb; }
	#bijan-toc-toggle {
		cursor: pointer;
		color: #3b5bdb;
		font-size: 0.78rem;
		padding: 3px 10px;
		border-radius: 20px;
		border: 1px solid #c7d2fe;
		background: rgba(59,91,219,0.06);
		transition: background 0.2s;
	}
	#bijan-toc-toggle:hover { background: rgba(59,91,219,0.13); }
	#bijan-toc-list { list-style: none !important; margin: 14px 0 0; padding: 14px 0 0; border-top: 1px solid #e0e4f8; }
	#bijan-toc-list.toc-hidden { display: none; }
	#bijan-toc-list li { list-style: none !important; margin: 0; padding: 0; }
	#bijan-toc-list li::before,
	#bijan-toc-list li::after,
	#bijan-toc-list li::marker { content: none !important; display: none !important; }
	#bijan-toc-list a {
		display: block;
		padding: 5px 10px 5px 0;
		font-size: 0.9rem;
		color: #374151;
		text-decoration: none;
		border-radius: 5px;
		transition: background 0.15s, color 0.15s, padding-right 0.15s;
	}
	#bijan-toc-list a::before,
	#bijan-toc-list a::after { content: none !important; display: none !important; }
	#bijan-toc-list a:hover { color: #3b5bdb; padding-right: 6px; background: rgba(59,91,219,0.06); }
	#bijan-toc-list a.toc-active { color: #3b5bdb; font-weight: 600; background: rgba(59,91,219,0.09); padding-right: 6px; }
	@media (max-width: 600px) { #bijan-toc { padding: 14px 14px; } }

	/* ─── Post Body ─────────────────────────────────────────── */
	.entry-content > .wp-content,
	.entry-content > div.post-body { padding: 0 !important; margin: 0 !important; }

	article.entry-content > p,
	article.entry-content > h2,
	article.entry-content > h3,
	article.entry-content > h4,
	article.entry-content > blockquote,
	article.entry-content > figure,
	article.entry-content > pre,
	article.entry-content > .wp-block-group,
	article.entry-content > .wp-block-paragraph {
		margin-left: 0 !important;
		margin-right: 0 !important;
		padding-left: 0 !important;
		padding-right: 0 !important;
	}

	/* ─── Typography ──────────────────────────────────────── */
	.entry-content p {
		color: #1f2937 !important;
		font-size: 1.08rem;
		line-height: 2;
		margin: 0 0 1.4em;
		direction: rtl;
		text-align: right;
		word-spacing: 0.03em;
	}
	@media (max-width: 768px) {
		.entry-content p { font-size: 1.05rem; line-height: 2.05; margin: 0 0 1.35em; letter-spacing: 0.01em; }
	}

	/* ─── Headings ──────────────────────────────────────────── */
	.entry-content h2 {
		font-size: clamp(1.3rem, 3.2vw, 1.65rem);
		font-weight: 800;
		color: #111827 !important;
		margin: 2.2em 0 0.7em;
		padding-bottom: 9px;
		border-bottom: 2px solid #e0e7ff;
		line-height: 1.35;
		word-break: keep-all;
		direction: rtl;
		text-align: right;
		letter-spacing: -0.01em;
	}
	.entry-content h3 {
		font-size: clamp(1.1rem, 2.6vw, 1.32rem);
		font-weight: 700;
		color: #1e3a8a !important;
		margin: 1.8em 0 0.55em;
		line-height: 1.4;
		word-break: keep-all;
		direction: rtl;
		text-align: right;
	}
	.entry-content h4 {
		font-size: 1.08rem;
		font-weight: 700;
		color: #374151 !important;
		margin: 1.5em 0 0.5em;
		direction: rtl;
		text-align: right;
	}
	@media (max-width: 600px) {
		.entry-content h2 { font-size: clamp(1.15rem, 4.8vw, 1.4rem); margin-top: 1.8em; }
		.entry-content h3 { font-size: clamp(1.05rem, 3.8vw, 1.2rem); }
	}

	/* ─── Images ────────────────────────────────────────────── */
	.entry-content img { max-width: 100% !important; width: 100% !important; height: auto !important; border-radius: 10px; display: block; margin: 1.6em auto; }
	.entry-content figure { margin: 1.6em 0; max-width: 100% !important; }
	.entry-content figure img { margin: 0; }
	.entry-content figcaption { font-size: 0.84rem; color: #9ca3af; text-align: center; margin-top: 7px; direction: rtl; }
	@media (max-width: 600px) {
		.entry-content img, .entry-content figure { border-radius: 8px; margin: 1.2em 0; }
	}

	/* ─── Professional Video Player ─────────────────────── */

    .entry-content .wp-video,
    .entry-content video {
    	display: block !important;
    	float: none !important;
    	clear: both !important;
    }
    
    
    .entry-content .wp-video {
    	width: 100% !important;
    	max-width: 800px !important;
    	margin: 32px auto !important;
    	background: #000;
    	border-radius: 18px;
    	overflow: hidden;
    	box-shadow: 0 10px 35px rgba(0,0,0,.18);
    }
    
    
    .entry-content .wp-video video,
    .entry-content video {
    	display: block !important;
    	width: 100% !important;
    	max-width: 100% !important;
    	height: auto !important;
    	max-height: 75vh;
    	object-fit: contain !important;
    	background: #000;
    	margin: 0 auto !important;
    	border-radius: 18px !important;
    }
    
    
    /* کنترلر مدیا پلیر وردپرس */
    .entry-content .mejs-container,
    .entry-content .mejs-inner,
    .entry-content .mejs-mediaelement {
    	width:100% !important;
    	max-width:100% !important;
    	border-radius:18px;
    	overflow:hidden;
    }
    
    
    /* همه ویدیوهای غیرعریض داخل قاب */
    .entry-content .wp-video video {
        max-height:75vh;
        object-fit:contain !important;
    }
        
    /* موبایل */
    @media(max-width:768px){
    
    	.entry-content .wp-video {
    		margin:24px auto !important;
    		border-radius:12px;
    	}
    
    	.entry-content .wp-video video,
    	.entry-content video {
    		border-radius:12px !important;
    		max-height:70vh;
    	}
    
    }

	/* ─── جدول — آبی یخی، مدرن، واکنشگرا ─────────────────── */
	.entry-content .table-responsive-wrap {
		width: 100%;
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
		margin: 1.8em 0;
		border-radius: 12px;
		box-shadow: 0 2px 20px rgba(96,165,250,0.13);
		border: 1px solid #bae6fd;
	}
	.entry-content table {
		width: 100% !important;
		min-width: 480px;
		border-collapse: collapse !important;
		margin: 0 !important;
		font-size: 0.93rem !important;
		direction: rtl !important;
		background: #fff;
	}
	.entry-content thead { background: #0ea5e9 !important; }
	.entry-content th {
		padding: 13px 16px !important;
		font-weight: 700 !important;
		color: #fff !important;
		text-align: right !important;
		font-size: 0.91rem !important;
		border: none !important;
		border-left: 1px solid rgba(255,255,255,0.18) !important;
		white-space: nowrap;
		letter-spacing: 0.01em;
		text-shadow: 0 1px 2px rgba(0,0,0,0.12);
	}
	.entry-content th:last-child { border-left: none !important; }
	.entry-content td {
		padding: 10px 16px !important;
		color: #0f172a !important;
		text-align: right !important;
		border: none !important;
		border-bottom: 1px solid #e0f2fe !important;
		font-size: 0.92rem !important;
		line-height: 1.75 !important;
		vertical-align: middle;
	}
	.entry-content tbody tr:last-child td { border-bottom: none !important; }
	.entry-content tbody tr { transition: background 0.15s; }
	.entry-content tbody tr:nth-child(even) { background: #f0f9ff !important; }
	.entry-content tbody tr:hover { background: #e0f2fe !important; }

	/* ─── لیست‌ها ─────────────────────────────────────────── */
	article.entry-content ul,
	article.entry-content ol {
		list-style: none !important;
		padding: 0 !important;
		margin: 0.9em 0 1.4em !important;
		direction: rtl !important;
		display: block !important;
		width: 100% !important;
		box-sizing: border-box !important;
	}
	article.entry-content ol { counter-reset: bijan-ol !important; }

	#page-body article.entry-content ul li,
	#page-body article.entry-content ol li {
		display: block !important;
		position: relative !important;
		padding-left: 0 !important;
		width: 100% !important;
		max-width: 100% !important;
		box-sizing: border-box !important;
		float: none !important;
		clear: none !important;
		color: #1f2937 !important;
		font-size: 1.05rem !important;
		line-height: 1.95 !important;
		direction: rtl !important;
		text-align: right !important;
		background: transparent !important;
		list-style: none !important;
		list-style-type: none !important;
		margin-bottom: 10px !important;
		word-break: normal !important;
		word-spacing: normal !important;
		letter-spacing: normal !important;
		white-space: normal !important;
		overflow-wrap: break-word !important;
	}
	#page-body article.entry-content ul li { padding-right: 28px !important; counter-increment: none !important; }
	#page-body article.entry-content ol li { padding-right: 40px !important; counter-increment: bijan-ol !important; }

	#page-body article.entry-content ul li p,
	#page-body article.entry-content ol li p {
		display: block !important;
		width: auto !important;
		max-width: 100% !important;
		margin: 0 !important;
		padding: 0 !important;
		float: none !important;
		color: inherit !important;
		font-size: inherit !important;
		line-height: inherit !important;
		direction: rtl !important;
		text-align: right !important;
		white-space: normal !important;
		word-break: normal !important;
		overflow-wrap: break-word !important;
	}

	article.entry-content ul > li::before {
		content: "" !important;
		display: block !important;
		position: absolute !important;
		right: 8px !important;
		top: 0.75em !important;
		width: 7px !important;
		height: 7px !important;
		min-width: 7px !important;
		background: #0ea5e9 !important;
		border-radius: 50% !important;
	}
	article.entry-content ol > li::before {
		content: counter(bijan-ol) !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		position: absolute !important;
		right: 0 !important;
		top: 0.28em !important;
		width: 24px !important;
		height: 24px !important;
		min-width: 24px !important;
		border-radius: 50% !important;
		background: #0ea5e9 !important;
		color: #fff !important;
		font-size: 0.74rem !important;
		font-weight: 700 !important;
		line-height: 1 !important;
	}
	article.entry-content ul > li::after,
	article.entry-content ol > li::after { content: none !important; display: none !important; }

	@media (max-width: 768px) {
		article.entry-content ul > li,
		article.entry-content ol > li { font-size: 1.02rem !important; line-height: 2 !important; margin-bottom: 8px !important; }
	}

	/* ─── Blockquote ────────────────────────────────────────── */
	.entry-content blockquote {
		border-right: 4px solid #3b5bdb;
		border-left: none;
		margin: 1.8em 0;
		padding: 14px 18px 14px 12px;
		background: #f0f4ff;
		border-radius: 0 8px 8px 0;
		color: #374151 !important;
		font-style: italic;
		direction: rtl;
		text-align: right;
	}

	/* ─── Code ──────────────────────────────────────────────── */
	.entry-content pre {
		background: #1e1e2e;
		color: #cdd6f4;
		border-radius: 10px;
		padding: 18px 20px;
		overflow-x: auto;
		font-size: 0.88rem;
		line-height: 1.7;
		margin: 1.5em 0;
		direction: ltr;
		text-align: left;
	}
	.entry-content code { background: #eef0ff; color: #3b5bdb; border-radius: 4px; padding: 2px 5px; font-size: 0.87em; }
	.entry-content pre code { background: none; color: inherit; padding: 0; }

	/* ─── Links ─────────────────────────────────────────────── */
	.entry-content a { color: #2563eb; text-decoration: underline; text-underline-offset: 3px; transition: color 0.2s; }
	.entry-content a:hover { color: #1e3a8a; }

	/* ─── Post Footer ───────────────────────────────────────── */
	#post-footer { margin-top: 36px; padding-top: 22px; border-top: 1px solid #e5e7eb; direction: rtl; }
	.post-terms { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-bottom: 12px; font-size: 0.86rem; direction: rtl; }
	.post-term-title { font-weight: 700; color: #6b7280; }
	.post-terms a { background: #eef0ff; color: #3b5bdb; padding: 4px 12px; border-radius: 20px; text-decoration: none; font-size: 0.81rem; transition: background 0.2s, color 0.2s; }
	.post-terms a:hover { background: #3b5bdb; color: #fff; }
	.post-term-separator { display: none; }

	/* ─── Related Posts ─────────────────────────────────────── */
	#latests-posts-wrap { margin-top: 48px; padding-top: 30px; border-top: 2px solid #eef0ff; direction: rtl; }
	#latests-posts-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }

	/* ─── Sticky/float buttons — hide on mobile ─────────────── */
	@media (max-width: 991px) {
		.widget_bijan_filter_widget,
		.widget-filter,
		[class*="filter-widget"],
		[id*="filter-widget"],
		.sidebar-filter,
		#sidebar-single [class*="filter"],
		.bijan-sticky,
		.sticky-sidebar,
		[class*="sticky-btn"],
		[class*="float-btn"],
		[class*="scroll-btn"],
		[id*="sticky-btn"],
		[id*="float-btn"],
		.fixed-sidebar-btn,
		.sidebar-toggle-btn,
		[class*="sidebar-toggle"],
		[class*="toc-toggle-fixed"],
		[class*="toc-float"],
		[id*="toc-float"],
		.toc-fixed,
		.toc-floating {
			display: none !important;
		}
	}

	/* ─── Mobile layout reset ───────────────────────────────── */
	@media (max-width: 767px) {
		.entry-container,
		.content-area-with-sidebar .entry-container,
		.col-md-9,
		#page-content.single,
		#page-body,
		#primary,
		.content-area,
		.site-content {
			width: 100% !important;
			max-width: 100% !important;
			flex: 0 0 100% !important;
			padding: 0 !important;
			margin: 0 !important;
			box-sizing: border-box !important;
		}
		article.entry-content { padding: 0 16px 48px !important; width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; }
		body { overflow-x: hidden; }
	}

	/* ─── RTL global ─────────────────────────────────────────── */
	article.entry-content,
	#post-header,
	#post-subheader,
	.post-meta-wrap { direction: rtl; text-align: right; }

	/* ─── Smooth scroll ──────────────────────────────────────── */
	html { scroll-behavior: smooth; }
	.toc-anchor { display: block; position: relative; top: -80px; visibility: hidden; pointer-events: none; }
	</style>

	<div id="page-body" class="page-width">
		<main id="page-main">
			
			<?php bijan_breadcrumb() ?>

			<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?> role="complementary">
				<div class="entry-container<?php echo $show_sidebar ? ' col-md-9 col-sm-12' : ' col-12' ?>">
					<div id="page-content" class="site-content single" role="article">
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content' ); ?>>
							<header id="post-header" aria-labelledby="post-title">
								<<?php echo tag_escape( $options['post-title-tag'] ) ?> id="post-title"><?php echo bijan_get_post_title() ?></<?php echo tag_escape( $options['post-title-tag'] ) ?>>

								<div id="post-subheader">
									<div class="post-meta-wrap">
										<div class="post-meta post-date">
											<i class="bijan-icon-calendar post-meta-icon" aria-hidden="true"></i>
											<time class="post-meta-value" datetime="<?php echo get_the_date( 'Y-m-d H:i:s' ) ?>"><?php echo get_the_date() ?></time>
										</div>

										<?php if( comments_open() ) { ?>
											<div class="post-meta post-comments-count">
												<i class="bijan-icon-messages post-meta-icon" aria-hidden="true"></i>
												<a href="#respond" class="post-meta-value"><?php echo comments_number( __( "0 comment", 'bijan' ), __( "1 comment", 'bijan' ), __( "% comment", 'bijan' ) ) ?></a>
											</div>
										<?php } ?>

										<div class="post-meta post-views">
											<i class="bijan-icon-eye post-views-icon"></i>
											<div class="post-views-texts">
												<span class="post-views-count"><?php echo esc_html( $views ) ?></span>
												<span class="post-views-after"><?php echo esc_html_x( 'Views', 'Post views count label', 'bijan' ) ?></span>
											</div>
										</div>

										<div class="post-meta post-author">
											<i class="bijan-icon-author post-meta-icon" aria-hidden="true"></i>
											<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="post-meta-value" rel="author"><?php the_author() ?></a>
										</div>
									</div>
								</div>
							</header>

							<?php bijan_post_thumbnail() ?>

							<?php
							/* ── محتوای پست را می‌گیریم ── */
							ob_start();
							the_content();
							$raw_content = ob_get_clean();

							/* ── ارتقای کیفیت تصاویر به سایز large (امن + سازگار با کش صفحه) ── */
							$raw_content = bijan_upgrade_content_images( $raw_content );

							/* ── Table of Contents (فقط h2) ── */
							preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/is', $raw_content, $matches, PREG_SET_ORDER );

							if ( count( $matches ) >= 2 ) {
								$toc_items = [];
								$slug_count = [];

								foreach ( $matches as $match ) {
									$text    = wp_strip_all_tags( $match[1] );
									$base_id = 'toc-' . sanitize_title( $text );

									if ( isset( $slug_count[ $base_id ] ) ) {
										$slug_count[ $base_id ]++;
										$slug = $base_id . '-' . $slug_count[ $base_id ];
									} else {
										$slug_count[ $base_id ] = 0;
										$slug = $base_id;
									}

									$toc_items[] = [
										'text' => $text,
										'id'   => $slug,
									];
								}

								/* فقط h2 ها anchor می‌گیرند */
								$modified_content = preg_replace_callback(
									'/<h2([^>]*)>(.*?)<\/h2>/is',
									function( $m ) use ( &$toc_items ) {
										static $index = 0;
										if ( isset( $toc_items[ $index ] ) ) {
											$id    = $toc_items[ $index ]['id'];
											$attrs = $m[1];
											$inner = $m[2];
											$index++;
											return '<span class="toc-anchor" id="' . esc_attr( $id ) . '"></span><h2' . $attrs . '>' . $inner . '</h2>';
										}
										$index++;
										return $m[0];
									},
									$raw_content
								);

								/* جدول‌ها را در wrapper واکنشگرا می‌پیچیم */
								$modified_content = bijan_wrap_tables( $modified_content );

								echo '<nav id="bijan-toc" aria-label="' . esc_attr__( 'فهرست مطالب', 'bijan' ) . '">';
								echo '<div id="bijan-toc-header">';
								echo '<span id="bijan-toc-title">' . esc_html__( 'فهرست مطالب', 'bijan' ) . '</span>';
								echo '<button id="bijan-toc-toggle" type="button" aria-expanded="true">' . esc_html__( 'بستن', 'bijan' ) . '</button>';
								echo '</div>';
								echo '<ul id="bijan-toc-list">';
								foreach ( $toc_items as $item ) {
									echo '<li><a href="#' . esc_attr( $item['id'] ) . '">' . esc_html( $item['text'] ) . '</a></li>';
								}
								echo '</ul>';
								echo '</nav>';

								echo $modified_content;
							} else {
								/* اگر h2 کافی نبود، محتوا مستقیم — جدول‌ها باز هم wrap می‌شوند */
								echo bijan_wrap_tables( $raw_content );
							}
							?>
							
							<section id="post-footer">
								<?php if( !empty( $categories ) ) { ?>
									<div id="post-categories" class="post-terms">
										<span class="post-term-title"><?php esc_html_e( 'Categories', 'bijan' ) ?>:</span>
										<?php foreach( $categories as $index => $category ) { ?>
											<a href="<?php echo esc_url( get_term_link( $category ), ['http', 'https'] ) ?>"><?php echo esc_html( $category->name ) ?></a>
										<?php } ?>
									</div>
								<?php } ?>

								<?php if( !empty( $tags ) && !is_wp_error( $tags ) ) { ?>
									<div id="post-tags" class="post-terms">
										<span class="post-term-title"><?php esc_html_e( 'Tags', 'bijan' ) ?>:</span>
										<?php echo $tags ?>
									</div>
								<?php } ?>
								<?php wp_link_pages(); ?>
							</section>
						</article>

						<?php comments_template(); ?>
					</div>
				</div>

				<?php
				if( $show_sidebar ) {
					get_sidebar( 'single' );
				}

				$post_type = get_post_type();
				$related_posts = Archive::posts( [
					'grid-mode'					=> true,
					'post_type'					=> $post_type,
					'ppp'						=> 5,
					'show_pagination'			=> false,
					'query_include_category'	=> !empty( $categories ) ? wp_list_pluck( $categories, 'term_id' ) : [],
					'query_exclude_ids'			=> [ get_the_ID() ],
				], false, 'array' );
				if( !empty( $related_posts ) ) {
					if( !$related_posts['is_empty'] ) {
						?>
						<div class="row">
							<div class="col-12" id="latests-posts-wrap">
								<div id="latests-posts-head">
									<?php
									$view_all_link = '';
									if( $post_type === 'post' && !empty( $categories ) ) {
										$view_all_link = get_term_link( $categories[0] );
									} else {
										$view_all_link = get_post_type_archive_link( get_post_type() );
									}
									get_template_part( "templates/components/section_title", null, [
										'icon'	=> 'bijan-icon-book',
										'tag'	=> 'h4',
										'title'	=> __( 'Related Posts', 'bijan' ),
									] );

									if( $view_all_link ) {
										get_template_part( "templates/components/button", null, [
											'type'		=> 'action',
											'text'		=> __( 'View All', 'bijan' ),
											'link'		=> $view_all_link,
											'small'		=> true,
											'style'		=> 'rounded',
											'classes'	=> ['related-posts-view-all'],
										] );
									}
									?>
								</div>

								<div id="latests-posts">
									<?php echo $related_posts['html'] ?>
								</div>

								<div id="latests-posts-bottom">
									<?php
									if( $view_all_link ) {
										get_template_part( "templates/components/button", null, [
											'type'		=> 'action',
											'text'		=> __( 'View All', 'bijan' ),
											'link'		=> $view_all_link,
											'small'		=> true,
											'style'		=> 'rounded',
											'classes'	=> ['related-posts-view-all'],
											'align'		=> 'center',
										] );
									}
									?>
								</div>
							</div>
						</div>
					<?php
					}
				}
				?>
			</div>
		</main>
	</div>

	<script>
	(function() {
		var toggleBtn = document.getElementById('bijan-toc-toggle');
		var tocList   = document.getElementById('bijan-toc-list');
		if (toggleBtn && tocList) {
			toggleBtn.addEventListener('click', function() {
				var hidden = tocList.classList.toggle('toc-hidden');
				toggleBtn.textContent = hidden ? 'نمایش' : 'بستن';
				toggleBtn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
			});
		}

		var tocLinks = document.querySelectorAll('#bijan-toc-list a');
		var anchors  = [];
		tocLinks.forEach(function(link) {
			var id = link.getAttribute('href').replace('#', '');
			var el = document.getElementById(id);
			if (el) anchors.push({ el: el, link: link });
		});

		function onScroll() {
			var scrollY = window.scrollY + 110;
			var active  = null;
			anchors.forEach(function(item) {
				if (item.el.offsetTop <= scrollY) active = item;
			});
			tocLinks.forEach(function(l) { l.classList.remove('toc-active'); });
			if (active) active.link.classList.add('toc-active');
		}

		if (anchors.length) {
			window.addEventListener('scroll', onScroll, { passive: true });
			onScroll();
		}

	
	})();
	</script>

	<?php
endwhile;

get_footer();