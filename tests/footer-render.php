<?php
/** Render the actual child menu template without a live WordPress installation. */
define( 'ABSPATH', __DIR__ );
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function wp_unique_id( $prefix = '' ) { static $id = 0; return $prefix . ++$id; }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function wp_nav_menu( $args ) {
	$menus = [ 'footer-menu' => [ 'shipping' => 'ارسال و تحویل سفارش', 'returns' => 'مرجوعی و بازگشت وجه', 'faq' => 'سوالات متداول', 'terms' => 'شرایط خرید' ], 'footer-contact-menu' => [ 'about' => 'دربارهٔ کلوز', 'contact' => 'تماس و پشتیبانی', 'privacy' => 'حریم خصوصی', 'licenses' => 'مجوزها و نماد اعتماد' ] ];
	$items = $menus[$args['theme_location']] ?? [ 'account' => 'حساب کاربری', 'tracking' => 'پیگیری سفارش' ];
	echo '<div class="' . esc_attr( $args['container_class'] ) . '"><ul class="' . esc_attr( $args['menu_class'] ) . '">';
	foreach ( $items as $slug => $title ) echo '<li><a href="https://footer.test/' . $slug . '/">' . esc_html( $title ) . '</a></li>';
	echo '</ul></div>';
}
$menus = '';
foreach ( [ [ 'menu', 'راهنمای خرید', 'bijan-icon-grid' ], [ 'contact-menu', 'کلوز و پشتیبانی', 'bijan-icon-call' ] ] as $item ) {
	$args = [ 'menu' => $item[0], 'title' => $item[1], 'icon' => $item[2] ];
	ob_start(); include dirname( __DIR__ ) . '/bijan-child/templates/footer/menu.php'; $menus .= ob_get_clean();
}
if ( in_array( '--html', $argv, true ) ) { echo $menus; exit; }
if ( substr_count( $menus, '<nav ' ) !== 2 || substr_count( $menus, '<h3 ' ) !== 2 || substr_count( $menus, '<a ' ) !== 8 || strpos( $menus, 'https://footer.test/privacy/' ) === false || strpos( $menus, 'aria-hidden="true"' ) === false ) throw new RuntimeException( 'Native menu contract failed.' );
echo "PASS native footer destinations, labelled navigation, heading hierarchy and decorative icons\n";
