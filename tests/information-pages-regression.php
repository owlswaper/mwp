<?php
/**
 * Standalone regression for the WordPress pagination collision and native setup.
 * Run: php tests/information-pages-regression.php
 * All mutations here use in-memory fixtures, never a WordPress database.
 */
if ( 'cli' !== PHP_SAPI ) { http_response_code( 404 ); exit; }
error_reporting( E_ALL );
set_error_handler( static function ( $severity, $message, $file, $line ) {
	throw new ErrorException( $message, 0, $severity, $file, $line );
} );
define( 'ABSPATH', __DIR__ );
define( 'OBJECT', 'OBJECT' );
class WP_Post { public $ID; public $post_name; public $post_title; public $post_content; public $post_status = 'publish'; public $post_parent = 0; }
class WP_Error { public function __construct( $code, $message ) {} }
$fixtures = array( 'posts' => array(), 'options' => array(), 'meta' => array(), 'menus' => array(), 'locations' => array( 'main-menu' => 55, 'footer-menu' => 60 ), 'admin' => true, 'writes' => 0 );
function add_action( ...$args ) {} function add_filter( ...$args ) {} function add_shortcode( ...$args ) {}
function get_stylesheet_directory() { return dirname( __DIR__ ) . '/bijan-child'; }
function trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function home_url( $s = '/' ) { return 'https://cloz.test' . $s; }
function esc_url( $s ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $s ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function wp_slash( $s ) { return is_array( $s ) ? array_map( 'wp_slash', $s ) : ( is_string( $s ) ? addslashes( $s ) : $s ); }
function wp_unslash( $s ) { return is_array( $s ) ? array_map( 'wp_unslash', $s ) : ( is_string( $s ) ? stripslashes( $s ) : $s ); }
function is_wp_error( $v ) { return $v instanceof WP_Error; }
function is_page() { return true; }
function get_queried_object() { return $GLOBALS['test_current_post']; }
function get_page_by_path( $slug, ...$args ) { foreach ( $GLOBALS['fixtures']['posts'] as $p ) { if ( $p->post_name === $slug ) { return $p; } } return null; }
function get_permalink( $p = null ) { if ( ! $p ) { $p = get_queried_object(); } if ( is_int( $p ) ) { $p = $GLOBALS['fixtures']['posts'][ $p ]; } return home_url( '/' . $p->post_name . '/' ); }
function get_header() { echo '<!-- header -->'; }
function get_footer() { echo '<!-- footer -->'; }
function have_posts() { return ! $GLOBALS['test_loop_done']; }
function the_post() {
	$GLOBALS['test_loop_done'] = true;
	// This is the crucial behavior in WP_Query::setup_postdata(), missed before.
	$GLOBALS['pages'] = array( get_queried_object()->post_content );
}
function the_content() { echo str_replace( '[clz_trust_seals]', clz_information_trust_seals(), get_queried_object()->post_content ); }
function get_option( $key, $default = false ) { return $GLOBALS['fixtures']['options'][ $key ] ?? $default; }
function update_option( $key, $value, ...$args ) { $GLOBALS['fixtures']['options'][ $key ] = $value; }
function add_option( $key, $value, ...$args ) { if ( array_key_exists( $key, $GLOBALS['fixtures']['options'] ) ) { return false; } update_option( $key, $value ); return true; }
function delete_option( $key ) { unset( $GLOBALS['fixtures']['options'][ $key ] ); }
function current_user_can( $cap ) { return $GLOBALS['fixtures']['admin']; }
function get_post_meta( $id, $key, ...$args ) { return $GLOBALS['fixtures']['meta'][ $id ][ $key ] ?? ''; }
function add_post_meta( $id, $key, $value, $unique = false ) { if ( $unique && isset( $GLOBALS['fixtures']['meta'][ $id ][ $key ] ) ) { return false; } $GLOBALS['fixtures']['meta'][ $id ][ $key ] = wp_unslash( $value ); return true; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['fixtures']['meta'][ $id ][ $key ] = $value; }
function wp_update_post( $values, ...$args ) { $values = wp_unslash( $values ); $GLOBALS['fixtures']['writes']++; foreach ( $values as $key => $value ) { $GLOBALS['fixtures']['posts'][ $values['ID'] ]->$key = $value; } return $values['ID']; }
function get_theme_mod( $key, $default = null ) { return $GLOBALS['fixtures']['locations']; }
function set_theme_mod( $key, $value ) { $GLOBALS['fixtures']['locations'] = $value; }
function wp_get_nav_menu_object( $value ) { foreach ( $GLOBALS['fixtures']['menus'] as $id => $menu ) { if ( $value === $id || $value === $menu['name'] ) { return (object) array( 'term_id' => $id ); } } return false; }
function wp_create_nav_menu( $name ) { $id = count( $GLOBALS['fixtures']['menus'] ) + 60; $GLOBALS['fixtures']['menus'][ $id ] = array( 'name' => $name, 'items' => array() ); return $id; }
function wp_get_nav_menu_items( $id ) { return $GLOBALS['fixtures']['menus'][ $id ]['items']; }
function wp_update_nav_menu_item( $menu, $id, $values ) {
	if ( ! empty( $GLOBALS['test_fail_menu_once'] ) ) { $GLOBALS['test_fail_menu_once'] = false; return new WP_Error( 'test', 'test failure' ); }
	$page = $GLOBALS['fixtures']['posts'][ $values['menu-item-object-id'] ];
	$GLOBALS['fixtures']['menus'][ $menu ]['items'][] = (object) array( 'object' => 'page', 'object_id' => $page->ID, 'url' => get_permalink( $page ) );
	return count( $GLOBALS['fixtures']['menus'][ $menu ]['items'] );
}
function assert_test( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
function render_in_template_loader_scope() {
	global $pages; // WordPress includes templates in global scope.
	$template = getenv( 'CLOZ_PAGE_TEMPLATE' ) ?: get_stylesheet_directory() . '/page-information.php';
	ob_start(); require $template; return ob_get_clean();
}
require get_stylesheet_directory() . '/inc/information-pages.php';
require get_stylesheet_directory() . '/inc/store-presentation.php';
foreach ( clz_information_pages() as $slug => $data ) {
	$p = new WP_Post(); $p->ID = count( $fixtures['posts'] ) + 1; $p->post_name = $slug; $p->post_title = $data['title']; $p->post_content = clz_information_seed_content( $slug ); $fixtures['posts'][ $p->ID ] = $p;
}
$before = array_map( static function ( $p ) { return $p->post_content; }, $fixtures['posts'] );
$contact = get_page_by_path( 'contact' ); $contact->post_content = 'Existing contact with a literal \\n and custom edits';
$before_contact = $contact->post_content;
$fixtures['options']['clz_information_version'] = '2026-10-01-1';
$fixtures['options']['bijan'] = array( 'footer_menu_count' => 0, 'footer_about' => 'KEEP ABOUT', 'footer_menu1_title' => 'KEEP TITLE', 'my-account-welcome' => 'به فروشگاه بیژن خوش آمدید.', 'custom' => 'KEEP CUSTOM' );
$fixtures['menus'][60] = array( 'name' => 'Existing footer', 'items' => array( (object) array( 'object' => 'custom', 'object_id' => 0, 'url' => 'https://example.test/existing' ) ) );
$fixtures['admin'] = false; clz_install_store_presentation(); assert_test( 0 === $fixtures['writes'], 'Non-admin must not write' );
$fixtures['admin'] = true; $GLOBALS['test_fail_menu_once'] = true; clz_install_store_presentation();
assert_test( ! get_option( 'clz_store_presentation_version' ), 'Failed migration must retry' );
assert_test( ! get_option( 'clz_store_presentation_lock' ), 'Lock released on early return' );
clz_install_store_presentation();
assert_test( get_post_meta( $contact->ID, '_clz_contact_before_redesign', true ) === $before_contact, 'Contact backup retains escapes and edits' );
$settings = get_option( 'bijan' );
assert_test( $settings['my-account-welcome'] === 'به فروشگاه کلوز خوش آمدید.', 'Welcome persisted in native option' );
assert_test( $settings['footer_menu_count'] === 2 && $settings['footer_show_menu1'] && $settings['footer_show_menu2'], 'Native footer settings' );
assert_test( $settings['footer_about'] === 'KEEP ABOUT' && $settings['custom'] === 'KEEP CUSTOM' && $settings['footer_menu1_title'] === 'KEEP TITLE', 'Unrelated native settings retained' );
assert_test( $fixtures['locations']['main-menu'] === 55, 'Header menu retained' );
assert_test( count( $fixtures['menus'][60]['items'] ) === 5, 'Existing footer link plus four new guide links' );
$second_menu = $fixtures['locations']['footer-contact-menu']; assert_test( count( $fixtures['menus'][ $second_menu ]['items'] ) === 4, 'Four store links' );
foreach ( $fixtures['posts'] as $id => $p ) { if ( $id !== $contact->ID ) { assert_test( $p->post_content === $before[$id], 'Other pages are not rewritten' ); } }
$writes = $fixtures['writes']; $settings['footer_show_menu1'] = false; $settings['my-account-welcome'] = 'Custom welcome'; update_option( 'bijan', $settings ); $contact->post_content .= '<!-- custom -->'; clz_install_store_presentation();
assert_test( $writes === $fixtures['writes'] && get_option( 'bijan' )['my-account-welcome'] === 'Custom welcome' && ! get_option( 'bijan' )['footer_show_menu1'], 'Later administrator edits are respected' );
foreach ( clz_information_pages() as $slug => $data ) {
	$GLOBALS['test_current_post'] = get_page_by_path( $slug ); $GLOBALS['test_loop_done'] = false;
	$html = render_in_template_loader_scope();
	assert_test( strpos( $html, '<!-- footer -->' ) !== false, 'Complete rendering through footer: ' . $slug );
	assert_test( substr_count( $html, '<h1>' ) === 1, 'One heading: ' . $slug );
	assert_test( substr_count( $html, '<li><a href=' ) === 7, 'Seven related links survive WordPress pagination global: ' . $slug );
}
echo "PASS: 8 complete pages despite WP pagination global; native footer migration; failure retry; preserved settings, menus and content; administrator edits remain editable.\n";
