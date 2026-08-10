<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$item_defaults = [
	'img'	=> [],
	'link'	=> '',
];
$args = Utils::check_default( $args, [
	'items' 				=> [],
	'grayscale_thumbs'		=> true,
	'loop'					=> true,
	'autoplay'				=> 10,
	'show_scrollbar'		=> true,
	'desktop_slider_height'	=> 360,
	'desktop_slides'		=> 4,
	'desktop_slides_space'	=> 12,
	'tablet_slides'			=> 4,
	'tablet_slides_space'	=> 12,
	'mobile_slides'			=> 4,
	'mobile_slides_space'	=> 12,
] );

$thumbnail_attributes = [
	'class'	=> [
		"bijan-thumbnail-slider",
		"bijan-slider-wrap",
		"swiper",
	],
	'data-settings'	=> [
		'slider'	=> [
			'reInit'				=> true,
			'direction'				=> 'vertical',
			'grabCursor'			=> true,
			'loop'					=> $args['loop'],
			'slidesPerView'			=> $args['desktop_slides'],
			'spaceBetween'			=> $args['desktop_slides_space'],
			'watchSlidesProgress'	=> true,
			'mousewheel'			=> true,
			'freeMode'				=> true,
			'autoplay'				=> [
				'delay'	=> absint( $args['autoplay'] ),
			],
		],
		'desktop'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
		'tablet'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'direction'		=> 'horizontal',
				'slidesPerView'	=> $args['tablet_slides'],
				'spaceBetween'	=> $args['tablet_slides_space'],
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'direction'		=> 'horizontal',
				'slidesPerView'	=> $args['mobile_slides'],
				'spaceBetween'	=> $args['mobile_slides_space'],
			],
		],
	],
];
if( $args['show_scrollbar'] ) {
	$thumbnail_attributes['data-settings']['slider']['scrollbar'] = [
		'el'	=> '.swiper-scrollbar',
	];
}

$main_attributes = [
	'class'	=> [
		"bijan-main-slider",
		"bijan-slider-wrap",
		"swiper",
	],
	'data-settings'	=> [
		'slider'	=> [
			'reInit'		=> true,
			'direction'		=> 'vertical',
			'loop'			=> true,
			'direction'		=> 'vertical',
			'spaceBetween'	=> 16,
			'slidesPerView'	=> 1,
			'autoplay'		=> [
				'delay'	=> absint( $args['autoplay'] ),
			],
			'thumbs'		=> [],
		],
		'desktop'	=> [
			'slider'	=> [
				'enabled'	=> true,
				// 'direction'	=> 'vertical',
			],
		],
		'tablet'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'direction'		=> 'horizontal',
				'slidesPerView'	=> 1,
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'direction'		=> 'horizontal',
				'slidesPerView'	=> 1,
			],
		],
	],
];

$wrap_attributes = [
	'class'	=> [
		'bijan-thumbnail-slider-wrap',
	],
];
if( $args['show_scrollbar'] ) {
	$wrap_attributes['class'][] = 'bijan-thumbnail-slider-with-scrollbar';
}
if( !$args['grayscale_thumbs'] ) {
	$wrap_attributes['class'][] = 'bijan-thumbnail-slider-colored';
}
?>
<div <?php echo Utils::get_html_attributes( $wrap_attributes ) ?>>
	<div <?php echo Utils::get_html_attributes( $thumbnail_attributes ) ?>>
		<?php if( $args['show_scrollbar'] ) { ?>
			<div class="swiper-scrollbar"></div>
		<?php } ?>
		<div class="swiper-wrapper wrapper">
			<?php foreach( $args['items'] as $item ) { ?>
				<div class="swiper-slide slider-slide">
					<?php
					if( is_numeric( $item['img'] ) ) {
						echo wp_get_attachment_image( $item['img'], [300, 300] );
					} else {
						echo '<img src="' . $item['img'] . '" alt="">';
					}
					?>
				</div>
			<?php } ?>
		</div>
	</div>

	<div <?php echo Utils::get_html_attributes( $main_attributes ) ?>>
		<div class="swiper-wrapper wrapper">
			<?php foreach( $args['items'] as $item ) { ?>
				<div class="swiper-slide slider-slide">
					<?php if( !empty( $item['link'] ) && !empty( $item['link']['url'] ) ) { ?>
						<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $item['link'] ) ) ?>>
					<?php } else { ?>
						<div class="main-slider-item">
					<?php } ?>
						<?php
						if( is_numeric( $item['img'] ) ) {
							echo wp_get_attachment_image( $item['img'], 'full' );
						} else {
							echo '<img src="' . $item['img'] . '" alt="">';
						}
						?>
					<?php if( !empty( $item['link'] ) && !empty( $item['link']['url'] ) ) { ?>
						</a>
					<?php } else { ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>