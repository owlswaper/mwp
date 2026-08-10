<?php

use Bijan\Utils;
use Bijan\Utils\Options;

// Checkout
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );
add_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 1 );
if( !function_exists( 'bijan_wc_gateway_icons' ) ) {
	function bijan_wc_gateway_icons( $title, $gateway_id ) {
		$icons = [
			'bacs'		=> [
				'normal'	=> 'bijan-icon-card',
			],
			'cheque'	=> [
				'normal'	=> 'bijan-icon-cheque',
			],
			'cod'		=> [
				'normal'	=> 'bijan-icon-house',
			],
		];
		if( in_array( $gateway_id, array_keys( $icons ) ) ) {
			$title .= '<i class="payment_gateway-icon ' . $icons[$gateway_id]['normal'] . '"></i>';
		}

		return $title;
	}
}
add_filter( 'woocommerce_gateway_title', 'bijan_wc_gateway_icons', 10, 2 );

if( !function_exists( 'bijan_wc_order_get_formatted_billing_address' ) ) {
	function bijan_wc_order_get_formatted_billing_address( $address, $raw_address ) {
		if( is_admin() ) return $address;

		return WC()->countries->get_formatted_address( $raw_address, ' - ' );
	}
}
add_filter( 'woocommerce_order_get_formatted_billing_address', 'bijan_wc_order_get_formatted_billing_address', 10, 2 );

if( !function_exists( 'bijan_wc_order_get_formatted_shipping_address' ) ) {
	function bijan_wc_order_get_formatted_shipping_address( $address, $raw_address ) {
		if( is_admin() ) return $address;

		return WC()->countries->get_formatted_address( $raw_address, ' - ' );
	}
}
add_filter( 'woocommerce_order_get_formatted_shipping_address', 'bijan_wc_order_get_formatted_shipping_address', 10, 2 );

if( !function_exists( 'bijan_wc_order_customer_address_icon' ) ) {
	function bijan_wc_order_customer_address_icon( $type ) {
		if( $type === 'billing' ) {
			$icon = 'bijan-icon-location';
		} else {
			$icon = 'bijan-icon-delivery';
		}
		?>
		<div class="order-address-icon-wrap">
			<i class="order-address-icon <?php echo $icon ?>"></i>
		</div>
		<?php
	}
}
add_action( 'woocommerce_order_details_after_customer_address', 'bijan_wc_order_customer_address_icon' );

if( !function_exists( "bijan_wc_account_checkout_fields" ) ) {
	function bijan_wc_account_checkout_fields( $fields ) {
		$options = Options::get_options( [
			'auth'	=> true,
		] );
		if( Utils::to_bool( $options['auth'] ) ) {
			$fields['account'] = [];
		}
		return $fields;
	}
}
add_filter( 'woocommerce_checkout_fields', 'bijan_wc_account_checkout_fields', 100 );