<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'title'			=> '',
	'title_tag'		=> 'h3',
	'subtitle'		=> '',
	'image'			=> '',
	'description'	=> '',
	'show_button'	=> true,
], ['image'] );
if( $args['show_button'] ) {
	$args = Elementor::check_button_defaults( $args );
}

// Sanitize
$args['title'] = wp_kses_post( $args['title'] );
$args['title_tag'] = Utils::ensure_values_in_array( Utils::convert_chars( $args['title_tag'] ), array_keys( Utils::custom_tags() ), 'h2' );
$args['subtitle'] = wp_kses_post( $args['subtitle'] );
$args['description'] = wp_kses_post( $args['description'] );
?>
<div class="bijan-cta-1-wrap">
	<div class="bijan-cta-1">
		<div class="bijan-cta-1-start">
			<<?php echo tag_escape( $args['title_tag'] ) ?> class="bijan-cta-1-title"><?php echo $args['title'] ?></<?php echo tag_escape( $args['title_tag'] ) ?>>
			<div class="bijan-cta-1-subtitle"><?php echo $args['subtitle'] ?></div>
		</div>

		<div class="bijan-cta-1-image-wrap">
			<?php echo !empty( $args['image']['id'] ) ? wp_get_attachment_image( $args['image']['id'], [116, 116] ) : '<img src="' . $args['image']['url'] . '" alt="">' ?>
		</div>

		<div class="bijan-cta-1-description"><?php echo $args['description'] ?></div>

		<?php
		if( $args['show_button'] ) {
			$args['prefix'] = 'button_';
			$args['button_type'] = 'secondary';
			?>
			<div class="bijan-cta-1-button-wrap">
				<?php get_template_part( "templates/components/button", null, $args ) ?>
			</div>
		<?php } ?>
	</div>
</div>