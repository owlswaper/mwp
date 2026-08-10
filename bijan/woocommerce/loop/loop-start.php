<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.3.0
 */

use Bijan\Utils;
use Bijan\Utils\Elementor;
use Bijan\Utils\Options;
use Bijan\Utils\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$settings = Options::get_options( [
	'default_wc_products_style'	=> 'products-style-1',
] );

$props = Product::get_loop_props();

if( isset( $_GET['special-products'] ) ) {
	$props['only_on_sales'] = true;
	$props['style'] = 'products-style-3';
	$props['special_products'] = true;
}

wc_set_loop_prop( 'bijan_loop_props', $props );

$display_attributes = Elementor::get_display_attributes( $props );

$attributes = [
	'class'			=> [
		'bijan-slider-wrap',
		'bijan-products-slider',
		"{$props['style']}-wrap",
	],
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
$attributes['class'] = array_merge( $attributes['class'], $display_attributes['wrap_classes'] );
if( empty( $props['show_arrows'] ) ) {
	$attributes['class'][] = 'bijan-slider-hidden-arrows';
}

if( $props['second-image-hover-show'] ) {
	$attributes['class'][] = 'second-image-hover-show';
}

$list_attributes = [
	'class'	=> array_merge( ["products", "wrapper", $props['style']], $display_attributes['classes'] ),
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php if( $props['special_products'] || !empty( $props['show_arrows'] ) ) { ?>
		<?php get_template_part( "templates/components/slider_arrows" ) ?>
	<?php } ?>
	<ul <?php echo Utils::get_html_attributes( $list_attributes ) ?>>
