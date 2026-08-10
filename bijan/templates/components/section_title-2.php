<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;

$defaults = [
	'icon'		=> '',
	'tag'		=> 'h2',
	'title'		=> '',
	'link'		=> [],
	'subtitle'	=> '',
	'align'		=> is_rtl() ? 'right' : 'left',
];
$args = Utils::check_default( $args, $defaults, ['icon'] );

// Sanitize
$custom_tag = Utils::ensure_values_in_array( Utils::convert_chars( $args['tag'] ), array_keys( Utils::custom_tags() ), 'h2' );
$icon = WhiteboxUtils::get_icon( $args['icon'], 'section-title-2-icon' );
$title = wp_kses_post( $args['title'] );
$subtitle = wp_kses_post( $args['subtitle'] );
$align = Utils::ensure_values_in_array( Utils::convert_chars( $args["align"] ), ['left', 'center', 'right'], $defaults["align"] );
if( !empty( $args['align_tablet'] ) ) {
	$align_tablet = Utils::ensure_values_in_array( Utils::convert_chars( $args["align_tablet"] ), ['left', 'center', 'right'], $defaults["align"] );
} else {
	$align_tablet = $align;
}
if( !empty( $args['align_mobile'] ) ) {
	$align_mobile = Utils::ensure_values_in_array( Utils::convert_chars( $args["align_mobile"] ), ['left', 'center', 'right'], $defaults["align"] );
} else {
	$align_mobile = $align;
}

$wrap_classes = ["section-title-2-wrap", "section-title-2-{$align}", "section-title-2-tablet-{$align_tablet}", "section-title-2-mobile-{$align_mobile}"];
?>
<div class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
	<<?php echo tag_escape( $custom_tag ) ?> class="section-title-2">
		<?php
		if( $icon ) {
			echo $icon;
		}
		?>
		<?php if( !empty( $args['link'] ) && !empty( !empty( $args['link']['url'] ) ) ) { ?>
			<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="section-title-2-title"><?php echo $title ?></a>
		<?php } else { ?>
			<span class="section-title-2-title"><?php echo $title ?></span>
		<?php } ?>
		<?php if( $subtitle ) { ?>
			<div class="section-title-2-subtitle"><?php echo $subtitle ?></div>
		<?php } ?>
	</<?php echo tag_escape( $custom_tag ) ?>>
</div>