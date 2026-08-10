<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use MJ\Whitebox\Utils as WhiteboxUtils;

$args = Utils::check_default( $args, [
	'icon_type'		=> 'image',
	'img'			=> [],
	'icon'			=> [],
	'icon_align'	=> 'center',
	'title'			=> '',
	'tag'			=> 'div',
	'subtitle'		=> '',
	'link'			=> [],
	'classes'		=> [],
	'hover_effect'	=> true,
], ['icon'] );

$args['icon_type'] = Utils::ensure_values_in_array( $args['icon_type'], ['image', 'icon'], 'image' );

$icon = '';
if( $args['icon_type'] == 'image' ) {
	$icon = !empty( $args['img']['id'] ) ? wp_get_attachment_image( $args['img']['id'], [52,52] ) : '<img src="' . $args['img']['url'] . '" alt="">';
} else {
	$icon = WhiteboxUtils::get_icon( $args['icon'], 'proicon-icon' );
}

$args['icon_align'] = Utils::ensure_values_in_array( $args['icon_align'], ['left', 'center', 'right'], 'center' );
$args['title'] = wp_kses_post( $args['title'] );
$args['tag'] = Utils::ensure_values_in_array( $args['tag'], array_keys( Utils::custom_tags() ), 'div' );
$args['subtitle'] = wp_kses_post( $args['subtitle'] );

$has_link = !empty( $args['link'] ) && !empty( $args['link']['url'] );

$classes = ['proicon', "proicon-icon-{$args['icon_align']}"];
if( $args['hover_effect'] ) {
	$classes[] = "proicon-hover";
} else {
	$classes[] = "proicon-no-hover";
}
$classes = array_merge( $classes, $args['classes'] );
?>
<?php if( $has_link ) { ?>
	<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
<?php } else { ?>
	<div class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
<?php } ?>
	<div class="proicon-img-wrap"><?php echo $icon ?></div>
	<div class="proicon-texts">
		<<?php echo tag_escape( $args['tag'] ) ?> class="proicon-title"><?php echo $args['title'] ?></<?php echo tag_escape( $args['tag'] ) ?>>
		<div class="proicon-subtitle"><?php echo $args['subtitle'] ?></div>
	</div>
<?php if( $has_link ) { ?>
	</a>
<?php } else { ?>
	</div>
<?php } ?>