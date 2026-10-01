<?php
/** Standalone behavioural checks: php tests/order-tracking-regression.php */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ );
define( 'MINUTE_IN_SECONDS', 60 );
class Json_Result extends Exception {
    public $ok, $data, $status;
    public function __construct( $ok, $data, $status ) { $this->ok = $ok; $this->data = $data; $this->status = $status; }
}
class WC_Order {
    public $id, $uid, $status = 'processing', $meta, $notes = [], $saved = 0;
    public function __construct( $id, $uid, $code ) { $this->id = $id; $this->uid = $uid; $this->meta = [ '_clz_tracking_code' => $code, '_clz_tracking_status' => 'registered', '_clz_tracking_history' => [] ]; }
    public function get_id() { return $this->id; }
    public function get_customer_id() { return $this->uid; }
    public function get_type() { return 'shop_order'; }
    public function get_status() { return $this->status; }
    public function get_meta( $key, $single = true ) { return $this->meta[$key] ?? ''; }
    public function update_meta_data( $key, $value ) { $this->meta[$key] = $value; }
    public function save() { $this->saved++; }
    public function add_order_note( $note ) { $this->notes[] = $note; }
    public function get_order_number() { return $this->id; }
    public function get_date_created() { return new DateTime( '2026-10-01 10:00:00' ); }
    public function get_items() { return [ new Item_Stub() ]; }
    public function get_item_count() { return 2; }
    public function get_formatted_line_subtotal( $item ) { return '۱۲۳٬۰۰۰ تومان'; }
    public function get_formatted_order_total() { return '۱۲۳٬۰۰۰ تومان'; }
    public function get_formatted_billing_full_name() { return 'مشتری نمونه'; }
    public function get_billing_phone() { return $this->id === 1 ? '09121111111' : '09122222222'; }
    public function get_shipping_state() { return ''; }
    public function get_billing_state() { return 'تهران'; }
    public function get_shipping_city() { return 'تهران'; }
    public function get_shipping_method() { return 'دیجی‌پی'; }
}
class Item_Stub {
    public function get_product() { return $this; }
    public function get_image( $size ) { return '<img src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2264%22 height=%2264%22%3E%3C/svg%3E" alt="پیرسینگ">'; }
    public function get_name() { return 'پیرسینگ حلقه‌ای مدل نمونه با نام طولانی برای بررسی خوانایی در موبایل'; }
    public function get_quantity() { return 2; }
}
class Order_Util_Stub { public static $hpos = false; public static function custom_orders_table_usage_is_enabled() { return self::$hpos; } }
class_alias( 'Order_Util_Stub', 'Automattic\WooCommerce\Utilities\OrderUtil' );
class WC_Admin_Meta_Boxes { public static $errors = []; public static function add_error( $message ) { self::$errors[] = $message; } }
class WP_Error { private $code; public function __construct( $code, $message ) { $this->code = $code; } public function get_error_code() { return $this->code; } }
$uid = 7; $session = 'session-a'; $nonce_ok = true; $permission = true; $rates = []; $hooks = []; $queries = []; $sms_calls = 0;
$orders = [ 1 => new WC_Order( 1, 7, 'BJN-ABCDEFGHIJKL' ), 2 => new WC_Order( 2, 8, 'BJN-MNOPQRSTUVWX' ) ];
$_SERVER['REQUEST_METHOD'] = 'POST';
function add_action( $hook, $callback, ...$args ) { $GLOBALS['hooks'][$hook] = $callback; }
function add_filter( $hook, $callback, ...$args ) { add_action( $hook, $callback ); }
function add_shortcode( $hook, $callback ) { add_action( $hook, $callback ); }
function get_current_user_id() { return $GLOBALS['uid']; }
function wp_get_session_token() { return $GLOBALS['session']; }
function is_user_logged_in() { return get_current_user_id() > 0; }
function check_ajax_referer( ...$args ) { return $GLOBALS['nonce_ok']; }
function nocache_headers() {}
function wp_send_json_error( $data, $status = 200 ) { throw new Json_Result( false, $data, $status ); }
function wp_send_json_success( $data ) { throw new Json_Result( true, $data, 200 ); }
function get_transient( $key ) { return $GLOBALS['rates'][$key] ?? false; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['rates'][$key] = $value; }
function get_option( $key, $default = false ) { return $key === 'clz_order_tracking_settings' ? [ 'sms_enabled' => '0' ] : $default; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function wp_json_encode( $value ) { return json_encode( $value ); }
function wp_salt( $scope ) { return 'unit-test-signing-key'; }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return is_scalar( $value ) ? strip_tags( (string) $value ) : ''; }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', (string) $value ); }
function absint( $value ) { return abs( (int) $value ); }
function current_user_can( ...$args ) { return $GLOBALS['permission']; }
function wp_verify_nonce( ...$args ) { return $GLOBALS['nonce_ok']; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url_raw( $value, $protocols = [] ) { return preg_match( '/^https?:\/\//i', $value ) ? $value : ''; }
function esc_url( $value ) { return esc_attr( $value ); }
function wp_parse_url( $value ) { return parse_url( $value ); }
function wp_kses_post( $value ) { return $value; }
function number_format_i18n( $value ) { return (string) $value; }
function wc_format_datetime( $date, $format ) { return $date->format( $format ); }
function wc_get_order_status_name( $value ) { return $value; }
function current_time( $format ) { return '2026-10-01 12:00:00'; }
function wp_date( $format, $timestamp ) { return date( $format, $timestamp ); }
function get_gmt_from_date( $date, $format ) { return gmdate( $format, strtotime( $date ) ); }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wc_get_order( $id ) { return $GLOBALS['orders'][$id] ?? false; }
function wc_get_orders( $args ) {
    $GLOBALS['queries'][] = $args;
    if ( isset( $args['customer_id'] ) ) {
        $items = array_values( array_filter( $GLOBALS['orders'], function( $order ) use ( $args ) { return $order->uid === $args['customer_id']; } ) );
        return (object) [ 'orders' => $items, 'max_num_pages' => 1 ];
    }
    $code = $args['meta_query'][0]['value'] ?? $args['meta_value'] ?? '';
    return array_values( array_filter( $GLOBALS['orders'], function( $order ) use ( $code ) { return $order->meta['_clz_tracking_code'] === $code; } ) );
}
function wp_remote_post( ...$args ) { $GLOBALS['sms_calls']++; throw new RuntimeException( 'Tracking must never send OTP.' ); }
function get_stylesheet_directory() { return dirname( __DIR__ ) . '/bijan-child'; }
function get_stylesheet_directory_uri() { return '/theme/bijan-child'; }
function trailingslashit( $path ) { return rtrim( $path, '/' ) . '/'; }
function is_page( $page ) { return $page === 'order-tracking'; }
function wp_register_style( ...$args ) {}
function wp_register_script( ...$args ) {}
function wp_enqueue_style( ...$args ) {}
function wp_enqueue_script( ...$args ) {}
function wp_localize_script( ...$args ) {}
function wp_script_is( ...$args ) { return true; }
function wp_add_inline_script( $handle, $script, $position ) { $GLOBALS['inline_scripts'][] = [ $handle, $script, $position ]; }
function admin_url( $path ) { return '/wp-admin/' . $path; }
function wp_create_nonce( $action ) { return 'test-nonce'; }
function wc_get_page_permalink( $page ) { return '/my-account/'; }
function wc_get_account_endpoint_url( $endpoint ) { return '/my-account/' . $endpoint . '/'; }
function home_url( $path ) { return 'https://cloz.ir' . $path; }
require dirname( __DIR__ ) . '/bijan-child/inc/order-tracking.php';
function check( $condition, $message ) { if ( ! $condition ) throw new RuntimeException( $message ); }
function call_private( $method, ...$args ) { $reflection = new ReflectionMethod( 'CLZ_Order_Tracking', $method ); if ( PHP_VERSION_ID < 80100 ) $reflection->setAccessible( true ); return $reflection->invokeArgs( null, $args ); }
function ajax( $method, $post = [] ) {
    $_POST = $post;
    try { CLZ_Order_Tracking::$method(); } catch ( Json_Result $result ) { return $result; }
    throw new RuntimeException( 'Expected JSON response.' );
}
foreach ( [ 'wp_ajax_nopriv_clz_tracking_lookup', 'wp_ajax_clz_tracking_send_otp', 'wp_ajax_clz_tracking_verify_otp', 'admin_menu' ] as $hook ) check( ! isset( $hooks[$hook] ), 'Public tracking, tracking OTP and custom admin menu must be removed.' );
$uid = 0;
check( ajax( 'ajax_list' )->status === 401 && ajax( 'ajax_lookup', [ 'tracking_code' => 'BJN-ABCDEFGHIJKL' ] )->status === 401, 'Guest access rejected.' );
$uid = 7; $nonce_ok = false;
check( ajax( 'ajax_list' )->status === 403, 'CSRF rejected.' );
$nonce_ok = true;
$_SERVER['REQUEST_METHOD'] = 'GET'; check( ajax( 'ajax_list' )->status === 405, 'GET requests rejected.' ); $_SERVER['REQUEST_METHOD'] = 'POST';
$list = ajax( 'ajax_list' );
check( $list->ok && strpos( $list->data['html'], 'BJN-ABCDEFGHIJKL' ) !== false && strpos( $list->data['html'], 'BJN-MNOPQRSTUVWX' ) === false, 'Only account-owned orders listed.' );
$access = call_private( 'access_token' );
check( ajax( 'ajax_detail', [ 'order_id' => 1, 'access' => $access ] )->ok, 'Owner detail allowed.' );
check( ajax( 'ajax_detail', [ 'order_id' => 2, 'access' => $access ] )->status === 403, 'Order ID enumeration rejected.' );
$session = 'session-b'; check( ajax( 'ajax_detail', [ 'order_id' => 1, 'access' => $access ] )->status === 403, 'Token bound to session.' ); $session = 'session-a';
$uid = 8; check( ajax( 'ajax_detail', [ 'order_id' => 1, 'access' => $access ] )->status === 403, 'Token bound to account.' ); $uid = 7;
check( ajax( 'ajax_detail', [ 'order_id' => 1, 'access' => $access . 'x' ] )->status === 403, 'Tampered token rejected.' );
$expired = call_private( 'encode_token', [ 'uid' => 7, 'session' => hash( 'sha256', $session ), 'exp' => time() - 1 ] );
check( ajax( 'ajax_detail', [ 'order_id' => 1, 'access' => $expired ] )->status === 403, 'Expired access rejected.' );
foreach ( [ false, true ] as $hpos ) {
    Order_Util_Stub::$hpos = $hpos;
    $result = ajax( 'ajax_lookup', [ 'tracking_code' => 'bjn-mnopqrstuvwx' ] );
    check( $result->ok && strpos( $result->data['html'], '#2' ) !== false, 'Different order phone allowed with secret code and login.' );
    $query = end( $queries ); check( $hpos ? isset( $query['meta_query'] ) : isset( $query['meta_key'] ), 'Correct HPOS/legacy query.' );
}
check( $sms_calls === 0, 'No tracking OTP sent.' );
$rates = [];
for ( $i = 0; $i < 12; $i++ ) check( ajax( 'ajax_lookup', [ 'tracking_code' => 'invalid' ] )->status === 404, 'Generic lookup failure.' );
check( ajax( 'ajax_lookup', [ 'tracking_code' => 'invalid' ] )->status === 429, 'Repeated invalid codes throttled.' );
$key = call_private( 'rate_key', 'failed_lookup', '7' ); $rates[$key]['until'] = time() - 1;
check( ajax( 'ajax_lookup', [ 'tracking_code' => 'BJN-ABCDEFGHIJKL' ] )->ok, 'Cooldown expires.' );
$rates = [];
for ( $i = 0; $i < 30; $i++ ) check( ajax( 'ajax_list' )->ok, 'Ordinary repeated reads allowed.' );
for ( $i = 30; $i < 90; $i++ ) ajax( 'ajax_list' );
check( ajax( 'ajax_list' )->status === 429, 'Flood requests throttled.' );
$order = $orders[1];
check( call_private( 'tracking_status', $order ) === 'processing', 'Payment processing is not preparation.' );
$order->status = 'completed'; check( call_private( 'tracking_status', $order ) === 'completed', 'Completion is not proof of delivery.' );
$order->meta['_clz_tracking_status'] = 'delivered'; check( call_private( 'tracking_status', $order ) === 'delivered', 'Actual delivery preserved.' );
$order->status = 'refunded'; check( call_private( 'tracking_status', $order ) === 'cancelled', 'Native refund overrides shipping stage.' );
$order->status = 'processing'; $order->meta['_clz_tracking_status'] = 'preparing';
$_POST = [ 'clz_order_shipping_nonce' => 'nonce', 'clz_shipping_status' => 'shipped', 'clz_tracking_url' => 'https://carrier.example/track/123' ];
$nonce_ok = false; CLZ_Order_Tracking::save_order_fields( 1 ); check( ! $order->get_meta( '_clz_tracking_url' ), 'Admin CSRF rejected.' );
$nonce_ok = true; $permission = false; CLZ_Order_Tracking::save_order_fields( 1 ); check( ! $order->get_meta( '_clz_tracking_url' ), 'Admin capability enforced.' );
$permission = true; CLZ_Order_Tracking::save_order_fields( 1 );
check( $order->get_meta( '_clz_tracking_url' ) === $_POST['clz_tracking_url'] && $order->get_meta( '_clz_tracking_status' ) === 'shipped', 'Native Woo order saves link and stage.' );
$_POST['clz_tracking_url'] = 'javascript:alert(1)'; CLZ_Order_Tracking::save_order_fields( 1 );
check( $order->get_meta( '_clz_tracking_url' ) === 'https://carrier.example/track/123' && count( WC_Admin_Meta_Boxes::$errors ) === 1, 'Unsafe URL rejected without destroying valid link.' );
check( ! CLZ_Order_Tracking::clean_tracking_url( 'https://user:pass@carrier.example/track' ), 'URL credentials rejected.' );
$rates = []; $detail = ajax( 'ajax_detail', [ 'order_id' => 1, 'access' => $access ] );
check( strpos( $detail->data['html'], 'https://carrier.example/track/123' ) !== false, 'Carrier link rendered.' );
if ( getenv( 'CLZ_TRACKING_FIXTURE_DIR' ) ) {
    file_put_contents( getenv( 'CLZ_TRACKING_FIXTURE_DIR' ) . '/tracking-detail.html', $detail->data['html'] );
    file_put_contents( getenv( 'CLZ_TRACKING_FIXTURE_DIR' ) . '/tracking-list.html', $list->data['html'] );
    file_put_contents( getenv( 'CLZ_TRACKING_FIXTURE_DIR' ) . '/tracking-shell.html', CLZ_Order_Tracking::shortcode() );
    $uid = 0;
    CLZ_Order_Tracking::register_front_assets();
    check( strpos( $GLOBALS['inline_scripts'][0][1], 'showLogin=true' ) !== false, 'Native login modal auto-opens before its script.' );
    $guest = CLZ_Order_Tracking::shortcode();
    check( strpos( $guest, 'clz-track-code-form' ) === false && strpos( $guest, '/my-account/' ) !== false, 'Guest login shell has no order lookup form.' );
    file_put_contents( getenv( 'CLZ_TRACKING_FIXTURE_DIR' ) . '/tracking-guest.html', $guest );
}
echo "PASS: login, CSRF, ownership, session/token validation, no OTP, HPOS/legacy lookup, rate limits, native order writes and carrier links.\n";
