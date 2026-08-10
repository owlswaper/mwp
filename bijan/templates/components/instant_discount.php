<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'autoplay'	=> 10,
	'title'		=> '',
	'products'	=> [],
] );

$attributes = [
	'class'			=> [
		'instant-discount-wrap',
		"bijan-slider-wrap",
		"swiper"
	],
	'data-settings'	=> [
		'slider'	=> [
			'loop'				=> true,
			'slidesPerView'		=> 1,
			'spaceBetween'		=> 56,
			'autoplay'			=> [
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
				'enabled'	=> true,
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
	],
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<div class="instant-discount-title"><?php echo $args['title'] ?></div>
	<div class="wrapper swiper-wrapper">
		<?php foreach( $args['products'] as $product_id => $product ) { ?>
			<div class="instant-discount-item slider-slide swiper-slide">
				<a href="<?php echo get_permalink( $product_id ) ?>" title="<?php echo esc_attr( $product['name'] ) ?>">
					<div class="instant-discount-item-img"><?php echo $product['img'] ?></div>
					<h3 class="instant-discount-item-name line-clamp line-clamp-1"><?php echo $product['name'] ?></h3>
					<div class="instant-discount-item-price"><?php echo wc_price( $product['price'] ) ?></div>
					<?php get_template_part( "templates/components/product_progress", null, ['total' => $product['total'], 'remaining' => $product['remaining'], 'secondary' => true] ) ?>
				</a>
			</div>
		<?php } ?>
	</div>
</div>