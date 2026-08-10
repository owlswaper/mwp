<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'loop'			=> true,
	'autoplay'		=> 10,
	'show_arrows'	=> true,
	'items'			=> [],
] );

$attributes = [
	"class"	=> [
		"bijan-slider",
		"bijan-slider-wrap",
		"swiper"
	],
	'data-settings'	=> [
		'slider'	=> [
			'loop'			=> $args['loop'],
			'slidesPerView'	=> 1,
			'spaceBetween'	=> 0,
			'autoHeight'	=> true,
		],
		'desktop'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
		'tablet'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
	]
];
if( $args['autoplay'] ) {
	$attributes['data-settings']['slider']['autoplay'] = [
		'delay' => absint( $args['autoplay'] ),
	];
}
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php
	if( $args['show_arrows'] ) {
		get_template_part( "templates/components/slider_arrows" );
	}
	?>
	<div class="swiper-wrapper">
		<?php
		foreach( $args['items'] as $item ) {
			if( is_numeric( $item['img'] ) ) {
				$img = wp_get_attachment_image( $item['img'], 'full' );
			} else {
				$img = '<img src="' . $item['img'] . '" alt="">';
			}
			if( is_numeric( $item['mobile_img'] ) ) {
				$mobile_img = wp_get_attachment_image( $item['mobile_img'], 'full' );
			} else {
				$mobile_img = '<img src="' . $item['mobile_img'] . '" alt="">';
			}
			?>
			<div class="slider-item swiper-slide">
				<?php if( !empty( $item['link'] ) ) { ?>
					<a <?php echo \Elementor\Utils::render_html_attributes( Elementor::get_link_attributes( $item['link'] ) ) ?> class="slider-item-link">
				<?php } ?>
				<?php echo $img ?>
				<?php echo $mobile_img ?>
				<?php if( !empty( $item['link'] ) ) { ?>
					</a>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>