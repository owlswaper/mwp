<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'	=> [],
] );

$display_attributes = Elementor::get_display_attributes( $args );

$attrs = [
	'class'	=> array_merge( [
		'team-items',
		'swiper',
		"bijan-slider-wrap",
	], $display_attributes['classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
?>
<div <?php echo Utils::get_html_attributes( $attrs ) ?>>
	<div class="wrapper swiper-wrapper">
		<?php foreach( $args['items'] as $item ) { ?>
			<?php if( !empty( $item['link'] ) && !empty( $item['link']['url'] ) ) { ?>
				<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $item['link'] ) ) ?> class="team-item slider-item swiper-slide">
			<?php } else { ?>
				<div class="team-item slider-item swiper-slide">
			<?php } ?>
				<?php echo !empty( $item['img']['id'] ) ? wp_get_attachment_image( $item['img']['id'], [196, 196] ) : '<img src="' . $item['img']['url'] . '" alt="">' ?>
				<div class="team-item-texts">
					<span class="team-item-position"><?php echo wp_kses_post( $item['position'] ) ?></span>
					<span class="team-item-name"><?php echo wp_kses_post( $item['name'] ) ?></span>
				</div>
			<?php if( !empty( $item['link'] ) && !empty( $item['link']['url'] ) ) { ?>
				</a>
			<?php } else { ?>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
</div>