<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) {
	return;
}

?>
<div class="woocommerce-form-coupon-toggle">
	<?php
	wc_print_notice(
		apply_filters(
			'woocommerce_checkout_coupon_message',
			'🎁 <strong>کد تخفیف دارید؟</strong> <a href="#" class="showcoupon">برای وارد کردن کد اینجا کلیک کنید.</a>'
		),
		'notice'
	);
	?>

	<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none">

		<p>اگر کد تخفیف یا کارت هدیه دارید، آن را در کادر زیر وارد کرده و روی دکمه <strong>اعمال تخفیف</strong> بزنید.</p>

		<p class="form-row form-row-first">
			<label for="coupon_code" class="screen-reader-text">کد تخفیف</label>

			<input
				type="text"
				name="coupon_code"
				class="input-text"
				placeholder="کد تخفیف را وارد کنید..."
				id="coupon_code"
				value=""
			/>
		</p>

		<p class="form-row form-row-last">
			<button
				type="submit"
				class="button button-gray rounded<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"
				name="apply_coupon"
				value="اعمال تخفیف"
			>
				اعمال تخفیف
			</button>
		</p>

		<div class="clear"></div>

	</form>
</div>