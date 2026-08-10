<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$prefix = '';
if( isset( $args["prefix"] ) ) {
	$prefix = $args["prefix"];
}
$args = Elementor::check_button_defaults( $args, $prefix );

$args["{$prefix}type"] = Utils::ensure_values_in_array( Utils::convert_chars( $args["{$prefix}type"] ), array_keys( Elementor::button_types() ), 'primary' );
$args["{$prefix}icon"] = Utils::get_icon( $args["{$prefix}icon"], 'button-icon' );
$args["{$prefix}icon_align"] = Utils::ensure_values_in_array( Utils::convert_chars( $args["{$prefix}icon_align"] ), ['left', 'right'], 'left' );
$args["{$prefix}style"] = Utils::ensure_values_in_array( Utils::convert_chars( $args["{$prefix}style"] ), array_keys( Elementor::button_styles() ), 'normal' );
$args["{$prefix}text"] = wp_kses_post( $args["{$prefix}text"] );
$args["{$prefix}align"] = Utils::ensure_values_in_array( Utils::convert_chars( $args["{$prefix}align"] ), ['left', 'center', 'right'], 'left' );
$args["{$prefix}id"] = Utils::convert_chars( $args["{$prefix}id"] );

$classes = ["button", "button-{$args["{$prefix}align"]}", $args["{$prefix}style"]];
if( Utils::to_bool( $args["{$prefix}transparent"] ) ) {
	$classes[] = "button-transparent";
} else {
	$classes[] = "button-{$args["{$prefix}type"]}";
}
if( Utils::to_bool( $args["{$prefix}small"] ) ) {
	$classes[] = "small";
}
if( Utils::to_bool( $args["{$prefix}disabled"] ) ) {
	$classes[] = "disabled";
}
if( Utils::to_bool( $args["{$prefix}loading"] ) ) {
	$classes[] = "button-has-loading";
}

$args["{$prefix}classes"] = Utils::prepare_html_classes( array_merge( $classes, $args["{$prefix}classes"] ) );

if( Elementor::has_link( $args["{$prefix}link"] ) ) {
	get_template_part( "templates/components/button/a", null, $args );
} else {
	get_template_part( "templates/components/button/button", null, $args );
}