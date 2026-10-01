<?php
/**
 * Secure order tracking and fulfilment workflow for WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

final class CLZ_Order_Tracking {
	const OPTION            = 'clz_order_tracking_settings';
	const PAGE_OPTION       = 'clz_order_tracking_page_id';
	const NONCE_ACTION      = 'clz_order_tracking';
	const META_STATUS       = '_clz_tracking_status';
	const META_CODE         = '_clz_tracking_code';
	const META_HISTORY      = '_clz_tracking_history';
	const META_DELIVERY_DAY = '_clz_delivery_day';
	const META_WINDOW_FROM  = '_clz_delivery_window_from';
	const META_WINDOW_TO    = '_clz_delivery_window_to';
	const META_URL          = '_clz_tracking_url';
	const ACCESS_TTL        = 1800;

	public static function init() {
		add_shortcode( 'bijan_order_tracking', [ __CLASS__, 'shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_front_assets' ], 20 );
		add_action( 'admin_init', [ __CLASS__, 'ensure_page' ] );
		add_action( 'template_redirect', [ __CLASS__, 'private_page' ] );
		add_filter( 'template_include', [ __CLASS__, 'page_template' ], 99 );
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
		add_action( 'wp_ajax_clz_tracking_list', [ __CLASS__, 'ajax_list' ] );
		add_action( 'wp_ajax_clz_tracking_lookup', [ __CLASS__, 'ajax_lookup' ] );
		add_action( 'wp_ajax_clz_tracking_detail', [ __CLASS__, 'ajax_detail' ] );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', [ __CLASS__, 'admin_order_fields' ] );
		add_action( 'woocommerce_process_shop_order_meta', [ __CLASS__, 'save_order_fields' ], 40 );
		add_action( 'woocommerce_order_status_changed', [ __CLASS__, 'sync_order_status' ], 20, 4 );
		add_filter( 'woocommerce_get_sections_advanced', [ __CLASS__, 'sms_section' ] );
		add_filter( 'woocommerce_get_settings_advanced', [ __CLASS__, 'sms_fields' ], 20, 2 );

		add_action( 'woocommerce_checkout_create_order', [ __CLASS__, 'initialize_order' ], 20 );
		add_action( 'woocommerce_checkout_order_processed', [ __CLASS__, 'schedule_initial_sms' ], 20, 3 );
		add_action( 'clz_send_initial_tracking_sms', [ __CLASS__, 'send_initial_sms' ] );
		add_filter( 'woocommerce_checkout_posted_data', [ __CLASS__, 'normalize_checkout_phone' ], 30 );
		add_action( 'woocommerce_order_details_after_order_table', [ __CLASS__, 'customer_tracking_code' ] );
		add_action( 'woocommerce_email_after_order_table', [ __CLASS__, 'email_tracking_code' ], 20, 4 );
	}

	public static function statuses() {
		return [
			'registered' => [ 'label' => 'ثبت شده', 'icon' => '✓', 'color' => 'var(--primary-100, #20eae7)' ],
			'processing' => [ 'label' => 'در حال انجام', 'icon' => '◌', 'color' => 'var(--primary-100, #20eae7)' ],
			'preparing'  => [ 'label' => 'آماده‌سازی برای ارسال', 'icon' => '◌', 'color' => 'var(--primary-100, #20eae7)' ],
			'shipped'    => [ 'label' => 'ارسال شده', 'icon' => '➜', 'color' => 'var(--primary-100, #20eae7)' ],
			'delivered'  => [ 'label' => 'تحویل شده', 'icon' => '✓', 'color' => 'var(--primary-100, #20eae7)' ],
			'cancelled'  => [ 'label' => 'لغو شده', 'icon' => '×', 'color' => 'var(--primary-100, #20eae7)' ],
			'completed'  => [ 'label' => 'تکمیل‌شده', 'icon' => '✓', 'color' => 'var(--primary-100, #20eae7)' ],
		];
	}

	private static function defaults() {
		$defaults = [
			'sms_enabled'    => '1',
		];
		foreach ( self::statuses() as $key => $status ) {
			$defaults[ 'pattern_' . $key ] = '';
			$defaults[ 'variables_' . $key ] = '{order_id};{status};{tracking_code};{delivery_date};{delivery_window}';
		}
		return $defaults;
	}

	private static function settings() {
		return wp_parse_args( (array) get_option( self::OPTION, [] ), self::defaults() );
	}

	public static function normalize_digits( $value ) {
		return strtr( (string) $value, [
			'۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
			'٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
		] );
	}

	public static function normalize_phone( $value ) {
		$number = preg_replace( '/[^0-9]+/', '', self::normalize_digits( $value ) );
		if ( 0 === strpos( $number, '0098' ) ) {
			$number = substr( $number, 4 );
		} elseif ( 0 === strpos( $number, '98' ) ) {
			$number = substr( $number, 2 );
		}
		if ( strlen( $number ) === 10 && 0 === strpos( $number, '9' ) ) {
			$number = '0' . $number;
		}
		return preg_match( '/^09[0-9]{9}$/D', $number ) ? $number : '';
	}

	public static function normalize_checkout_phone( $data ) {
		if ( ! empty( $data['billing_phone'] ) ) {
			$phone = self::normalize_phone( $data['billing_phone'] );
			if ( $phone ) {
				$data['billing_phone'] = $phone;
			}
		}
		return $data;
	}

	private static function random_tracking_code() {
		for ( $attempt = 0; $attempt < 5; $attempt++ ) {
			$code = 'BJN-' . strtoupper( wp_generate_password( 12, false, false ) );
			$found = self::orders_by_code( $code );
			if ( ! $found ) {
				return $code;
			}
		}
		return 'BJN-' . strtoupper( wp_generate_password( 16, false, false ) );
	}

	public static function initialize_order( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		if ( ! $order->get_meta( self::META_CODE, true ) ) {
			$order->update_meta_data( self::META_CODE, self::random_tracking_code() );
		}
		if ( ! $order->get_meta( self::META_STATUS, true ) ) {
			$order->update_meta_data( self::META_STATUS, 'registered' );
			$order->update_meta_data( self::META_HISTORY, [ [
				'status' => 'registered',
				'at'      => current_time( 'mysql' ),
				'user_id' => get_current_user_id(),
			] ] );
		}
	}

	public static function schedule_initial_sms( $order_id, $posted_data, $order ) {
		if ( ! $order instanceof WC_Order || $order->get_meta( '_clz_registered_sms_sent', true ) ) {
			return;
		}
		if ( ! wp_next_scheduled( 'clz_send_initial_tracking_sms', [ absint( $order_id ) ] ) ) {
			wp_schedule_single_event( time() + 10, 'clz_send_initial_tracking_sms', [ absint( $order_id ) ] );
		}
	}

	public static function send_initial_sms( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_meta( '_clz_registered_sms_sent', true ) ) {
			return;
		}
		self::ensure_order_meta( $order );
		$result = self::send_status_sms( $order, 'registered' );
		if ( ! is_wp_error( $result ) ) {
			$order->update_meta_data( '_clz_registered_sms_sent', current_time( 'mysql' ) );
			$order->save();
			$order->add_order_note( 'پیامک ثبت سفارش و کد پیگیری برای مشتری ارسال شد.' );
		} elseif ( 'sms_disabled' !== $result->get_error_code() && 'sms_not_configured' !== $result->get_error_code() ) {
			$order->add_order_note( 'خطا در پیامک ثبت سفارش: ' . $result->get_error_message() );
		}
	}

	private static function ensure_order_meta( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		$save = false;
		if ( ! $order->get_meta( self::META_CODE, true ) ) {
			$order->update_meta_data( self::META_CODE, self::random_tracking_code() );
			$save = true;
		}
		if ( ! array_key_exists( $order->get_meta( self::META_STATUS, true ), self::statuses() ) ) {
			$order->update_meta_data( self::META_STATUS, 'registered' );
			$order->update_meta_data( self::META_HISTORY, [ [ 'status' => 'registered', 'at' => $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : current_time( 'mysql' ), 'user_id' => 0 ] ] );
			$save = true;
		}
		if ( $save ) {
			$order->save();
		}
	}

	private static function tracking_status( $order ) {
		$native = $order->get_status();
		if ( in_array( $native, [ 'cancelled', 'failed', 'refunded' ], true ) ) return 'cancelled';
		if ( 'completed' === $native && 'delivered' !== $order->get_meta( self::META_STATUS, true ) ) return 'completed';
		if ( in_array( $native, [ 'pending', 'on-hold', 'checkout-draft' ], true ) ) return 'registered';
		$status = $order->get_meta( self::META_STATUS, true );
		return in_array( $status, [ 'preparing', 'shipped', 'delivered' ], true ) ? $status : ( 'processing' === $native ? 'processing' : 'registered' );
	}

	private static function status_label( $order ) {
		if ( in_array( $order->get_status(), [ 'pending', 'on-hold', 'checkout-draft', 'cancelled', 'failed', 'refunded' ], true ) ) return wc_get_order_status_name( $order->get_status() );
		return self::statuses()[ self::tracking_status( $order ) ]['label'];
	}

	public static function is_tracking_page() {
		return is_page( absint( get_option( self::PAGE_OPTION ) ) ?: 'order-tracking' );
	}

	public static function private_page() {
		if ( ! self::is_tracking_page() ) return;
		if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
		nocache_headers();
	}

	public static function page_template( $template ) {
		return self::is_tracking_page() ? get_stylesheet_directory() . '/page-order-tracking.php' : $template;
	}

	public static function body_class( $classes ) {
		if ( self::is_tracking_page() ) $classes[] = 'clz-order-tracking-page';
		return $classes;
	}

	public static function register_front_assets() {
		$dir = trailingslashit( get_stylesheet_directory() );
		$uri = trailingslashit( get_stylesheet_directory_uri() );
		wp_register_style( 'clz-order-tracking', $uri . 'assets/order-tracking.css', [], filemtime( $dir . 'assets/order-tracking.css' ) );
		wp_register_script( 'clz-order-tracking', $uri . 'assets/order-tracking.js', [ 'jquery' ], filemtime( $dir . 'assets/order-tracking.js' ), true );
		if ( self::is_tracking_page() ) {
			wp_enqueue_style( 'clz-order-tracking' );
			wp_enqueue_script( 'clz-order-tracking' );
			if ( ! is_user_logged_in() && wp_script_is( 'bijan-auth-modal', 'enqueued' ) ) {
				wp_add_inline_script( 'bijan-auth-modal', 'if(window.bijanLogin){window.bijanLogin.showLogin=true;}', 'before' );
			}
		}
	}

	public static function shortcode() {
		if ( ! function_exists( 'wc_get_orders' ) ) return '<p>پیگیری سفارش در حال حاضر در دسترس نیست. لطفاً با پشتیبانی کلوز تماس بگیرید.</p>';
		wp_enqueue_style( 'clz-order-tracking' );
		wp_enqueue_script( 'clz-order-tracking' );
		wp_localize_script( 'clz-order-tracking', 'clzTracking', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( self::NONCE_ACTION ),
			'loggedIn' => is_user_logged_in(),
		] );
		ob_start(); ?>
		<section class="clz-tracker" dir="rtl" aria-labelledby="clz-track-title">
			<header class="clz-track-heading"><h1 id="clz-track-title">پیگیری سفارش</h1><p>وضعیت سفارش، جزئیات خرید و لینک رهگیری مرسوله را در این صفحه مشاهده کنید.</p></header>
			<?php if ( ! is_user_logged_in() ) : ?>
			<div class="clz-track-login"><h2>ابتدا وارد حساب خود شوید</h2><p>پس از ورود، سفارش‌های حساب شما نمایش داده می‌شوند. برای پیگیری سفارش با شماره همراه متفاوت نیز می‌توانید کد اختصاصی همان سفارش را وارد کنید؛ در این مرحله پیامک تأیید جداگانه‌ای ارسال نمی‌شود.</p><a class="clz-track-button" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">ورود به حساب کاربری</a></div>
			<?php else : ?>
			<details class="clz-track-lookup"><summary>پیگیری با کد اختصاصی سفارش</summary><p>اگر سفارش در فهرست حساب شما نیست یا با شماره دیگری ثبت شده، کد اختصاصی آن را وارد کنید. این کد با شماره سفارش و کد رهگیری شرکت حمل تفاوت دارد و در جزئیات سفارش و ایمیل خرید درج می‌شود.</p>
			<form id="clz-track-code-form"><label for="clz-track-code">کد اختصاصی سفارش</label><div class="clz-track-input"><input id="clz-track-code" name="tracking_code" dir="ltr" autocomplete="off" maxlength="32" spellcheck="false" required placeholder="BJN-XXXXXXXXXXXX"><button type="submit">مشاهده سفارش</button></div><small>این کد را فقط در اختیار شخصی بگذارید که اجازه مشاهده سفارش را دارد.</small></form></details>
			<div class="clz-track-message" role="status" aria-live="polite"></div>
			<button type="button" data-track-reset>دریافت دوباره سفارش‌ها</button>
			<div id="clz-track-results" aria-live="polite" aria-busy="true"><p data-track-loading>در حال دریافت سفارش‌های شما…</p></div>
			<noscript><p>برای پیگیری در این صفحه، جاوااسکریپت مرورگر را فعال کنید یا سفارش را از <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">حساب کاربری</a> مشاهده کنید.</p></noscript>
			<?php endif; ?>
			<footer class="clz-track-help"><p>پس از تحویل بسته به دیجی‌پی، زمان اعلام‌شده برای تهران ۲۴ ساعت و سایر نقاط ایران ۷۲ ساعت است. زمان آماده‌سازی سفارش جداگانه محاسبه می‌شود.</p><p><a href="<?php echo esc_url( home_url( '/shipping/' ) ); ?>">راهنمای ارسال</a> · <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">تماس با پشتیبانی کلوز</a></p></footer>
		</section>
		<?php return ob_get_clean();
	}

	private static function verify_ajax() {
		nocache_headers();
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) wp_send_json_error( [ 'message' => 'روش درخواست معتبر نیست.' ], 405 );
		if ( ! is_user_logged_in() ) wp_send_json_error( [ 'message' => 'برای مشاهده سفارش وارد حساب کاربری شوید.' ], 401 );
		if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) wp_send_json_error( [ 'message' => 'نشست صفحه منقضی شده است. لطفاً صفحه را تازه‌سازی کنید.' ], 403 );
		if ( ! self::rate_hit( 'requests', (string) get_current_user_id(), 90, MINUTE_IN_SECONDS ) ) wp_send_json_error( [ 'message' => 'تعداد درخواست‌ها زیاد است. لطفاً یک دقیقه دیگر دوباره تلاش کنید.' ], 429 );
	}

	private static function rate_key( $scope, $identifier ) {
		// Account-wide counters cannot be bypassed by switching IP or sessions.
		return 'clz_tr_' . $scope . '_' . md5( strtolower( $identifier ) );
	}

	private static function rate_hit( $scope, $identifier, $limit, $ttl ) {
		$key = self::rate_key( $scope, $identifier );
		$record = get_transient( $key );
		if ( ! is_array( $record ) || $record['until'] <= time() ) $record = [ 'count' => 0, 'until' => time() + $ttl ];
		if ( $record['count'] >= $limit ) return false;
		$record['count']++;
		set_transient( $key, $record, max( 1, $record['until'] - time() ) );
		return true;
	}

	private static function access_token() {
		return self::encode_token( [ 'uid' => get_current_user_id(), 'session' => hash( 'sha256', wp_get_session_token() ), 'exp' => time() + self::ACCESS_TTL ] );
	}

	private static function encode_token( $data ) {
		$payload = rtrim( strtr( base64_encode( wp_json_encode( $data ) ), '+/', '-_' ), '=' );
		return $payload . '.' . hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
	}

	private static function decode_token( $token ) {
		if ( ! is_string( $token ) || strlen( $token ) > 2048 ) return false;
		$parts = explode( '.', (string) $token, 2 );
		if ( 2 !== count( $parts ) || ! hash_equals( hash_hmac( 'sha256', $parts[0], wp_salt( 'auth' ) ), $parts[1] ) ) {
			return false;
		}
		$data = json_decode( base64_decode( strtr( $parts[0], '-_', '+/' ) ), true );
		return is_array( $data ) && ! empty( $data['exp'] ) && (int) $data['exp'] >= time() ? $data : false;
	}

	private static function melipayamak_credentials() {
		$settings = (array) get_option( 'bijan_sms_settings', [] );
		return [
			'username' => sanitize_text_field( $settings['melipayamak']['username'] ?? '' ),
			'password' => (string) ( $settings['melipayamak']['password'] ?? '' ),
		];
	}

	private static function send_pattern_sms( $phone, $body_id, $text ) {
		$credentials = self::melipayamak_credentials();
		if ( ! $credentials['username'] || ! $credentials['password'] || ! absint( $body_id ) ) {
			return new WP_Error( 'sms_not_configured', 'اطلاعات ملی‌پیامک یا شناسه پترن کامل نیست.' );
		}
		$response = wp_remote_post( 'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber', [
			'timeout' => 20,
			'headers' => [ 'content-type' => 'application/x-www-form-urlencoded' ],
			'body'    => [
				'username' => $credentials['username'],
				'password' => $credentials['password'],
				'to'       => $phone,
				'text'     => $text,
				'bodyId'   => absint( $body_id ),
			],
		] );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$http_code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$ret_status = isset( $data['RetStatus'] ) ? (int) $data['RetStatus'] : ( isset( $data['retStatus'] ) ? (int) $data['retStatus'] : 0 );
		if ( $http_code < 200 || $http_code >= 300 || 1 !== $ret_status ) {
			$message = sanitize_text_field( $data['StrRetStatus'] ?? $data['strRetStatus'] ?? 'پاسخ نامعتبر از ملی‌پیامک.' );
			return new WP_Error( 'sms_provider_error', $message, [ 'http_code' => $http_code ] );
		}
		return $data;
	}

	public static function ajax_list() {
		self::verify_ajax();
		$page = max( 1, min( 10000, absint( $_POST['page'] ?? 1 ) ) );
		$result = wc_get_orders( [ 'customer_id' => get_current_user_id(), 'type' => 'shop_order', 'limit' => 12, 'page' => $page, 'paginate' => true, 'orderby' => 'date', 'order' => 'DESC' ] );
		wp_send_json_success( [ 'html' => self::orders_html( $result->orders, self::access_token() ), 'page' => $page, 'pages' => (int) $result->max_num_pages ] );
	}

	public static function ajax_lookup() {
		self::verify_ajax();
		$identifier = (string) get_current_user_id();
		if ( get_transient( self::rate_key( 'failed_lookup', $identifier ) ) ) {
			$record = get_transient( self::rate_key( 'failed_lookup', $identifier ) );
			if ( is_array( $record ) && $record['count'] >= 12 && $record['until'] > time() ) wp_send_json_error( [ 'message' => 'چند کد نامعتبر وارد شده است. لطفاً پنج دقیقه دیگر دوباره تلاش کنید.' ], 429 );
		}
		$input = $_POST['tracking_code'] ?? '';
		$code = is_string( $input ) && strlen( $input ) <= 128 ? strtoupper( trim( self::normalize_digits( wp_unslash( $input ) ) ) ) : '';
		$orders = preg_match( '/^BJN-[A-Z0-9]{12,16}$/D', $code ) ? self::orders_by_code( $code ) : [];
		$order = $orders ? reset( $orders ) : false;
		if ( ! $order || ! hash_equals( (string) $order->get_meta( self::META_CODE, true ), $code ) ) {
			self::rate_hit( 'failed_lookup', $identifier, 12, 5 * MINUTE_IN_SECONDS );
			wp_send_json_error( [ 'message' => 'سفارشی با این کد پیدا نشد. کد اختصاصی سفارش را بررسی کنید یا با پشتیبانی تماس بگیرید.' ], 404 );
		}
		wp_send_json_success( [ 'html' => self::order_detail_html( $order ) ] );
	}

	public static function ajax_detail() {
		self::verify_ajax();
		$order_id = absint( $_POST['order_id'] ?? 0 );
		$token = self::decode_token( sanitize_text_field( wp_unslash( $_POST['access'] ?? '' ) ) );
		$order = $order_id ? wc_get_order( $order_id ) : false;
		if ( ! $token || ! $order || ! self::token_can_view_order( $token, $order ) ) wp_send_json_error( [ 'message' => 'دسترسی معتبر نیست. لطفاً فهرست سفارش‌ها را دوباره دریافت کنید.' ], 403 );
		wp_send_json_success( [ 'html' => self::order_detail_html( $order ) ] );
	}

	private static function token_can_view_order( $token, $order ) {
		return (int) ( $token['uid'] ?? 0 ) === get_current_user_id()
			&& hash_equals( hash( 'sha256', wp_get_session_token() ), (string) ( $token['session'] ?? '' ) )
			&& 'shop_order' === $order->get_type()
			&& (int) $order->get_customer_id() === get_current_user_id();
	}

	private static function mask_phone( $phone ) {
		return substr( $phone, 0, 4 ) . '***' . substr( $phone, -4 );
	}

	/** Custom metadata queries differ between HPOS and the legacy data store. */
	private static function orders_by_code( $code ) {
		$args = [ 'type' => 'shop_order', 'limit' => 1 ];
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$args['meta_query'] = [ [ 'key' => self::META_CODE, 'value' => $code, 'compare' => '=' ] ];
		} else {
			$args['meta_key'] = self::META_CODE;
			$args['meta_value'] = $code;
		}
		return wc_get_orders( $args );
	}

	private static function orders_html( $orders, $access ) {
		if ( ! $orders ) {
			return '<div class="clz-track-empty"><i>⌕</i><h2>سفارشی پیدا نشد</h2><p>هنوز سفارشی به این حساب متصل نیست. اگر کد اختصاصی سفارش را دارید، از بخش پیگیری با کد استفاده کنید.</p></div>';
		}
		ob_start();
		?><div class="clz-orders-head"><div><span>سفارش‌های شما</span><h2><?php echo esc_html( sprintf( '%s سفارش', number_format_i18n( count( $orders ) ) ) ); ?></h2></div><button type="button" data-track-reset>به‌روزرسانی فهرست</button></div><div class="clz-orders-grid"><?php
		foreach ( $orders as $order ) {
			self::ensure_order_meta( $order );
			$status_key = self::tracking_status( $order );
			$status = self::statuses()[ $status_key ];
			$status['label'] = self::status_label( $order );
			?>
			<article class="clz-order-card" style="--status-color:<?php echo esc_attr( $status['color'] ); ?>">
				<header><div><small>شماره سفارش</small><strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong></div><span><?php echo esc_html( $status['label'] ); ?></span></header>
				<div class="clz-order-card-meta"><p><small>تاریخ ثبت</small><b><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'Y/m/d' ) ); ?></b></p><p><small>مبلغ سفارش</small><b><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></b></p><p><small>تعداد کالا</small><b><?php echo esc_html( number_format_i18n( $order->get_item_count() ) ); ?></b></p></div>
				<footer><code><?php echo esc_html( $order->get_meta( self::META_CODE, true ) ); ?></code><button type="button" data-track-order="<?php echo esc_attr( $order->get_id() ); ?>" data-track-access="<?php echo esc_attr( $access ); ?>">مشاهده جزئیات <span>←</span></button></footer>
			</article>
			<?php
		}
		?></div><?php
		return ob_get_clean();
	}

	private static function order_detail_html( $order ) {
		self::ensure_order_meta( $order );
		$status_key = self::tracking_status( $order );
		$statuses = self::statuses();
		$delivery_day = $order->get_meta( self::META_DELIVERY_DAY, true );
		$from = $order->get_meta( self::META_WINDOW_FROM, true );
		$to = $order->get_meta( self::META_WINDOW_TO, true );
		$history_dates = [];
		foreach ( (array) $order->get_meta( self::META_HISTORY, true ) as $event ) {
			if ( ! empty( $event['status'] ) && ! empty( $event['at'] ) ) {
				$history_dates[ $event['status'] ] = $event['at'];
			}
		}
		$steps = [ 'registered', 'processing', 'preparing', 'shipped', 'delivered' ];
		$current_index = array_search( $status_key, $steps, true );
		ob_start();
		?>
		<div class="clz-order-detail" tabindex="-1">
			<div class="clz-detail-actions"><button type="button" data-track-back>بازگشت به فهرست</button><code><?php echo esc_html( $order->get_meta( self::META_CODE, true ) ); ?></code></div>
			<header class="clz-detail-head"><div><span>سفارش <?php echo esc_html( '#' . $order->get_order_number() ); ?></span><h2><?php echo esc_html( self::status_label( $order ) ); ?></h2><p>وضعیت سفارش در فروشگاه: <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></p><p>ثبت‌شده در <?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'Y/m/d ساعت H:i' ) ); ?></p></div><i style="--status-color:<?php echo esc_attr( $statuses[ $status_key ]['color'] ); ?>"><?php echo esc_html( $statuses[ $status_key ]['icon'] ); ?></i></header>
			<?php if ( 'cancelled' === $status_key ) : ?><div class="clz-cancelled-note">این سفارش فعال نیست. وضعیت لغو، پرداخت ناموفق یا استرداد وجه را در بخش وضعیت سفارش بررسی کنید. برای راهنمایی بیشتر با پشتیبانی تماس بگیرید.</div><?php elseif ( 'completed' === $status_key ) : ?><p>سفارش در فروشگاه تکمیل شده است. برای اطلاع از تحویل مرسوله، لینک شرکت حمل را بررسی کنید؛ تکمیل سفارش به‌تنهایی تأیید دریافت بسته نیست.</p><?php else : ?>
			<div class="clz-track-timeline">
				<?php foreach ( $steps as $index => $step ) : $done = false !== $current_index && $index <= $current_index; ?>
				<div class="<?php echo $done ? 'is-done' : ''; ?><?php echo $step === $status_key ? ' is-current' : ''; ?>"><i><?php echo $done ? '✓' : esc_html( $index + 1 ); ?></i><span><?php echo esc_html( $statuses[ $step ]['label'] ); ?></span><?php if ( ! empty( $history_dates[ $step ] ) ) : ?><small><?php echo esc_html( wp_date( 'Y/m/d H:i', (int) get_gmt_from_date( $history_dates[ $step ], 'U' ) ) ); ?></small><?php endif; ?></div>
				<?php endforeach; ?>
			</div><?php endif; ?>
			<?php if ( $delivery_day || $from || $to ) : ?><div class="clz-delivery-box"><i>⌁</i><div><span>بازه تقریبی تحویل</span><strong><?php echo esc_html( $delivery_day ? wp_date( 'Y/m/d', strtotime( $delivery_day ) ) : 'تاریخ در حال هماهنگی' ); ?><?php echo ( $from || $to ) ? esc_html( '، ساعت ' . ( $from ?: '—' ) . ' تا ' . ( $to ?: '—' ) ) : ''; ?></strong><small>این بازه ممکن است با توجه به شرایط ارسال کمی تغییر کند.</small></div></div><?php endif; ?>
			<section class="clz-carrier-link"><h3>رهگیری مرسوله</h3><?php $tracking_url = self::clean_tracking_url( $order->get_meta( self::META_URL, true ) ); ?>
			<?php if ( $tracking_url ) : ?><a class="clz-track-button" href="<?php echo esc_url( $tracking_url ); ?>" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer">مشاهده وضعیت در سامانه شرکت حمل</a><p>این لینک، وضعیت مرسوله را در سامانه شرکت حمل نمایش می‌دهد.</p><?php else : ?><p>لینک رهگیری مرسوله هنوز ثبت نشده است. پس از ثبت توسط فروشگاه، در همین بخش نمایش داده می‌شود. برای پیگیری می‌توانید با شماره سفارش به پشتیبانی پیام بدهید.</p><?php endif; ?></section>
			<div class="clz-detail-grid">
				<section><h3>اقلام سفارش</h3><div class="clz-detail-items">
				<?php foreach ( $order->get_items() as $item ) : $product = $item->get_product(); ?>
					<div><?php echo $product ? $product->get_image( 'woocommerce_thumbnail' ) : wc_placeholder_img( 'woocommerce_thumbnail' ); ?><p><strong><?php echo esc_html( $item->get_name() ); ?></strong><small>تعداد: <?php echo esc_html( $item->get_quantity() ); ?></small></p><b><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></b></div>
				<?php endforeach; ?></div><footer><span>مبلغ نهایی</span><strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong></footer></section>
				<aside><h3>اطلاعات ارسال</h3><dl><div><dt>تحویل‌گیرنده</dt><dd><?php echo esc_html( trim( $order->get_formatted_billing_full_name() ) ?: '—' ); ?></dd></div><div><dt>شماره همراه</dt><dd dir="ltr"><?php echo esc_html( self::mask_phone( self::normalize_phone( $order->get_billing_phone() ) ?: $order->get_billing_phone() ) ); ?></dd></div><div><dt>مقصد</dt><dd><?php echo esc_html( implode( '، ', array_filter( [ $order->get_shipping_state() ?: $order->get_billing_state(), $order->get_shipping_city() ?: $order->get_billing_city() ] ) ) ?: '—' ); ?></dd></div><div><dt>روش ارسال</dt><dd><?php echo esc_html( $order->get_shipping_method() ?: '—' ); ?></dd></div></dl></aside>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function customer_tracking_code( $order ) {
		self::ensure_order_meta( $order );
		echo '<p class="clz-customer-code"><strong>کد پیگیری سفارش:</strong> <code dir="ltr">' . esc_html( $order->get_meta( self::META_CODE, true ) ) . '</code></p>';
	}

	public static function email_tracking_code( $order, $sent_to_admin, $plain_text, $email ) {
		if ( $sent_to_admin || ! $order instanceof WC_Order ) return;
		self::ensure_order_meta( $order );
		$code = $order->get_meta( self::META_CODE, true );
		if ( $plain_text ) echo "\nکد پیگیری سفارش: " . $code . "\n";
		else echo '<p><strong>کد پیگیری سفارش:</strong> <code dir="ltr">' . esc_html( $code ) . '</code></p>';
	}

	public static function ensure_page() {
		if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'wc_get_orders' ) ) return;
		$page_id = absint( get_option( self::PAGE_OPTION ) );
		if ( $page_id && 'publish' === get_post_status( $page_id ) ) return;
		$page = get_page_by_path( 'order-tracking' );
		if ( ! $page ) {
			$page_id = wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'پیگیری سفارش', 'post_name' => 'order-tracking', 'post_content' => '[bijan_order_tracking]' ] );
		} else {
			$page_id = $page->ID;
		}
		if ( ! is_wp_error( $page_id ) ) update_option( self::PAGE_OPTION, absint( $page_id ), false );
	}

	public static function clean_tracking_url( $value ) {
		$url = esc_url_raw( trim( (string) $value ), [ 'https', 'http' ] );
		$parts = wp_parse_url( $url );
		return $url && ! empty( $parts['host'] ) && empty( $parts['user'] ) && empty( $parts['pass'] ) && in_array( $parts['scheme'] ?? '', [ 'https', 'http' ], true ) ? $url : '';
	}

	public static function admin_order_fields( $order ) {
		if ( ! $order instanceof WC_Order || ! current_user_can( 'edit_shop_order', $order->get_id() ) ) return;
		self::ensure_order_meta( $order );
		wp_nonce_field( 'clz_order_shipping_' . $order->get_id(), 'clz_order_shipping_nonce' );
		echo '<h3>ارسال و پیگیری کلوز</h3><p>شماره سفارش: ' . esc_html( $order->get_order_number() ) . '<br>کد اختصاصی: <code>' . esc_html( $order->get_meta( self::META_CODE, true ) ) . '</code></p>';
		$options = array_map( static function( $status ) { return $status['label']; }, array_intersect_key( self::statuses(), array_flip( [ 'registered', 'preparing', 'shipped', 'delivered' ] ) ) );
		woocommerce_wp_select( [ 'id' => 'clz_shipping_status', 'label' => 'مرحله ارسال', 'value' => $order->get_meta( self::META_STATUS, true ), 'options' => [ '' => 'انتخاب مرحله ارسال' ] + $options, 'description' => 'مرحله آماده‌سازی، ارسال یا تحویل واقعی را ثبت کنید. لغو، پرداخت ناموفق و استرداد وجه از وضعیت اصلی ووکامرس خوانده می‌شوند.' ] );
		woocommerce_wp_text_input( [ 'id' => 'clz_tracking_url', 'label' => 'لینک رهگیری مرسوله', 'type' => 'url', 'value' => $order->get_meta( self::META_URL, true ), 'description' => 'نشانی کامل رهگیری در سامانه شرکت حمل، با https://. این لینک در صفحه پیگیری مشتری نمایش داده می‌شود.' ] );
		echo '<p><label><input type="checkbox" name="clz_shipping_sms" value="1" checked> در صورت تغییر مرحله، پیامک وضعیت ارسال شود.</label></p>';
	}

	public static function save_order_fields( $order_id ) {
		if ( ! current_user_can( 'edit_shop_order', $order_id ) || empty( $_POST['clz_order_shipping_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['clz_order_shipping_nonce'] ) ), 'clz_order_shipping_' . $order_id ) ) return;
		$order = wc_get_order( $order_id );
		if ( ! $order ) return;
		$old_status = self::tracking_status( $order );
		$status = sanitize_key( wp_unslash( $_POST['clz_shipping_status'] ?? '' ) );
		$url_input = trim( wp_unslash( $_POST['clz_tracking_url'] ?? '' ) );
		$url = self::clean_tracking_url( $url_input );
		if ( $url_input && ! $url ) {
			WC_Admin_Meta_Boxes::add_error( 'لینک رهگیری معتبر نیست. نشانی کامل با http یا https وارد کنید. لینک قبلی حفظ شد.' );
		} else { $order->update_meta_data( self::META_URL, $url ); }
		if ( in_array( $status, [ 'registered', 'preparing', 'shipped', 'delivered' ], true ) ) $order->update_meta_data( self::META_STATUS, $status );
		$new_status = self::tracking_status( $order );
		self::record_status( $order, $old_status, $new_status, ! empty( $_POST['clz_shipping_sms'] ) );
	}

	private static function record_status( $order, $old_status, $new_status, $send_sms ) {
		if ( $old_status !== $new_status ) {
			$history = (array) $order->get_meta( self::META_HISTORY, true );
			$history[] = [ 'status' => $new_status, 'at' => current_time( 'mysql' ), 'user_id' => get_current_user_id() ];
			$order->update_meta_data( self::META_HISTORY, array_slice( $history, -30 ) );
			$order->add_order_note( 'مرحله ارسال کلوز: ' . self::statuses()[ $new_status ]['label'] );
		}
		$order->save();
		if ( $old_status !== $new_status && $send_sms ) {
			$result = self::send_status_sms( $order, $new_status );
			if ( ! is_wp_error( $result ) ) $order->add_order_note( 'پیامک وضعیت سفارش برای مشتری ارسال شد.' );
			elseif ( ! in_array( $result->get_error_code(), [ 'sms_disabled', 'sms_not_configured' ], true ) ) $order->add_order_note( 'ارسال پیامک وضعیت انجام نشد: ' . $result->get_error_message() );
		}
	}

	public static function sync_order_status( $order_id, $from, $to, $order ) {
		$old = $order->get_meta( self::META_STATUS, true ) ?: 'registered';
		$new = self::tracking_status( $order );
		// Returning an order to processing must not retain a terminal shipping stage.
		if ( 'processing' === $to && in_array( $old, [ 'cancelled', 'delivered', 'completed' ], true ) ) $new = 'processing';
		$order->update_meta_data( self::META_STATUS, $new );
		self::record_status( $order, $old, $new, true );
	}

	public static function sms_section( $sections ) {
		$sections['clz_tracking_sms'] = 'پیامک وضعیت سفارش کلوز';
		return $sections;
	}

	public static function sms_fields( $fields, $section ) {
		if ( 'clz_tracking_sms' !== $section ) return $fields;
		$settings = self::settings();
		$fields = [ [ 'type' => 'title', 'id' => 'clz_tracking_sms_title', 'title' => 'پیامک وضعیت سفارش', 'desc' => 'پیامک اطلاع‌رسانی سفارش با تنظیمات ملی‌پیامک قالب ارسال می‌شود. پیگیری سفارش به پیامک تأیید جداگانه نیاز ندارد.' ], [ 'type' => 'checkbox', 'id' => self::OPTION . '[sms_enabled]', 'title' => 'فعال‌سازی', 'desc' => 'ارسال پیامک وضعیت سفارش', 'default' => 'yes', 'value' => '1' === $settings['sms_enabled'] || 'yes' === $settings['sms_enabled'] ? 'yes' : 'no' ] ];
		foreach ( self::statuses() as $key => $status ) {
			$fields[] = [ 'type' => 'number', 'id' => self::OPTION . '[pattern_' . $key . ']', 'title' => 'شناسه پترن: ' . $status['label'], 'value' => $settings[ 'pattern_' . $key ], 'custom_attributes' => [ 'min' => '1' ] ];
			$fields[] = [ 'type' => 'text', 'id' => self::OPTION . '[variables_' . $key . ']', 'title' => 'متغیرهای پترن', 'value' => $settings[ 'variables_' . $key ], 'desc' => 'به ترتیب پترن و با ; جدا کنید: {order_id}، {status}، {tracking_code}، {customer_name}، {delivery_date}، {delivery_window}، {site_name}' ];
		}
		$fields[] = [ 'type' => 'sectionend', 'id' => 'clz_tracking_sms_title' ];
		return $fields;
	}

	private static function send_status_sms( $order, $status_key ) {
		$settings = self::settings();
		if ( ! in_array( $settings['sms_enabled'], [ '1', 'yes' ], true ) ) return new WP_Error( 'sms_disabled', 'پیامک وضعیت غیرفعال است.' );
		$phone = self::normalize_phone( $order->get_billing_phone() );
		if ( ! $phone ) return new WP_Error( 'invalid_phone', 'شماره مشتری معتبر نیست.' );
		$pattern = $settings[ 'pattern_' . $status_key ] ?? '';
		$template = $settings[ 'variables_' . $status_key ] ?? '';
		$delivery_day = $order->get_meta( self::META_DELIVERY_DAY, true );
		$from = $order->get_meta( self::META_WINDOW_FROM, true );
		$to = $order->get_meta( self::META_WINDOW_TO, true );
		$values = [
			'{order_id}'       => $order->get_order_number(),
			'{status}'         => self::statuses()[ $status_key ]['label'],
			'{tracking_code}'  => $order->get_meta( self::META_CODE, true ),
			'{customer_name}'  => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
			'{delivery_date}'  => $delivery_day ? wp_date( 'Y/m/d', strtotime( $delivery_day ) ) : '-',
			'{delivery_window}'=> ( $from || $to ) ? ( $from ?: '-' ) . '-' . ( $to ?: '-' ) : '-',
			'{site_name}'      => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		];
		return self::send_pattern_sms( $phone, $pattern, strtr( $template, $values ) );
	}
}

CLZ_Order_Tracking::init();
