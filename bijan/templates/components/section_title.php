<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'icon'			=> '',
	'tag'			=> 'h2',
	'title'			=> '',
	'link'			=> [],
	'show_divider'	=> true,
	'nav_btns'		=> false,
	'classes'		=> [],
], ['icon', 'link'] );

// Sanitize
$custom_tag = Utils::ensure_values_in_array( Utils::convert_chars( $args['tag'] ), array_keys( Utils::custom_tags() ), 'h2' );
$icon = WhiteboxUtils::get_icon( $args['icon'], 'section-title-icon' );
$title = wp_kses_post( $args['title'] );
$show_divider = Utils::to_bool( $args['show_divider'] );
$nav_btns = Utils::to_bool( $args['nav_btns'] );

$classes = ['section-title-wrap'];
if( !empty( $args['classes'] ) ) {
	$classes = array_merge( $classes, $args['classes'] );
}
?>
<div class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
	<<?php echo tag_escape( $custom_tag ) ?> class="section-title">
		<?php
		if( $icon ) {
			echo $icon;
		}
		?>
		<?php if( !empty( $args['link'] ) && !empty( !empty( $args['link']['url'] ) ) ) { ?>
			<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="section-title-title"><?php echo $title ?></a>
		<?php } else { ?>
			<span class="section-title-title"><?php echo $title ?></span>
		<?php } ?>
		<?php if( $show_divider ) { ?>
			<div class="section-title-divider"></div>
		<?php } ?>
		<?php
		if( $nav_btns ) {
			?>
			<div class="slider-nav-wrap">
				<?php
				get_template_part( 'templates/components/slider_arrows' );
				?>
			</div>
			<?php
		}
		?>
	</<?php echo tag_escape( $custom_tag ) ?>>
</div>