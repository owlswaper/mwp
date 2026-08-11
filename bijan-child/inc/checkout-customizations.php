<?php
/**
 * Checkout usability and presentation customizations.
 *
 * Kept in the child theme so parent theme updates cannot overwrite them.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the checkout-only stylesheet after both parent and child styles.
 */
function clz_enqueue_checkout_styles() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return;
	}

	$file = trailingslashit( get_stylesheet_directory() ) . 'assets/checkout.css';
	$url  = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/checkout.css';

	wp_enqueue_style(
		'clz-checkout',
		$url,
		array( 'bijan-child-style' ),
		file_exists( $file ) ? (string) filemtime( $file ) : BIJAN_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'clz_enqueue_checkout_styles', 30 );

/**
 * Iran is the store's only checkout country. Keep the native country select in
 * the DOM for WooCommerce's country/state scripts, but hide its field visually.
 */
function clz_checkout_iran_country_field( $fields ) {
	foreach ( array( 'billing_country', 'shipping_country' ) as $key ) {
		$section = 0 === strpos( $key, 'billing_' ) ? 'billing' : 'shipping';

		if ( ! isset( $fields[ $section ][ $key ] ) ) {
			continue;
		}

		$fields[ $section ][ $key ]['default'] = 'IR';
		$fields[ $section ][ $key ]['class']   = array_values(
			array_unique(
				array_merge(
					isset( $fields[ $section ][ $key ]['class'] ) ? (array) $fields[ $section ][ $key ]['class'] : array(),
					array( 'clz-country-field' )
				)
			)
		);
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'clz_checkout_iran_country_field', 999 );

/**
 * Use Iran before customer metadata is read, including for returning users.
 */
function clz_checkout_country_value( $value, $input ) {
	if ( in_array( $input, array( 'billing_country', 'shipping_country' ), true ) ) {
		return 'IR';
	}

	return $value;
}
add_filter( 'woocommerce_checkout_get_value', 'clz_checkout_country_value', 20, 2 );
add_filter( 'default_checkout_billing_country', 'clz_checkout_default_iran' );
add_filter( 'default_checkout_shipping_country', 'clz_checkout_default_iran' );

function clz_checkout_default_iran() {
	return 'IR';
}

/**
 * Do not trust a modified browser request to change the checkout country.
 */
function clz_force_iran_in_checkout_data( $data ) {
	$data['billing_country']  = 'IR';
	$data['shipping_country'] = 'IR';

	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'clz_force_iran_in_checkout_data', 20 );
