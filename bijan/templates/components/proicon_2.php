<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use MJ\Whitebox\Utils\Sanitizers;

$args = Utils::check_default( $args, [
	'items'		=> [],
	'title_tag'	=> 'div',
] );

$display_attrs = Elementor::get_display_attributes( $args );

$main_html_attrs = [
	'classes'		=> array_merge( ['bijan-slider-wrap', 'pro-icon-2-wrap'], $display_attrs['wrap_classes'] ),
	'data-settings'	=> $display_attrs['args'],
	'style'			=> $display_attrs['style'],
];
$wrap_html_attrs = [
	'classes'	=> array_merge( ['wrapper', 'pro-icon-2-items'], $display_attrs['classes'] ),
];
?>
<div <?php echo Utils::get_html_attributes( $main_html_attrs ) ?>>
	<div <?php echo Utils::get_html_attributes( $wrap_html_attrs ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			$tag = 'div';
			$item_html_attrs = [
				'classes'	=> ['slider-slide', 'pro-icon-2']
			];
			if( Elementor::has_link( $item['link'] ) ) {
				$tag = 'a';
				$item_html_attrs = array_merge( $item_html_attrs, Elementor::get_link_attributes( $item['link'] ) );
			}
			?>
			<<?php echo "{$tag} " . Utils::get_html_attributes( $item_html_attrs ) ?>>
				<div class="pro-icon-2-icon-wrap">
					<?php if( $item['icon_type'] == 'image' ) { ?>
						<?php echo $item['img']['id'] ? wp_get_attachment_image( $item['img']['id'], [44, 44] ) : '<img src="' . $item['img']['url'] . '" alt="">' ?>
					<?php } else { ?>
						<?php echo Utils::get_icon( $item['icon'], 'pro-icon-2-icon' ) ?>
					<?php } ?>
				</div>
				
				<div class="pro-icon-2-texts">
					<<?php echo tag_escape( Sanitizers::tag( $args['title_tag'] ) ) ?> class="pro-icon-2-title"><?php echo wp_kses_post( $item['title'] ) ?></<?php echo tag_escape( Sanitizers::tag( $args['title_tag'] ) ) ?>>
					<div class="pro-icon-2-subtitle"><?php echo wp_kses_post( $item['subtitle'] ) ?></div>
				</div>
			</<?php echo $tag ?>>
		<?php } ?>
	</div>
</div>