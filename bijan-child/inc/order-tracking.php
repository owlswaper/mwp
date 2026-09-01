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
	const OTP_TTL           = 300;
	const ACCESS_TTL        = 1800;
	private static $admin_max_pages = 1;
	private static $admin_total = 0;

	public static function init() {
		add_shortcode( 'bijan_order_tracking', [ __CLASS__, 'shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_front_assets' ], 20 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_assets' ] );
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ], 25 );
		add_action( 'admin_init', [ __CLASS__, 'ensure_page' ] );

		add_action( 'wp_ajax_nopriv_clz_tracking_lookup', [ __CLASS__, 'ajax_lookup' ] );
		add_action( 'wp_ajax_clz_tracking_lookup', [ __CLASS__, 'ajax_lookup' ] );
		add_action( 'wp_ajax_nopriv_clz_tracking_send_otp', [ __CLASS__, 'ajax_send_otp' ] );
		add_action( 'wp_ajax_clz_tracking_send_otp', [ __CLASS__, 'ajax_send_otp' ] );
		add_action( 'wp_ajax_nopriv_clz_tracking_verify_otp', [ __CLASS__, 'ajax_verify_otp' ] );
		add_action( 'wp_ajax_clz_tracking_verify_otp', [ __CLASS__, 'ajax_verify_otp' ] );
		add_action( 'wp_ajax_nopriv_clz_tracking_detail', [ __CLASS__, 'ajax_detail' ] );
		add_action( 'wp_ajax_clz_tracking_detail', [ __CLASS__, 'ajax_detail' ] );

		add_action( 'admin_post_clz_update_tracking_order', [ __CLASS__, 'admin_update_order' ] );
		add_action( 'admin_post_clz_save_tracking_settings', [ __CLASS__, 'admin_save_settings' ] );

		add_action( 'woocommerce_checkout_create_order', [ __CLASS__, 'initialize_order' ], 20 );
		add_action( 'woocommerce_checkout_order_processed', [ __CLASS__, 'schedule_initial_sms' ], 20, 3 );
		add_action( 'clz_send_initial_tracking_sms', [ __CLASS__, 'send_initial_sms' ] );
		add_filter( 'woocommerce_checkout_posted_data', [ __CLASS__, 'normalize_checkout_phone' ], 30 );
		add_action( 'woocommerce_order_details_after_order_table', [ __CLASS__, 'customer_tracking_code' ] );
		add_action( 'woocommerce_email_after_order_table', [ __CLASS__, 'email_tracking_code' ], 20, 4 );
	}

	public static function statuses() {
		return [
			'registered' => [ 'label' => 'ثبت شده', 'icon' => '✓', 'color' => '#7c3aed' ],
			'preparing'  => [ 'label' => 'آماده‌سازی برای ارسال', 'icon' => '◌', 'color' => '#e08a00' ],
			'shipped'    => [ 'label' => 'ارسال شده', 'icon' => '➜', 'color' => '#0878d1' ],
			'delivered'  => [ 'label' => 'تحویل شده', 'icon' => '✓', 'color' => '#078447' ],
			'cancelled'  => [ 'label' => 'لغو شده', 'icon' => '×', 'color' => '#c92f46' ],
		];
	}

	private static function defaults() {
		$defaults = [
			'otp_pattern_id' => '',
			'otp_variables'  => '{code}',
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

	private static function phone_variants( $phone ) {
		$tail = substr( $phone, 1 );
		return array_values( array_unique( [
			$phone, $tail, '98' . $tail, '+98' . $tail, '0098' . $tail,
			substr( $phone, 0, 4 ) . ' ' . substr( $phone, 4, 3 ) . ' ' . substr( $phone, 7 ),
			substr( $phone, 0, 4 ) . '-' . substr( $phone, 4, 3 ) . '-' . substr( $phone, 7 ),
		] ) );
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
			$found = wc_get_orders( [ 'limit' => 1, 'return' => 'ids', 'meta_key' => self::META_CODE, 'meta_value' => $code ] );
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
		$status = $order->get_meta( self::META_STATUS, true );
		return array_key_exists( $status, self::statuses() ) ? $status : 'registered';
	}

	public static function register_front_assets() {
		$dir = trailingslashit( get_stylesheet_directory() );
		$uri = trailingslashit( get_stylesheet_directory_uri() );
		wp_register_style( 'clz-order-tracking', $uri . 'assets/order-tracking.css', [], file_exists( $dir . 'assets/order-tracking.css' ) ? filemtime( $dir . 'assets/order-tracking.css' ) : BIJAN_CHILD_VERSION );
		wp_register_script( 'clz-order-tracking', $uri . 'assets/order-tracking.js', [ 'jquery' ], file_exists( $dir . 'assets/order-tracking.js' ) ? filemtime( $dir . 'assets/order-tracking.js' ) : BIJAN_CHILD_VERSION, true );
	}

	public static function shortcode() {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return '<div class="clz-track-alert">ووکامرس فعال نیست.</div>';
		}
		wp_enqueue_style( 'clz-order-tracking' );
		wp_enqueue_script( 'clz-order-tracking' );
		wp_localize_script( 'clz-order-tracking', 'clzTracking', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
			'i18n'    => [
				'wait' => 'کمی صبر کنید…',
				'error' => 'ارتباط با سرور برقرار نشد. دوباره تلاش کنید.',
			],
		] );
		ob_start();
		?>
		<section class="clz-tracker" aria-labelledby="clz-track-title">
			<div class="clz-track-hero">
				<div class="clz-track-hero-copy"><span>مرکز پیگیری سفارش</span><h1 id="clz-track-title">سفارشت کجاست؟</h1><p>با شماره همراه ثبت‌شده در سفارش یا کد پیگیری، آخرین وضعیت و زمان تحویل را ببین.</p></div>
				<div class="clz-track-hero-art" aria-hidden="true"><i>✓</i><b></b><b></b><b></b></div>
			</div>

			<div class="clz-track-shell">
				<div class="clz-track-tabs" role="tablist">
					<button type="button" class="is-active" data-track-tab="phone" role="tab" aria-selected="true">شماره همراه</button>
					<button type="button" data-track-tab="code" role="tab" aria-selected="false">کد پیگیری</button>
				</div>
				<div class="clz-track-panel is-active" data-track-panel="phone">
					<form id="clz-track-phone-form" novalidate>
						<label for="clz-track-phone">شماره همراه سفارش</label>
						<div class="clz-track-input"><span>+98</span><input id="clz-track-phone" name="phone" inputmode="tel" autocomplete="tel" maxlength="18" placeholder="مثلاً ۰۹۱۲۱۲۳۴۵۶۷" required><button type="submit">ارسال کد تأیید</button></div>
						<small>برای حفاظت از اطلاعات سفارش، یک کد یک‌بارمصرف برایتان ارسال می‌شود.</small>
					</form>
					<form id="clz-track-otp-form" hidden novalidate>
						<div class="clz-track-otp-head"><div><label for="clz-track-otp">کد تأیید</label><small>کد ارسال‌شده را وارد کنید.</small></div><button type="button" data-track-change-phone>تغییر شماره</button></div>
						<div class="clz-track-input"><input id="clz-track-otp" name="otp" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="کد ۴ تا ۶ رقمی" required><button type="submit">مشاهده سفارش‌ها</button></div>
					</form>
				</div>
				<div class="clz-track-panel" data-track-panel="code" hidden>
					<form id="clz-track-code-form" novalidate>
						<label for="clz-track-code">کد پیگیری سفارش</label>
						<div class="clz-track-input"><input id="clz-track-code" name="tracking_code" dir="ltr" autocomplete="off" maxlength="32" placeholder="BJN-XXXXXXXXXXXX" required><button type="submit">پیگیری سفارش</button></div>
						<small>کد پیگیری در جزئیات سفارش و ایمیل ثبت سفارش درج شده است.</small>
					</form>
				</div>
				<div class="clz-track-message" role="status" aria-live="polite"></div>
			</div>
			<div id="clz-track-results" class="clz-track-results" aria-live="polite"></div>
		</section>
		<?php
		return ob_get_clean();
	}

	private static function verify_ajax() {
		if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'نشست صفحه منقضی شده است؛ صفحه را تازه‌سازی کنید.' ], 403 );
		}
	}

	private static function client_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	}

	private static function rate_key( $scope, $identifier ) {
		return 'clz_tr_' . $scope . '_' . md5( strtolower( $identifier ) . '|' . self::client_ip() );
	}

	private static function rate_hit( $scope, $identifier, $limit, $ttl ) {
		$key = self::rate_key( $scope, $identifier );
		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return false;
		}
		set_transient( $key, $count + 1, $ttl );
		return true;
	}

	private static function encode_token( $data ) {
		$payload = rtrim( strtr( base64_encode( wp_json_encode( $data ) ), '+/', '-_' ), '=' );
		return $payload . '.' . hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
	}

	private static function decode_token( $token ) {
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

	public static function ajax_send_otp() {
		self::verify_ajax();
		$phone = self::normalize_phone( wp_unslash( $_POST['phone'] ?? '' ) );
		if ( ! $phone ) {
			wp_send_json_error( [ 'message' => 'شماره همراه معتبر نیست.' ], 422 );
		}
		if ( ! self::rate_hit( 'otp_send_ip', 'request', 10, 15 * MINUTE_IN_SECONDS ) || ! self::rate_hit( 'otp_send', $phone, 3, 15 * MINUTE_IN_SECONDS ) ) {
			wp_send_json_error( [ 'message' => 'تعداد درخواست‌ها زیاد است؛ ۱۵ دقیقه دیگر تلاش کنید.' ], 429 );
		}
		$settings = self::settings();
		$code = (string) random_int( 1000, 999999 );
		if ( strlen( $code ) < 6 ) {
			$code = str_pad( $code, 6, '0', STR_PAD_LEFT );
		}
		$text = str_replace( [ '{code}', '{site_name}' ], [ $code, wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ], $settings['otp_variables'] );
		$sent = self::send_pattern_sms( $phone, $settings['otp_pattern_id'], $text );
		if ( is_wp_error( $sent ) ) {
			wp_send_json_error( [ 'message' => 'ارسال کد انجام نشد. تنظیمات پیامک را بررسی کنید.' ], 502 );
		}
		$challenge = wp_generate_password( 32, false, false );
		set_transient( 'clz_track_otp_' . hash( 'sha256', $challenge ), [
			'phone'    => $phone,
			'hash'     => wp_hash_password( $code ),
			'attempts' => 0,
		], self::OTP_TTL );
		wp_send_json_success( [
			'message'   => 'کد تأیید ارسال شد.',
			'challenge' => $challenge,
			'masked'    => self::mask_phone( $phone ),
		] );
	}

	public static function ajax_verify_otp() {
		self::verify_ajax();
		$challenge = sanitize_text_field( wp_unslash( $_POST['challenge'] ?? '' ) );
		$otp = preg_replace( '/[^0-9]/', '', self::normalize_digits( wp_unslash( $_POST['otp'] ?? '' ) ) );
		$key = 'clz_track_otp_' . hash( 'sha256', $challenge );
		$record = get_transient( $key );
		if ( ! is_array( $record ) || ! preg_match( '/^[0-9]{4,6}$/D', $otp ) ) {
			wp_send_json_error( [ 'message' => 'کد تأیید نامعتبر یا منقضی شده است.' ], 422 );
		}
		if ( (int) $record['attempts'] >= 5 ) {
			delete_transient( $key );
			wp_send_json_error( [ 'message' => 'تعداد تلاش‌ها بیش از حد مجاز بود؛ کد جدید بگیرید.' ], 429 );
		}
		if ( ! wp_check_password( $otp, $record['hash'] ) ) {
			$record['attempts']++;
			set_transient( $key, $record, self::OTP_TTL );
			wp_send_json_error( [ 'message' => 'کد تأیید درست نیست.' ], 422 );
		}
		delete_transient( $key );
		$access = self::encode_token( [ 'phone' => $record['phone'], 'exp' => time() + self::ACCESS_TTL, 'scope' => 'phone' ] );
		$orders = self::orders_by_phone( $record['phone'] );
		wp_send_json_success( [
			'message' => $orders ? sprintf( '%s سفارش پیدا شد.', number_format_i18n( count( $orders ) ) ) : 'سفارشی با این شماره پیدا نشد.',
			'access'  => $access,
			'html'    => self::orders_html( $orders, $access ),
		] );
	}

	public static function ajax_lookup() {
		self::verify_ajax();
		$code = strtoupper( preg_replace( '/[^A-Za-z0-9\-]/', '', self::normalize_digits( wp_unslash( $_POST['tracking_code'] ?? '' ) ) ) );
		if ( strlen( $code ) < 10 || ! self::rate_hit( 'code_ip', 'lookup', 20, 15 * MINUTE_IN_SECONDS ) || ! self::rate_hit( 'code', $code, 5, 15 * MINUTE_IN_SECONDS ) ) {
			wp_send_json_error( [ 'message' => 'کد پیگیری معتبر نیست یا تعداد تلاش‌ها بیش از حد مجاز است.' ], 422 );
		}
		$orders = wc_get_orders( [ 'limit' => 1, 'meta_key' => self::META_CODE, 'meta_value' => $code ] );
		if ( ! $orders ) {
			wp_send_json_error( [ 'message' => 'سفارشی با این کد پیگیری پیدا نشد.' ], 404 );
		}
		$order = reset( $orders );
		$access = self::encode_token( [ 'order_id' => $order->get_id(), 'code' => $code, 'exp' => time() + self::ACCESS_TTL, 'scope' => 'code' ] );
		wp_send_json_success( [ 'html' => self::order_detail_html( $order ), 'access' => $access ] );
	}

	public static function ajax_detail() {
		self::verify_ajax();
		$order_id = absint( $_POST['order_id'] ?? 0 );
		$token = self::decode_token( sanitize_text_field( wp_unslash( $_POST['access'] ?? '' ) ) );
		$order = $order_id ? wc_get_order( $order_id ) : false;
		if ( ! $token || ! $order || ! self::token_can_view_order( $token, $order ) ) {
			wp_send_json_error( [ 'message' => 'دسترسی شما منقضی یا نامعتبر است.' ], 403 );
		}
		wp_send_json_success( [ 'html' => self::order_detail_html( $order ) ] );
	}

	private static function token_can_view_order( $token, $order ) {
		if ( 'phone' === ( $token['scope'] ?? '' ) ) {
			return self::normalize_phone( $order->get_billing_phone() ) === ( $token['phone'] ?? '' );
		}
		return 'code' === ( $token['scope'] ?? '' ) && (int) ( $token['order_id'] ?? 0 ) === $order->get_id() && hash_equals( (string) $order->get_meta( self::META_CODE, true ), (string) ( $token['code'] ?? '' ) );
	}

	private static function orders_by_phone( $phone ) {
		$found = [];
		foreach ( self::phone_variants( $phone ) as $variant ) {
			foreach ( wc_get_orders( [ 'limit' => 100, 'billing_phone' => $variant, 'orderby' => 'date', 'order' => 'DESC' ] ) as $order ) {
				if ( self::normalize_phone( $order->get_billing_phone() ) === $phone ) {
					$found[ $order->get_id() ] = $order;
				}
			}
		}
		// Catch legacy phones saved with spaces or punctuation. Candidates are
		// normalized and compared exactly before any order data is disclosed.
		$fuzzy_orders = wc_get_orders( [
			'limit'       => 100,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'field_query' => [ [
				'field'   => 'billing_phone',
				'value'   => substr( $phone, -7 ),
				'compare' => 'LIKE',
			] ],
		] );
		foreach ( $fuzzy_orders as $order ) {
			if ( self::normalize_phone( $order->get_billing_phone() ) === $phone ) {
				$found[ $order->get_id() ] = $order;
			}
		}
		usort( $found, static function( $a, $b ) { return $b->get_date_created()->getTimestamp() <=> $a->get_date_created()->getTimestamp(); } );
		return array_values( $found );
	}

	private static function mask_phone( $phone ) {
		return substr( $phone, 0, 4 ) . '***' . substr( $phone, -4 );
	}

	private static function orders_html( $orders, $access ) {
		if ( ! $orders ) {
			return '<div class="clz-track-empty"><i>⌕</i><h2>سفارشی پیدا نشد</h2><p>شماره واردشده را با شماره زمان ثبت سفارش مقایسه کنید.</p></div>';
		}
		ob_start();
		?><div class="clz-orders-head"><div><span>سفارش‌های شما</span><h2><?php echo esc_html( sprintf( '%s سفارش', number_format_i18n( count( $orders ) ) ) ); ?></h2></div><button type="button" data-track-reset>پیگیری جدید</button></div><div class="clz-orders-grid"><?php
		foreach ( $orders as $order ) {
			self::ensure_order_meta( $order );
			$status_key = self::tracking_status( $order );
			$status = self::statuses()[ $status_key ];
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
		$steps = [ 'registered', 'preparing', 'shipped', 'delivered' ];
		$current_index = array_search( $status_key, $steps, true );
		ob_start();
		?>
		<div class="clz-order-detail">
			<div class="clz-detail-actions"><button type="button" data-track-back>بازگشت به فهرست</button><code><?php echo esc_html( $order->get_meta( self::META_CODE, true ) ); ?></code></div>
			<header class="clz-detail-head"><div><span>سفارش <?php echo esc_html( '#' . $order->get_order_number() ); ?></span><h2><?php echo esc_html( $statuses[ $status_key ]['label'] ); ?></h2><p>ثبت‌شده در <?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'Y/m/d ساعت H:i' ) ); ?></p></div><i style="--status-color:<?php echo esc_attr( $statuses[ $status_key ]['color'] ); ?>"><?php echo esc_html( $statuses[ $status_key ]['icon'] ); ?></i></header>
			<?php if ( 'cancelled' === $status_key ) : ?><div class="clz-cancelled-note">این سفارش لغو شده است. برای اطلاعات بیشتر با پشتیبانی تماس بگیرید.</div><?php else : ?>
			<div class="clz-track-timeline">
				<?php foreach ( $steps as $index => $step ) : $done = false !== $current_index && $index <= $current_index; ?>
				<div class="<?php echo $done ? 'is-done' : ''; ?><?php echo $step === $status_key ? ' is-current' : ''; ?>"><i><?php echo $done ? '✓' : esc_html( $index + 1 ); ?></i><span><?php echo esc_html( $statuses[ $step ]['label'] ); ?></span><?php if ( ! empty( $history_dates[ $step ] ) ) : ?><small><?php echo esc_html( wp_date( 'Y/m/d H:i', strtotime( $history_dates[ $step ] ) ) ); ?></small><?php endif; ?></div>
				<?php endforeach; ?>
			</div><?php endif; ?>
			<?php if ( $delivery_day || $from || $to ) : ?><div class="clz-delivery-box"><i>⌁</i><div><span>بازه تقریبی تحویل</span><strong><?php echo esc_html( $delivery_day ? wp_date( 'Y/m/d', strtotime( $delivery_day ) ) : 'تاریخ در حال هماهنگی' ); ?><?php echo ( $from || $to ) ? esc_html( '، ساعت ' . ( $from ?: '—' ) . ' تا ' . ( $to ?: '—' ) ) : ''; ?></strong><small>این بازه ممکن است با توجه به شرایط ارسال کمی تغییر کند.</small></div></div><?php endif; ?>
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

	public static function admin_menu() {
		add_submenu_page( 'woocommerce', 'مدیریت و پیگیری سفارش‌ها', 'پیگیری سفارش‌ها', 'manage_woocommerce', 'clz-order-tracking', [ __CLASS__, 'admin_page' ] );
	}

	public static function admin_assets( $hook ) {
		if ( 'woocommerce_page_clz-order-tracking' !== $hook ) return;
		$path = trailingslashit( get_stylesheet_directory() ) . 'assets/order-tracking-admin.css';
		wp_enqueue_style( 'clz-order-tracking-admin', trailingslashit( get_stylesheet_directory_uri() ) . 'assets/order-tracking-admin.css', [], file_exists( $path ) ? filemtime( $path ) : BIJAN_CHILD_VERSION );
	}

	private static function admin_orders() {
		$search = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
		$page = max( 1, absint( $_GET['paged'] ?? 1 ) );
		$args = [
			'limit'    => $search ? 200 : 30,
			'orderby'  => 'date',
			'order'    => 'DESC',
			'paginate' => ! $search,
			'page'     => $page,
		];
		if ( ! empty( $_GET['from'] ) || ! empty( $_GET['to'] ) ) {
			$from = sanitize_text_field( wp_unslash( $_GET['from'] ?? '2000-01-01' ) );
			$to = sanitize_text_field( wp_unslash( $_GET['to'] ?? wp_date( 'Y-m-d' ) ) );
			$args['date_created'] = $from . '...' . $to . ' 23:59:59';
		}
		$requested_status = sanitize_key( wp_unslash( $_GET['tracking_status'] ?? '' ) );
		if ( $requested_status && array_key_exists( $requested_status, self::statuses() ) ) {
			if ( 'registered' === $requested_status ) {
				$args['meta_query'] = [
					'relation' => 'OR',
					[ 'key' => self::META_STATUS, 'compare' => 'NOT EXISTS' ],
					[ 'key' => self::META_STATUS, 'value' => 'registered' ],
				];
			} else {
				$args['meta_key'] = self::META_STATUS;
				$args['meta_value'] = $requested_status;
			}
		}
		$query_result = wc_get_orders( $args );
		if ( $search ) {
			$orders = $query_result;
			self::$admin_total = count( $orders );
			self::$admin_max_pages = 1;
		} else {
			$orders = $query_result->orders;
			self::$admin_total = (int) $query_result->total;
			self::$admin_max_pages = max( 1, (int) $query_result->max_num_pages );
		}
		if ( $search ) {
			$phone = self::normalize_phone( $search );
			$orders = array_filter( $orders, static function( $order ) use ( $search, $phone ) {
				return false !== stripos( (string) $order->get_order_number(), $search ) || false !== stripos( $order->get_meta( self::META_CODE, true ), $search ) || ( $phone && self::normalize_phone( $order->get_billing_phone() ) === $phone ) || false !== stripos( $order->get_formatted_billing_full_name(), $search );
			} );
		}
		$order_map = array_flip( array_keys( self::statuses() ) );
		usort( $orders, static function( $a, $b ) use ( $order_map ) {
			$status_cmp = ( $order_map[ self::tracking_status( $a ) ] ?? 99 ) <=> ( $order_map[ self::tracking_status( $b ) ] ?? 99 );
			return $status_cmp ?: ( $b->get_date_created()->getTimestamp() <=> $a->get_date_created()->getTimestamp() );
		} );
		return $orders;
	}

	public static function admin_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'دسترسی غیرمجاز.' );
		$tab = sanitize_key( $_GET['tab'] ?? 'orders' );
		?>
		<div class="wrap clz-admin"><div class="clz-admin-hero"><div><span>مرکز عملیات فروش</span><h1>مدیریت و پیگیری سفارش‌ها</h1><p>وضعیت ارسال، بازه تحویل و پیامک‌های اطلاع‌رسانی را یک‌جا مدیریت کنید.</p></div><a href="<?php echo esc_url( get_permalink( absint( get_option( self::PAGE_OPTION ) ) ) ); ?>" target="_blank">مشاهده صفحه مشتری ↗</a></div>
		<?php if ( ! empty( $_GET['updated'] ) ) : ?><div class="notice notice-success is-dismissible"><p>تغییرات با موفقیت ذخیره شد.</p></div><?php endif; ?>
		<?php if ( ! empty( $_GET['sms_error'] ) ) : ?><div class="notice notice-error is-dismissible"><p>وضعیت ذخیره شد، اما ارسال پیامک ناموفق بود. جزئیات در یادداشت سفارش ثبت شده است.</p></div><?php endif; ?>
		<nav class="clz-admin-tabs"><a class="<?php echo 'orders' === $tab ? 'is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=clz-order-tracking' ) ); ?>">سفارش‌ها</a><a class="<?php echo 'sms' === $tab ? 'is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=clz-order-tracking&tab=sms' ) ); ?>">تنظیمات پیامک</a></nav>
		<?php if ( 'sms' === $tab ) self::admin_sms_settings(); else self::admin_orders_page(); ?>
		</div><?php
	}

	private static function admin_orders_page() {
		$orders = self::admin_orders();
		$statuses = self::statuses();
		?><form class="clz-admin-filters" method="get"><input type="hidden" name="page" value="clz-order-tracking"><label>جست‌وجو<input type="search" name="s" value="<?php echo esc_attr( wp_unslash( $_GET['s'] ?? '' ) ); ?>" placeholder="شماره سفارش، موبایل، نام یا کد"></label><label>وضعیت<select name="tracking_status"><option value="">همه وضعیت‌ها</option><?php foreach ( $statuses as $key => $status ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $_GET['tracking_status'] ?? '', $key ); ?>><?php echo esc_html( $status['label'] ); ?></option><?php endforeach; ?></select></label><label>از تاریخ<input type="date" name="from" value="<?php echo esc_attr( wp_unslash( $_GET['from'] ?? '' ) ); ?>"></label><label>تا تاریخ<input type="date" name="to" value="<?php echo esc_attr( wp_unslash( $_GET['to'] ?? '' ) ); ?>"></label><button>اعمال فیلتر</button></form>
		<div class="clz-admin-summary"><?php foreach ( $statuses as $key => $status ) : $count = count( array_filter( $orders, static function( $order ) use ( $key ) { return self::tracking_status( $order ) === $key; } ) ); ?><div style="--status-color:<?php echo esc_attr( $status['color'] ); ?>"><i><?php echo esc_html( $status['icon'] ); ?></i><span><?php echo esc_html( $status['label'] ); ?></span><strong><?php echo esc_html( number_format_i18n( $count ) ); ?></strong></div><?php endforeach; ?></div>
		<div class="clz-admin-orders"><?php if ( ! $orders ) : ?><div class="clz-admin-empty">سفارشی با این فیلترها پیدا نشد.</div><?php endif; foreach ( $orders as $order ) : self::ensure_order_meta( $order ); $status_key = self::tracking_status( $order ); ?>
		<form class="clz-admin-order" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="--status-color:<?php echo esc_attr( $statuses[ $status_key ]['color'] ); ?>"><input type="hidden" name="action" value="clz_update_tracking_order"><input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>"><?php wp_nonce_field( 'clz_update_tracking_' . $order->get_id() ); ?>
		<header><div class="clz-admin-order-id"><i><?php echo esc_html( $statuses[ $status_key ]['icon'] ); ?></i><div><small>سفارش</small><a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a></div></div><div><strong><?php echo esc_html( $order->get_formatted_billing_full_name() ?: 'مهمان' ); ?></strong><small dir="ltr"><?php echo esc_html( $order->get_billing_phone() ); ?></small></div><div><strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong><small><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'Y/m/d H:i' ) ); ?></small></div><code><?php echo esc_html( $order->get_meta( self::META_CODE, true ) ); ?></code></header>
		<div class="clz-admin-order-fields"><label>وضعیت رهگیری<select name="tracking_status"><?php foreach ( $statuses as $key => $status ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status_key, $key ); ?>><?php echo esc_html( $status['label'] ); ?></option><?php endforeach; ?></select></label><label>روز تحویل<input type="date" name="delivery_day" value="<?php echo esc_attr( $order->get_meta( self::META_DELIVERY_DAY, true ) ); ?>"></label><label>از ساعت<input type="time" name="window_from" value="<?php echo esc_attr( $order->get_meta( self::META_WINDOW_FROM, true ) ); ?>"></label><label>تا ساعت<input type="time" name="window_to" value="<?php echo esc_attr( $order->get_meta( self::META_WINDOW_TO, true ) ); ?>"></label><label class="clz-send-sms"><input type="checkbox" name="send_sms" value="1" checked><span>ارسال پیامک در صورت تغییر وضعیت</span></label><button type="submit">ذخیره تغییرات</button></div></form>
		<?php endforeach; ?></div>
		<?php if ( self::$admin_max_pages > 1 ) : ?><div class="clz-admin-pagination"><?php echo wp_kses_post( paginate_links( [ 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => max( 1, absint( $_GET['paged'] ?? 1 ) ), 'total' => self::$admin_max_pages, 'prev_text' => '→', 'next_text' => '←' ] ) ); ?></div><?php endif; ?>
		<?php
	}

	private static function admin_sms_settings() {
		$settings = self::settings();
		$credentials = self::melipayamak_credentials();
		?><div class="clz-sms-note <?php echo $credentials['username'] && $credentials['password'] ? 'is-ready' : 'is-error'; ?>"><i><?php echo $credentials['username'] && $credentials['password'] ? '✓' : '!'; ?></i><div><strong><?php echo $credentials['username'] && $credentials['password'] ? 'اتصال ملی‌پیامک آماده است' : 'اطلاعات ملی‌پیامک کامل نیست'; ?></strong><p>نام کاربری و رمز از تنظیمات پیامک قالب ← درگاه ملی‌پیامک خوانده می‌شود. در این صفحه فقط شناسه پترن و ترتیب متغیرها را مشخص کنید.</p></div></div>
		<form class="clz-sms-settings" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="clz_save_tracking_settings"><?php wp_nonce_field( 'clz_save_tracking_settings' ); ?>
		<section><header><div><span>احراز هویت مشتری</span><h2>پترن کد یک‌بارمصرف</h2></div></header><div class="clz-sms-grid"><label>شناسه پترن (Body ID)<input type="number" min="1" name="otp_pattern_id" value="<?php echo esc_attr( $settings['otp_pattern_id'] ); ?>" placeholder="مثلاً 12345"></label><label>رشته متغیرها<textarea name="otp_variables" dir="ltr"><?php echo esc_textarea( $settings['otp_variables'] ); ?></textarea></label></div><p class="description">پترن ملی‌پیامک باید متغیر کد را داشته باشد. متغیرهای مجاز: <code>{code}</code> و <code>{site_name}</code></p></section>
		<section><header><div><span>اطلاع‌رسانی خودکار</span><h2>پترن وضعیت‌های سفارش</h2></div><label class="clz-switch"><input type="checkbox" name="sms_enabled" value="1" <?php checked( $settings['sms_enabled'], '1' ); ?>><span></span>فعال</label></header><div class="clz-pattern-list"><?php foreach ( self::statuses() as $key => $status ) : ?><div style="--status-color:<?php echo esc_attr( $status['color'] ); ?>"><i><?php echo esc_html( $status['icon'] ); ?></i><strong><?php echo esc_html( $status['label'] ); ?></strong><label>Body ID<input type="number" min="1" name="pattern_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $settings[ 'pattern_' . $key ] ); ?>"></label><label>ترتیب متغیرها<input type="text" dir="ltr" name="variables_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $settings[ 'variables_' . $key ] ); ?>"></label></div><?php endforeach; ?></div><p class="description">متغیرهای مجاز: <code>{order_id}</code>، <code>{status}</code>، <code>{tracking_code}</code>، <code>{customer_name}</code>، <code>{delivery_date}</code>، <code>{delivery_window}</code> و <code>{site_name}</code>. ترتیب را دقیقاً مطابق پترن تأییدشده و با «;» وارد کنید.</p></section><button class="button button-primary button-hero" type="submit">ذخیره تنظیمات پیامک</button></form><?php
	}

	public static function admin_save_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'دسترسی غیرمجاز.' );
		check_admin_referer( 'clz_save_tracking_settings' );
		$settings = self::defaults();
		$settings['otp_pattern_id'] = (string) absint( $_POST['otp_pattern_id'] ?? 0 );
		$settings['otp_variables'] = sanitize_text_field( wp_unslash( $_POST['otp_variables'] ?? '{code}' ) );
		$settings['sms_enabled'] = ! empty( $_POST['sms_enabled'] ) ? '1' : '0';
		foreach ( self::statuses() as $key => $status ) {
			$settings[ 'pattern_' . $key ] = (string) absint( $_POST[ 'pattern_' . $key ] ?? 0 );
			$settings[ 'variables_' . $key ] = sanitize_text_field( wp_unslash( $_POST[ 'variables_' . $key ] ?? '' ) );
		}
		update_option( self::OPTION, $settings, false );
		wp_safe_redirect( admin_url( 'admin.php?page=clz-order-tracking&tab=sms&updated=1' ) ); exit;
	}

	public static function admin_update_order() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'دسترسی غیرمجاز.' );
		$order_id = absint( $_POST['order_id'] ?? 0 );
		check_admin_referer( 'clz_update_tracking_' . $order_id );
		$order = wc_get_order( $order_id );
		if ( ! $order ) wp_die( 'سفارش پیدا نشد.' );
		$new_status = sanitize_key( $_POST['tracking_status'] ?? '' );
		if ( ! array_key_exists( $new_status, self::statuses() ) ) wp_die( 'وضعیت نامعتبر است.' );
		$old_status = self::tracking_status( $order );
		$day = sanitize_text_field( wp_unslash( $_POST['delivery_day'] ?? '' ) );
		$from = sanitize_text_field( wp_unslash( $_POST['window_from'] ?? '' ) );
		$to = sanitize_text_field( wp_unslash( $_POST['window_to'] ?? '' ) );
		if ( $day ) {
			$day_parts = array_map( 'absint', explode( '-', $day ) );
			if ( 3 !== count( $day_parts ) || ! checkdate( $day_parts[1], $day_parts[2], $day_parts[0] ) ) $day = '';
		}
		if ( $from && ! preg_match( '/^\d{2}:\d{2}$/D', $from ) ) $from = '';
		if ( $to && ! preg_match( '/^\d{2}:\d{2}$/D', $to ) ) $to = '';
		if ( $from && $to && $from >= $to ) wp_die( 'ساعت پایان باید بعد از ساعت شروع باشد.', 'بازه تحویل نامعتبر', [ 'back_link' => true ] );
		self::ensure_order_meta( $order );
		$order->update_meta_data( self::META_STATUS, $new_status );
		$order->update_meta_data( self::META_DELIVERY_DAY, $day );
		$order->update_meta_data( self::META_WINDOW_FROM, $from );
		$order->update_meta_data( self::META_WINDOW_TO, $to );
		if ( $old_status !== $new_status ) {
			$history = (array) $order->get_meta( self::META_HISTORY, true );
			$history[] = [ 'status' => $new_status, 'at' => current_time( 'mysql' ), 'user_id' => get_current_user_id(), 'delivery_day' => $day, 'window_from' => $from, 'window_to' => $to ];
			$order->update_meta_data( self::META_HISTORY, array_slice( $history, -30 ) );
		}
		$order->save();
		$sms_result = null;
		if ( $old_status !== $new_status && ! empty( $_POST['send_sms'] ) ) {
			$sms_result = self::send_status_sms( $order, $new_status );
			$order->add_order_note( is_wp_error( $sms_result ) ? 'خطا در پیامک وضعیت: ' . $sms_result->get_error_message() : 'پیامک وضعیت «' . self::statuses()[ $new_status ]['label'] . '» برای مشتری ارسال شد.' );
		}
		$url = admin_url( 'admin.php?page=clz-order-tracking&updated=1' . ( is_wp_error( $sms_result ) ? '&sms_error=1' : '' ) );
		wp_safe_redirect( $url ); exit;
	}

	private static function send_status_sms( $order, $status_key ) {
		$settings = self::settings();
		if ( '1' !== $settings['sms_enabled'] ) return new WP_Error( 'sms_disabled', 'پیامک وضعیت غیرفعال است.' );
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
