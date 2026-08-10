<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\Product;
use Bijan\Utils\UI;
use Bijan\Utils\WC;

add_filter( 'woocommerce_enqueue_styles', '__return_false' ); // Disable all wc styles

include( BIJAN_DIR . "woocommerce/InstantDiscount.php" );

if( !function_exists( "bijan_wc_currency_symbol" ) ) {
	function bijan_wc_currency_symbol( $currency_symbol, $currency ) {
		if( get_locale() != 'fa_IR' ) return $currency_symbol;
		if( defined( "REST_REQUEST" ) && REST_REQUEST ) return $currency_symbol;

		$options = Options::get_options( [
			'custom_toman'			=> true,
			'custom_toman_style'	=> 'toman',
		] );
		if( !$options['custom_toman'] ) return $currency_symbol;

		$restricted_filters = ['dokan_can_post'];
		foreach( $restricted_filters as $filter ) {
			if( did_filter( $filter ) ) return $currency_symbol;
		}

		if(
			( ( is_admin() && wp_doing_ajax() ) || !is_admin() ) &&
			WC::apply_custom_toman()
		) {
			if( $currency == "IRT" ) {
				$currency_symbol = '<span class="currency-symbol-text">' . $currency_symbol . '</span><svg class="bijan-' . $options['custom_toman_style'] . '"><use xlink:href="#bijan_toman"></use></svg>';
			}
		}
		return $currency_symbol;
	}
}
add_filter( 'woocommerce_currency_symbol', 'bijan_wc_currency_symbol', 10, 2 );

if( !function_exists( "bijan_wc_currency_symbol_svg" ) ) {
	function bijan_wc_currency_symbol_svg() {
		if( !Utils::is_wc_active() ) return;
		if( get_woocommerce_currency() !== 'IRT' ) return;

		$options = Options::get_options( [
			'custom_toman'			=> true,
			'custom_toman_style'	=> 'toman',
		] );
		if( !$options['custom_toman'] ) return;

		$symbol_attrs = [
			'toman'		=> [
				'width'		=> 25,
				'height'	=> 21
			],
			'toman2'	=> [
				'width'		=> 32,
				'height'	=> 18,
				'viewbox'	=> '0 0 32 18'
			],
			'toman3'	=> [
				'width'		=> 20,
				'height'	=> 20,
				'viewbox'	=> '0 0 20 20'
			],
		];
		?>
		<svg style="display:none!important">
			<symbol <?php echo Utils::get_html_attributes( $symbol_attrs[$options['custom_toman_style']] ) ?> id="bijan_toman"><?php echo file_get_contents( BIJAN_DIR . "assets/img/{$options['custom_toman_style']}.svg" ) ?></symbol>
		</svg>
		<?php
	}
}
add_action( 'wp_body_open', 'bijan_wc_currency_symbol_svg', 99 );

if( !function_exists( "bijan_wc_product_classes" ) ) {
	function bijan_wc_product_classes( $classes ) {
		$classes[] = 'slider-slide';

		$props = Product::get_loop_props();
		if( !empty( $props['special_products'] ) ) {
			$classes[] = 'special-product';
			$classes[] = 'bijan-title-wrap';
		}

		return $classes;
	}
}
add_filter( 'woocommerce_post_class', 'bijan_wc_product_classes', 99 );

if( !function_exists( "bijan_wc_return_to_shop_text" ) ) {
	function bijan_wc_return_to_shop_text( $text ) {
		return Options::get_options( [
			'wc_return_to_shop_text'	=> __( 'Return to shop', 'bijan' )
		] )['wc_return_to_shop_text'];
	}
}
add_filter( 'woocommerce_return_to_shop_text', 'bijan_wc_return_to_shop_text' );

// Change variable products price range
if( !function_exists( "bijan_wc_format_price_range" ) ) {
	function bijan_wc_format_price_range( $price_html, $from, $to ) {
		$price_html = '<div class="price-range-wrap">';
			$price_html .= '<div class="price-range price-range-from">';
				$price_html .= '<div class="price-range-label">' . esc_html_x( 'From', 'Price range', 'bijan' ) . '</div>';
				$price_html .= is_numeric( $from ) ? wc_price( $from ) : $from;
			$price_html .= '</div>';
			$price_html .= '<div class="price-range price-range-to">';
				$price_html .= '<div class="price-range-label">' . esc_html_x( 'To', 'Price range', 'bijan' ) . '</div>';
				$price_html .= is_numeric( $to ) ? wc_price( $to ) : $to;
			$price_html .= '</div>';
		$price_html .= '</div>';
		return $price_html;
	}
}
add_filter( 'woocommerce_format_price_range', 'bijan_wc_format_price_range', 10, 3 );

if( !function_exists( "bijan_wc_star_rating_html" ) ) {
	function bijan_wc_star_rating_html( $html, $rating, $count ) {
		$count = $count === 0 ? 5 : $count;
		return UI::stars( $rating, $count, false, '', false );
	}
}
add_filter( 'woocommerce_get_star_rating_html', 'bijan_wc_star_rating_html', 10, 3 );

if( !function_exists( "bijan_wc_sku_status" ) ) {
	function bijan_wc_sku_status( $status ) {
		return Utils::to_bool( Options::get_options( ['sku_status' => true] )['sku_status'] );
	}
}
add_filter( 'wc_product_sku_enabled', 'bijan_wc_sku_status' );

if( !function_exists( "bijan_wc_modify_admin_access" ) ) {
	function bijan_wc_modify_admin_access( $prevent ) {
		if( $prevent ) {
			$additional_files = ['async-upload.php'];

			$file = basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) );
			if( in_array( $file, $additional_files ) ) {
				$prevent = false;
			}
		}

		return $prevent;
	}
}
add_filter( 'woocommerce_prevent_admin_access', 'bijan_wc_modify_admin_access', 99 );

include( BIJAN_DIR . "woocommerce/Functions/Archive.php" );
include( BIJAN_DIR . "woocommerce/Functions/Cart.php" );
include( BIJAN_DIR . "woocommerce/Functions/Checkout.php" );
include( BIJAN_DIR . "woocommerce/Functions/Filters.php" );
include( BIJAN_DIR . "woocommerce/Functions/MyAccount.php" );
include( BIJAN_DIR . "woocommerce/Functions/Single.php" );
include( BIJAN_DIR . "woocommerce/Functions/PriceHistory.php" );