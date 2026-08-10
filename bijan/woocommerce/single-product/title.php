<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://woocommerce.com/document/template-structure/
 * @package    WooCommerce\Templates
 * @version    1.6.4
 */

use Bijan\Utils\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$options = Options::get_options( [
	'product-title-tag'	=> 'h1',
] );

the_title( '<' . tag_escape( $options['product-title-tag'] ) . ' class="product_title entry-title show-only-desktop-1024">', '</' . tag_escape( $options['product-title-tag'] ) . '>' );
