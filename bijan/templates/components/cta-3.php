<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'title'		=> '',
	'title_tag'	=> 'h3',
	'subtitle'	=> '',
	'image'		=> '',
	'buttons'	=> [],
] );

$args['title'] = wp_kses_post( $args['title'] );
$args['title_tag'] = Utils::ensure_values_in_array( Utils::convert_chars( $args['title_tag'] ), array_keys( Utils::custom_tags() ), 'h2' );
$args['subtitle'] = wp_kses_post( $args['subtitle'] );
if( !empty( $args['image'] ) ) {
	$args['image'] = is_numeric( $args['image'] ) ? wp_get_attachment_image( $args['image'], 'full' ) : '<img src="' . $args['image'] . '" alt="">';
}
?>
<div class="bijan-cta-3-wrap">
	<div class="bijan-cta-3">
		<div class="bijan-cta-3-start">
			<<?php echo tag_escape( $args['title_tag'] ) ?> class="bijan-cta-3-title"><?php echo $args['title'] ?></<?php echo tag_escape( $args['title_tag'] ) ?>>
			<div class="bijan-cta-3-subtitle"><?php echo $args['subtitle'] ?></div>
		</div>

		<div class="bijan-cta-3-image-wrap">
			<?php echo $args['image'] ?>
		</div>

		<div class="bijan-cta-3-buttons">
			<?php
			if( !empty( $args['buttons'] ) ) {
				foreach( $args['buttons'] as $button ) {
					if( $button['button_mode'] == 'button' ) {
						$button['prefix'] = 'button_';
						get_template_part( "templates/components/button", null, $button );
					} else if( $button['button_mode'] == 'market' ) {
						$button['prefix'] = 'market_button_';
						get_template_part( "templates/components/market_button", null, $button );
					}
				}
			}
			?>
		</div>
	</div>
</div>