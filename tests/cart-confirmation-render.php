<?php
/** Render the production confirmation markup with minimal WordPress stubs. */
define( 'ABSPATH', __DIR__ );
class WooCommerce {}
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function is_admin() { return false; }
function is_checkout() { return false; }
function wc_get_cart_url() { return 'https://cart.test/cart/'; }
function esc_attr( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_url( $url ) { return esc_attr( $url ); }
require dirname( __DIR__ ) . '/bijan-child/inc/ajax-cart.php';
ob_start(); Cloz_Ajax_Cart::render_toast(); $html = ob_get_clean();
if ( in_array( '--html', $argv, true ) ) { echo $html; exit; }
if ( strpos( $html, 'cloz-cart-toast__progress' ) !== false || strpos( $html, 'src=""' ) !== false || strpos( $html, 'aria-hidden="true" hidden' ) === false || strpos( $html, 'cloz-cart-toast__header' ) === false ) throw new RuntimeException( 'Confirmation markup contract failed.' );
echo "PASS confirmation header/body/action markup, initial hidden state, no progress bar or empty image request\n";
