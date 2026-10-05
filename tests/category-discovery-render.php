<?php
/** Render the actual PHP component without requiring a running store. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function wp_unique_id( $prefix = '' ) { static $id = 0; return $prefix . ++$id; }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_url( $value ) { return preg_match( '#^https?://#', $value ) ? esc_attr( $value ) : ''; }
function absint( $value ) { return abs( (int) $value ); }
function wp_get_attachment_image( $id, $size, $icon, $attributes ) {
	$colors = [ '#b9c8bd', '#e4cfb6', '#b6c7d0', '#d6b4ab', '#aaa5b8', '#93b6b3' ];
	$color = $colors[ ( $id - 1 ) % count( $colors ) ];
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#f1f0ec"/><ellipse cx="200" cy="251" rx="87" ry="12" fill="#dbd8d2"/><path d="M142 64 176 46Q200 64 224 46L258 64 298 104 265 136 244 116 244 244 156 244 156 116 135 136 102 104Z" fill="' . $color . '"/><path d="M177 48Q200 82 223 48M200 71v173" stroke="#ffffff" stroke-opacity=".35" fill="none" stroke-width="3"/></svg>';
	$html = '<img src="data:image/svg+xml;base64,' . base64_encode( $svg ) . '" width="400" height="300"';
	foreach ( $attributes as $key => $value ) $html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
	return $html . '>';
}
require dirname( __DIR__ ) . '/bijan-child/inc/category-discovery.php';
$titles = [ 'تی‌شرت و پولوشرت', 'پیراهن مردانه', 'شلوار و جین', 'پوشاک تابستانی', 'لباس‌های ورزشی', 'استایل روزمره' ];
$items = [];
foreach ( range( 1, 12 ) as $id ) $items[] = [ 'image_id' => $id, 'title' => $titles[ ( $id - 1 ) % count( $titles ) ], 'link' => 'https://example.test/category/' . $id . '/' ];
ob_start(); cloz_render_category_discovery( $items ); $html = ob_get_clean();
if ( in_array( '--html', $argv, true ) ) { echo $html; exit; }
if ( substr_count( $html, 'class="cloz-discovery-card"' ) !== 12 || substr_count( $html, 'loading="eager"' ) !== 2 || substr_count( $html, 'loading="lazy"' ) !== 10 || strpos( $html, 'width="400" height="300"' ) === false ) throw new RuntimeException( 'Rendering contract failed' );
$items = [ [ 'title' => '<bad>', 'link' => 'javascript:alert(1)' ], [ 'title' => 'Valid', 'link' => [ 'url' => 'https://example.test/valid/' ] ] ];
ob_start(); cloz_render_category_discovery( $items ); $safe = ob_get_clean();
if ( strpos( $safe, 'javascript:' ) !== false || strpos( $safe, '&lt;bad&gt;' ) === false || substr_count( $safe, '<a class="cloz-discovery-card"' ) !== 1 ) throw new RuntimeException( 'Escaping or ACF link format failed' );
echo "PASS native responsive images, image priorities, semantic links, ACF link formats and escaping\n";
