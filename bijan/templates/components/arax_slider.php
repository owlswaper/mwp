<?php

use Bijan\ElementorControls\Slider;
use Bijan\Utils;
use Bijan\Utils\Elementor;

$args = Utils::check_default( $args, [
	'items'			=> [],
	'autoplay'		=> true,
	'autoplay_time'	=> 10,
	'show_arrows'	=> true,
	'next_icon'		=> Slider::$default_next_arrow_icon['value'],
	'prev_icon'		=> Slider::$default_prev_arrow_icon['value'],
	'loop'			=> true,
	'thumb_desktop_slides'	=> 4,
	'badge_img'	=> [
		'url'	=> BIJAN_URI . "assets/img/slider-badge.svg",
	]
], ['next_icon', 'prev_icon'] );

$args['desktop_slides_type'] = 'count';
$args['desktop_slides'] = 1;
$args['desktop_slides_space'] = 64;
$args['tablet_slides_type'] = 'auto';
$args['tablet_slides'] = 1;
$args['tablet_slides_space'] = 1;
$args['mobile_slides_type'] = 'auto';
$args['mobile_slides'] = 1;
$args['mobile_slides_space'] = 1;

$thumb_display_args = Utils::extract( $args, ['autoplay', 'autoplay_time', 'loop'] );
$thumb_display_args['desktop_slides_type'] = 'count';
$thumb_display_args['desktop_slides'] = $args['thumb_desktop_slides'];
$thumb_display_args['desktop_slides_space'] = 20;
$thumb_display_args['tablet_slides_type'] = 'auto';
$thumb_display_args['tablet_slides'] = 1;
$thumb_display_args['tablet_slides_space'] = 1;
$thumb_display_args['mobile_slides_type'] = 'auto';
$thumb_display_args['mobile_slides'] = 1;
$thumb_display_args['mobile_slides_space'] = 1;

$thumb_display_attrs = Elementor::get_display_attributes( $thumb_display_args, true );
$thumb_main_html_attrs = [
	'classes'		=> array_merge( ['bijan-slider-wrap', 'arax-slider-thumb-wrap', 'show-only-desktop-1024'], $thumb_display_attrs['wrap_classes'] ),
	'data-settings'	=> $thumb_display_attrs['args'],
	'style'			=> $thumb_display_attrs['style'],
];
$thumb_wrap_html_attrs = [
	'classes'	=> array_merge( ['wrapper'], $thumb_display_attrs['classes'] ),
];

$display_attrs = Elementor::get_display_attributes( $args, true );

$display_attrs['args']['slider']['thumbs'] = [];
$main_html_attrs = [
	'classes'		=> array_merge( ['bijan-slider-wrap', 'arax-slider-main-wrap','arax-circle'], $display_attrs['wrap_classes'] ),
	'data-settings'	=> $display_attrs['args'],
	'style'			=> $display_attrs['style'],
];
$items_html_attrs = [
	'classes'	=> array_merge( ['wrapper', 'arax-circle-inner'], $display_attrs['classes'] ),
];
?>
<div class="arax-slider-wrap">
	<div <?php echo Utils::get_html_attributes( $thumb_main_html_attrs ) ?>>
		<div <?php echo Utils::get_html_attributes( $thumb_wrap_html_attrs ) ?>>
			<?php
			foreach( $args['items'] as $item ) {
				$img = !empty( $item['thumb_img']['id'] ) ? $item['thumb_img']['id'] : $item['thumb_img']['url'];
				if( !$img ) {
					$img = !empty( $item['img']['id'] ) ? $item['img']['id'] : $item['img']['url'];
				}
				?>
				<div class="arax-slider-thumb slider-slide">
					<div class="arax-slider-thumb-img"><?php echo is_numeric( $img ) ? wp_get_attachment_image( $img, [80, 80] ) : '<img src="' . $img . '" alt="">' ?></div>
					<div class="arax-slider-thumb-text"><?php echo wp_kses_post( $item['thumb_text'] ) ?></div>
				</div>
			<?php } ?>
		</div>
	</div>

	<div <?php echo Utils::get_html_attributes( $main_html_attrs ) ?>>
		<?php get_template_part( "templates/components/slider_arrows", null, $args ) ?>

		<div class="arax-slider-circle"></div>

		<div <?php echo Utils::get_html_attributes( $items_html_attrs ) ?>>
			<?php
			foreach( $args['items'] as $item ) {
				$tag = 'div';
				$html_attrs = [
					'classes'	=> ['slider-slide', 'arax-slider-item'],
				];
				$has_badge = Utils::to_bool( $item['show_badge'] ) && ( $item['badge_top_text'] || $item['badge_bottom_text'] );
				if( $has_badge ) {
					$html_attrs['classes'][] = 'has-badge';
					$badge_image = $item['img']['id'] ? wp_get_attachment_image_url( $item['img']['id'], 'full' ) : $item['img']['url'];
					if( $badge_image ) {
						$badge_image = BIJAN_URI . "assets/img/slider-badge.svg";
					}
				}
				if( Elementor::has_link( $item['link'] ) ) {
					$tag = 'a';
					$html_attrs = array_merge( $html_attrs, Elementor::get_link_attributes( $item['link'] ) );
				}

				if( !isset( $item['badge_position'] ) ) {
					$item['badge_position'] = 'center-center';
				}
				?>
				<<?php echo "{$tag} " . Utils::get_html_attributes( $html_attrs ) ?>>
					<?php echo $item['img']['id'] ? wp_get_attachment_image( $item['img']['id'], 'full' ) : '<img src="' . $item['img']['url'] . '" alt="">' ?>
					<?php if( $has_badge ) { ?>
						<div class="arax-slider-item-badge <?php echo esc_attr( $item['badge_position'] ) ?>" style="rotate:<?php echo $item['badge_rotate'] ?>deg">
							<img src="<?php echo $badge_image ?>" alt="">
							<div class="arax-slider-item-badge-texts">
								<span class="arax-slider-item-badge-top"><?php echo wp_kses_post( $item['badge_top_text'] ) ?></span>
								<span class="arax-slider-item-badge-bottom"><?php echo wp_kses_post( $item['badge_bottom_text'] ) ?></span>
							</div>
						</div>
					<?php } ?>
				</<?php echo $tag ?>>
			<?php } ?>
		</div>
	</div>
</div>