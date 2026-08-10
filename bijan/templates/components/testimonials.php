<?php

use Bijan\Utils;
use Bijan\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

$item_defaults = [
	'img'		=> [],
	'name'		=> '',
	'position'	=> '',
	'text'		=> '',
];

$args = Utils::check_default( $args, [
	'items' 	=> [],
	'loop'		=> false,
	'autoplay'	=> 10,
	'show_nav'	=> true,
] );

$attributes = [
	'class'	=> [
		"bijan-testimonials",
		"bijan-slider-wrap",
		"swiper",
		"testimonials-slider",
	],
	'data-settings'	=> [
		'slider'	=> [
			'direction'			=> 'vertical',
			'grabCursor'		=> true,
			'loop'				=> $args['loop'],
			'slidesPerView'		=> 3,
			'spaceBetween'		=> -192,
			'centeredSlides'	=> true,
			'autoHeight'		=> false,
			'roundLengths'		=> true,
			'autoplay'			=> [
				'delay'	=> absint( $args['autoplay'] ),
			],
			'effect'			=> "coverflow",
			"coverflowEffect"	=> [
				"rotate"		=> 0,
				'depth'			=> 50,
				'modifier'		=> 30,
				"slideShadows"	=> false,
			],
		],
		'desktop'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
		'tablet'	=> [
			'slider'	=> [
				'enabled'			=> true,
				'slidesPerView'		=> 1,
				"coverflowEffect"	=> [
					"rotate"		=> 0,
					'depth'			=> 30,
					'modifier'		=> 30,
					"slideShadows"	=> false,
				],
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'			=> true,
				'slidesPerView'		=> 1,
				"coverflowEffect"	=> [
					"rotate"		=> 0,
					'depth'			=> 20,
					'modifier'		=> 10,
					"slideShadows"	=> false,
				],
			],
		],
	],
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php if( $args['show_nav'] ) { ?>
		<div class="slider-nav-wrap">
			<?php get_template_part( "templates/components/slider_arrows", null, [
				'transparent'	=> false,
				'next_icon'		=> 'bijan-icon-arrow-down-2',
				'prev_icon'		=> 'bijan-icon-arrow-up-2',
			] ); ?>
		</div>
	<?php } ?>
	<div class="wrapper swiper-wrapper">
		<?php
		foreach( $args['items'] as $item ) {
			$item = Utils::check_default( $item, $item_defaults );
			if( !$item['name'] || !$item['text'] ) continue;
			$img = is_numeric( $item['img']['id'] ) ? wp_get_attachment_image( $item['img']['id'], [90, 90] ) : '<img src="' . $item['img']['url'] . '" alt="">';
			?>
			<div class="testimonial-item slider-slide swiper-slide">
				<?php UI::curve( "vertical" ) ?>
				<div class="testimonial-inner">
					<div class="testimonial-texts">
						<div class="testimonial-name"><?php echo $item['name'] ?></div>
						<div class="testimonial-position"><?php echo $item['position'] ?></div>
						<div class="testimonial-text"><?php echo $item['text'] ?></div>
					</div>

					<div class="testimonial-image"><?php echo $img ?></div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>