<?php
namespace Bijan\Shortcodes;

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$prefix = '';
if( isset( $args["prefix"] ) ) {
	$prefix = $args["prefix"];
}

$defaults = [
	$prefix . 'market'		=> '',
	$prefix . 'custom_icon'	=> 0,
	$prefix . 'top_text'	=> '',
	$prefix . 'text'		=> '',
	$prefix . 'link'		=> '#',
	$prefix . 'size'		=> 'full', // full | auto
];
$args = Utils::check_default( $args, $defaults, [$prefix . 'link'] );

// Sanitize
$market = Utils::ensure_values_in_array( Utils::convert_chars( $args[$prefix . 'market'] ), array_keys( Utils::app_markets() ), 'custom' );
$custom_icon = Utils::convert_chars( $args[$prefix . 'custom_icon'], true, 'absint' );
$top_text = wp_kses_post( $args[$prefix . 'top_text'] );
$text = wp_kses_post( $args[$prefix . 'text'] );
$size = Utils::ensure_values_in_array( Utils::convert_chars( $args[$prefix . 'size'] ), ['full', 'auto'], 'full' );

if( !is_array( $args[$prefix . 'link'] ) ) {
	$args[$prefix . 'link'] = [
		'url'				=> $args[$prefix . 'link'],
		'is_external'		=> false,
		'nofollow'			=> false,
		'custom_attributes'	=> '',
	];
}

$icon = '';
if( $market === 'custom' ) {
	$icon = wp_get_attachment_image( $custom_icon );
} else {
	$icon = file_exists( BIJAN_DIR . "assets/img/app-markets/{$market}.svg" ) ? BIJAN_URI . "assets/img/app-markets/{$market}.svg" : BIJAN_URI . "assets/img/app-markets/{$market}.png";
	$icon = '<img src="' . $icon . '" alt="' . esc_attr( wp_strip_all_tags( $text ) ) . '">';
}

$classes = ['market-button', "market-button-{$size}"];
?>
<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args[$prefix . 'link'] ) ) ?> class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
	<div class="market-button-icon-wrap"><?php echo $icon ?></div>
	<div class="market-button-texts">
		<div class="market-button-top-text"><?php echo $top_text ?></div>
		<div class="market-button-text"><?php echo $text ?></div>
	</div>
</a>