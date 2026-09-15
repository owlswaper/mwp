<?php
/**
 * A single, lightweight AJAX add-to-cart flow for every WooCommerce surface.
 *
 * @package BijanChild
 */

defined( 'ABSPATH' ) || exit;

final class Cloz_Ajax_Cart {
	const ACTION = 'cloz_add_to_cart';

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 30 );
		add_action( 'wp_footer', array( __CLASS__, 'render_toast' ), 50 );
		add_action( 'wc_ajax_' . self::ACTION, array( __CLASS__, 'add_to_cart' ) );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'make_loop_button_link_free' ), PHP_INT_MAX, 3 );
	}

	public static function enqueue_assets() {
		if ( is_admin() || ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_AJAX' ) ) {
			return;
		}

		$version = wp_get_theme()->get( 'Version' );
		$base    = trailingslashit( get_stylesheet_directory_uri() );

		wp_enqueue_style( 'cloz-ajax-cart', $base . 'assets/ajax-cart.css', array(), $version );
		wp_enqueue_script( 'cloz-ajax-cart', $base . 'assets/ajax-cart.js', array( 'jquery' ), $version, true );
		wp_localize_script(
			'cloz-ajax-cart',
			'ClozAjaxCart',
			array(
				'endpoint'        => WC_AJAX::get_endpoint( self::ACTION ),
				'cartUrl'         => wc_get_cart_url(),
				'timeout'         => 5000,
				'chooseOptions'   => 'لطفاً گزینه‌های محصول را انتخاب کنید.',
				'genericError'    => 'افزودن محصول انجام نشد. لطفاً دوباره تلاش کنید.',
				'successTitle'    => 'به سبد خرید اضافه شد',
				'errorTitle'      => 'افزودن به سبد خرید انجام نشد',
				'viewCart'        => 'مشاهده سبد خرید',
				'close'           => 'بستن پیام',
				'working'         => 'در حال افزودن به سبد خرید',
			)
		);
	}

	/**
	 * Direct-add loop controls must be buttons, not crawlable ?add-to-cart links.
	 * Variable/external products retain their product URL because a selection or
	 * an external visit is required before they can be purchased.
	 */
	public static function make_loop_button_link_free( $html, $product, $args ) {
		if ( ! $product instanceof WC_Product || ! $product->supports( 'ajax_add_to_cart' ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return $html;
		}

		$html = preg_replace( '/<a\b/i', '<button type="button"', $html, 1 );
		$html = preg_replace( '/\s+href=("[^"]*"|\'[^\']*\')/i', '', $html, 1 );
		$html = preg_replace( '/\s+rel=("[^"]*"|\'[^\']*\')/i', '', $html, 1 );
		$html = preg_replace( '/<\/a>\s*$/i', '</button>', $html, 1 );

		if ( false === strpos( $html, 'cloz-ajax-add-to-cart' ) ) {
			$html = preg_replace( '/class=("|\')/i', 'class=$1cloz-ajax-add-to-cart ', $html, 1 );
		}
		if ( false === strpos( $html, 'data-product_id=' ) ) {
			$html = preg_replace( '/<button\b/i', '<button data-product_id="' . esc_attr( $product->get_id() ) . '"', $html, 1 );
		}

		return $html;
	}

	public static function add_to_cart() {
		nocache_headers();

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			self::send_error( 'سبد خرید در دسترس نیست. لطفاً صفحه را تازه کنید.' );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		if ( ! $product_id && isset( $_POST['add-to-cart'] ) ) {
			$product_id = absint( wp_unslash( $_POST['add-to-cart'] ) );
		}
		$product_id = absint( apply_filters( 'woocommerce_add_to_cart_product_id', $product_id ) );

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			self::send_error( 'محصول انتخاب‌شده پیدا نشد.' );
		}

		try {
			if ( isset( $_POST['quantity'] ) && is_array( $_POST['quantity'] ) ) {
				$added_keys = self::add_grouped_products( $product_id );
			} else {
				$added_keys = array( self::add_one_product( $product_id ) );
			}
		} catch ( Exception $exception ) {
			self::send_error( $exception->getMessage() );
		}

		$added_keys = array_values( array_filter( $added_keys ) );
		if ( empty( $added_keys ) ) {
			self::send_error();
		}

		$primary_key = end( $added_keys );
		$cart_item   = WC()->cart->get_cart_item( $primary_key );
		$item        = self::get_item_payload( $cart_item, count( $added_keys ) );

		do_action( 'woocommerce_ajax_added_to_cart', $product_id );
		wc_clear_notices();

		wp_send_json_success(
			array(
				'fragments' => self::get_fragments(),
				'cart_hash' => WC()->cart->get_cart_hash(),
				'item'      => $item,
			)
		);
	}

	private static function add_one_product( $product_id ) {
		$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
		$variation    = array();
		$product      = wc_get_product( $product_id );

		if ( $product && $product->is_type( 'variation' ) ) {
			$variation_id = $product_id;
			$product_id   = $product->get_parent_id();
		}

		foreach ( $_POST as $key => $value ) {
			$key = wc_clean( wp_unslash( $key ) );
			if ( 0 === strpos( $key, 'attribute_' ) && ! is_array( $value ) ) {
				$variation[ $key ] = wc_clean( wp_unslash( $value ) );
			}
		}

		if ( $quantity <= 0 ) {
			throw new Exception( 'تعداد محصول معتبر نیست.' );
		}

		$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation );
		if ( ! $passed ) {
			return false;
		}

		$key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
		if ( $key ) {
			do_action( 'internal_woocommerce_cart_item_added_from_user_request', $variation_id ? $variation_id : $product_id, $quantity );
		}

		return $key;
	}

	private static function add_grouped_products( $group_id ) {
		$quantities = wc_clean( wp_unslash( $_POST['quantity'] ) );
		$to_add     = array();

		foreach ( $quantities as $child_id => $quantity ) {
			$child_id = absint( $child_id );
			$quantity = wc_stock_amount( $quantity );
			$child    = wc_get_product( $child_id );

			if ( $quantity > 0 && $child && $child->is_purchasable() && $child->is_in_stock() ) {
				$to_add[ $child_id ] = $quantity;
			}
		}

		if ( empty( $to_add ) ) {
			throw new Exception( 'حداقل یک محصول را برای افزودن انتخاب کنید.' );
		}

		foreach ( $to_add as $child_id => $quantity ) {
			if ( ! apply_filters( 'woocommerce_add_to_cart_validation', true, $child_id, $quantity, 0, array(), $group_id ) ) {
				return array();
			}
		}

		$keys = array();
		foreach ( $to_add as $child_id => $quantity ) {
			$key = WC()->cart->add_to_cart( $child_id, $quantity );
			if ( $key ) {
				$keys[] = $key;
				do_action( 'internal_woocommerce_cart_item_added_from_user_request', $child_id, $quantity );
			}
		}

		return $keys;
	}

	private static function get_item_payload( $cart_item, $added_count ) {
		$product = ! empty( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : false;

		if ( ! $product ) {
			return array(
				'name'     => 'محصول',
				'image'    => wc_placeholder_img_src(),
				'quantity' => 1,
				'meta'     => '',
				'count'    => $added_count,
			);
		}

		$image_id = $product->get_image_id();
		$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : '';

		return array(
			'name'     => wp_strip_all_tags( $product->get_name() ),
			'image'    => $image ? $image : wc_placeholder_img_src(),
			'quantity' => isset( $cart_item['quantity'] ) ? wc_stock_amount( $cart_item['quantity'] ) : 1,
			'meta'     => wp_strip_all_tags( wc_get_formatted_cart_item_data( $cart_item, true ) ),
			'count'    => $added_count,
		);
	}

	private static function get_fragments() {
		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		return apply_filters(
			'woocommerce_add_to_cart_fragments',
			array(
				'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
			)
		);
	}

	private static function send_error( $fallback = '' ) {
		$messages = array();
		foreach ( wc_get_notices( 'error' ) as $notice ) {
			$message = is_array( $notice ) && isset( $notice['notice'] ) ? $notice['notice'] : $notice;
			if ( is_string( $message ) ) {
				$messages[] = wp_strip_all_tags( $message );
			}
		}
		wc_clear_notices();

		$message = implode( ' ', array_filter( $messages ) );
		if ( ! $message ) {
			$message = $fallback ? wp_strip_all_tags( $fallback ) : 'افزودن محصول انجام نشد. لطفاً دوباره تلاش کنید.';
		}

		wp_send_json_error( array( 'message' => $message ) );
	}

	public static function render_toast() {
		if ( is_admin() || ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		?>
		<div id="cloz-cart-toast-region" class="cloz-cart-toast-region" aria-live="polite" aria-atomic="true">
			<aside id="cloz-cart-toast" class="cloz-cart-toast" aria-hidden="true">
				<button type="button" class="cloz-cart-toast__close" aria-label="<?php echo esc_attr( 'بستن پیام' ); ?>">&times;</button>
				<div class="cloz-cart-toast__visual">
					<img class="cloz-cart-toast__image" src="" alt="" width="72" height="72">
					<span class="cloz-cart-toast__status" aria-hidden="true">&#10003;</span>
				</div>
				<div class="cloz-cart-toast__content">
					<strong class="cloz-cart-toast__title">به سبد خرید اضافه شد</strong>
					<span class="cloz-cart-toast__product"></span>
					<span class="cloz-cart-toast__meta"></span>
				</div>
				<a class="cloz-cart-toast__action" href="<?php echo esc_url( wc_get_cart_url() ); ?>">مشاهده سبد خرید</a>
				<span class="cloz-cart-toast__progress" aria-hidden="true"></span>
			</aside>
		</div>
		<?php
	}
}

Cloz_Ajax_Cart::init();
