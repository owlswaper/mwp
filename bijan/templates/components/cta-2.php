<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'logo'			=> '',
	'title'			=> '',
	'title_tag'		=> 'h3',
	'subtitle'		=> '',
	'link'			=> [],
	'description'	=> '',
	'image'			=> '',
	'show_curve'	=> true,
] );

// Sanitize
if( !empty( $args['logo'] ) ) {
	$args['logo'] = is_numeric( $args['logo'] ) ? wp_get_attachment_image( $args['logo'], [28, 28] ) : '<img src="' . $args['logo'] . '" alt="">';
}
$args['title'] = wp_kses_post( $args['title'] );
$args['title_tag'] = Utils::ensure_values_in_array( Utils::convert_chars( $args['title_tag'] ), array_keys( Utils::custom_tags() ), 'h2' );
$args['subtitle'] = wp_kses_post( $args['subtitle'] );
if( !empty( $args['image'] ) ) {
	$args['image'] = is_numeric( $args['image'] ) ? wp_get_attachment_image( $args['image'], 'full' ) : '<img src="' . $args['image'] . '" alt="">';
}
if( !empty( $args['mobile_image'] ) ) {
	$args['mobile_image'] = is_numeric( $args['mobile_image'] ) ? wp_get_attachment_image( $args['mobile_image'], 'full' ) : '<img src="' . $args['mobile_image'] . '" alt="">';
}
$args['description'] = wp_kses_post( $args['description'] );
?>
<div class="bijan-cta-2<?php echo $args['show_curve'] ? ' bijan-cta-2-with-curve' : '' ?>">
	<?php if( $args['show_curve'] ) { ?>
		<?php echo file_get_contents( BIJAN_DIR . "assets/img/cta-shape.svg" ) ?>
	<?php } ?>
	<div class="bijan-cta-2-inner">
		<?php if( !empty( $args['link'] ) && !empty( $args['link']['url'] ) ) { ?>
			<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $args['link'] ) ) ?> class="bijan-cta-2-start">
		<?php } else { ?>
			<div class="bijan-cta-2-start">
		<?php } ?>
			<?php if( $args['logo'] ) { ?>
				<div class="bijan-cta-2-logo"><?php echo $args['logo'] ?></div>
			<?php } ?>
			<div class="bijan-cta-2-start-texts">
				<<?php echo tag_escape( $args['title_tag'] ) ?> class="bijan-cta-2-title"><?php echo $args['title'] ?></<?php echo tag_escape( $args['title_tag'] ) ?>>
				<div class="bijan-cta-2-subtitle"><?php echo $args['subtitle'] ?></div>
			</div>

			<div class="bijan-cta-2-mobile-description"><?php echo $args['description'] ?></div>

			<div class="bijan-cta-2-mobile-image-wrap">
				<?php echo $args['mobile_image'] ?>
			</div>
		<?php if( !empty( $args['link'] ) && !empty( $args['link']['url'] ) ) { ?>
			</a>
		<?php } else { ?>
			</div>
		<?php } ?>

		<div class="bijan-cta-2-description"><?php echo $args['description'] ?></div>
	</div>

	<div class="bijan-cta-2-image-wrap">
		<?php echo $args['image'] ?>
	</div>
</div>