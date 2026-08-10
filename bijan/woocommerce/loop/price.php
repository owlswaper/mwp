<?php
/**
 * Loop Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

use Bijan\Utils;
use Bijan\Utils\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

if( $product->get_stock_status() !== 'outofstock' && $price_html = $product->get_price_html() ) {
	echo '<span class="price">' . $price_html . '</span>';
} else {
	$options = Options::get_options( [
		'wc-show-stock-status'	=> true,
	] );
	if( Utils::to_bool( $options['wc-show-stock-status'] ) ) {
		echo wc_get_stock_html( $product );
	}
}