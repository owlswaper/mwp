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

	$script_file = trailingslashit( get_stylesheet_directory() ) . 'assets/checkout.js';
	$script_url  = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/checkout.js';

	wp_enqueue_script(
		'clz-checkout',
		$script_url,
		array( 'jquery', 'wc-checkout', 'wc-country-select' ),
		file_exists( $script_file ) ? (string) filemtime( $script_file ) : BIJAN_CHILD_VERSION,
		true
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

	$address_fields = array(
		'first_name' => array( 'priority' => 10, 'autocomplete' => 'given-name' ),
		'last_name'  => array( 'priority' => 20, 'autocomplete' => 'family-name' ),
		'phone'      => array( 'priority' => 30, 'autocomplete' => 'tel' ),
		'email'      => array( 'priority' => 40, 'autocomplete' => 'email' ),
		'state'      => array( 'priority' => 50, 'autocomplete' => 'address-level1' ),
		'city'       => array( 'priority' => 60, 'autocomplete' => 'address-level2' ),
		'address_1'  => array( 'priority' => 70, 'autocomplete' => 'street-address' ),
		'address_2'  => array( 'priority' => 75, 'autocomplete' => 'address-line2' ),
		'postcode'   => array( 'priority' => 80, 'autocomplete' => 'postal-code' ),
	);

	foreach ( array( 'billing', 'shipping' ) as $section ) {
		foreach ( $address_fields as $name => $settings ) {
			$key = $section . '_' . $name;
			if ( ! isset( $fields[ $section ][ $key ] ) ) {
				continue;
			}
			$fields[ $section ][ $key ]['priority']     = $settings['priority'];
			$fields[ $section ][ $key ]['autocomplete'] = $settings['autocomplete'];
		}
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
	$location_sync_enabled = isset( $_POST['clz_location_sync'] )
		&& ! is_array( $_POST['clz_location_sync'] )
		&& '1' === wc_clean( wp_unslash( $_POST['clz_location_sync'] ) );
	if ( ! $location_sync_enabled ) {
		return $data;
	}

	// Some browsers and address plugins briefly replace the city/state control
	// after autofill. The companion script keeps the last real control value in
	// shadow fields so a visibly selected location cannot be submitted as empty.
	foreach ( array( 'billing', 'shipping' ) as $section ) {
		$state_key        = $section . '_state';
		$city_key         = $section . '_city';
		$backup_state_key = 'clz_' . $state_key;
		$backup_city_key  = 'clz_' . $city_key;
		$city_state_key   = 'clz_' . $section . '_city_state';

		$backup_state = isset( $_POST[ $backup_state_key ] ) && ! is_array( $_POST[ $backup_state_key ] )
			? wc_clean( wp_unslash( $_POST[ $backup_state_key ] ) )
			: '';
		$backup_city = isset( $_POST[ $backup_city_key ] ) && ! is_array( $_POST[ $backup_city_key ] )
			? wc_clean( wp_unslash( $_POST[ $backup_city_key ] ) )
			: '';
		$city_state = isset( $_POST[ $city_state_key ] ) && ! is_array( $_POST[ $city_state_key ] )
			? wc_clean( wp_unslash( $_POST[ $city_state_key ] ) )
			: '';

		if ( empty( $data[ $state_key ] ) && '' !== $backup_state ) {
			$data[ $state_key ]     = $backup_state;
			$_POST[ $state_key ]    = $backup_state;
		}

		$current_state = isset( $data[ $state_key ] ) ? (string) $data[ $state_key ] : '';
		$same_state    = '' === $city_state || '' === $current_state || $city_state === $current_state;
		if ( empty( $data[ $city_key ] ) && '' !== $backup_city && $same_state ) {
			$data[ $city_key ]  = $backup_city;
			$_POST[ $city_key ] = $backup_city;
		}
	}

	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'clz_force_iran_in_checkout_data', 999 );

/**
 * Render non-authoritative shadow values used only if an autofilled control is
 * destroyed and recreated before WooCommerce serializes the checkout form.
 */
function clz_checkout_location_shadow_fields( $checkout, $section ) {
	foreach ( array( 'state', 'city' ) as $part ) {
		$key   = $section . '_' . $part;
		$value = is_object( $checkout ) && is_callable( array( $checkout, 'get_value' ) )
			? $checkout->get_value( $key )
			: '';
		printf(
			'<input type="hidden" name="clz_%1$s" id="clz_%1$s" value="%2$s" autocomplete="off">',
			esc_attr( $key ),
			esc_attr( $value )
		);
	}

	printf(
		'<input type="hidden" name="clz_%1$s_city_state" id="clz_%1$s_city_state" value="%2$s" autocomplete="off">',
		esc_attr( $section ),
		esc_attr( is_object( $checkout ) && is_callable( array( $checkout, 'get_value' ) ) ? $checkout->get_value( $section . '_state' ) : '' )
	);
}

function clz_checkout_billing_shadow_fields( $checkout ) {
	clz_checkout_location_shadow_fields( $checkout, 'billing' );
	echo '<input type="hidden" name="clz_location_sync" id="clz_location_sync" value="0" autocomplete="off">';
}
add_action( 'woocommerce_after_checkout_billing_form', 'clz_checkout_billing_shadow_fields', 99 );

function clz_checkout_shipping_shadow_fields( $checkout ) {
	clz_checkout_location_shadow_fields( $checkout, 'shipping' );
}
add_action( 'woocommerce_after_checkout_shipping_form', 'clz_checkout_shipping_shadow_fields', 99 );
