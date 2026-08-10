<?php

use Bijan\Utils;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'			=> [],
	'show_arrows'	=> true,
] );

$attributes = [
	'class'			=> [
		"categories-slider",
		"bijan-slider-wrap",
		"swiper",
		'slider-arrows-hover',
	],
	'data-settings'	=> [
		'slider'	=> [
			'slidesPerView'	=> 'auto',
		],
		'desktop'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'spaceBetween'	=> 0,
			],
		],
		'tablet'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'spaceBetween'	=> 0,
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'spaceBetween'	=> 0,
			],
		],
	],
];
if( !$args['show_arrows'] ) {
	$attributes['class'][] = 'bijan-slider-hidden-arrows';
}
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php
	if( $args['show_arrows'] ) {
		get_template_part( "templates/components/slider_arrows" );
	}
	?>
	<div class="wrapper swiper-wrapper">
		<?php
		foreach( $args['items'] as $item ) {
			if( empty( $item['term_id'] ) ) continue;
			$term = get_term( $item['term_id'] );
			if( empty( $term ) || is_wp_error( $term ) ) continue;
			?>
			<a href="<?php echo get_term_link( $term ) ?>" class="category-item slider-slide swiper-slide" title="<?php echo esc_attr( $term->name ) ?>" data-id="<?php echo esc_attr( $term->term_id ) ?>">
				<div class="category-item-wrap">
					<div class="category-item-inner">
						<?php echo WhiteboxUtils::get_icon( $item['icon'], 'category-icon' ) ?>
						<div class="category-title line-clamp line-clamp-1"><?php echo esc_html( $term->name ) ?></div>
					</div>
				</div>
			</a>
		<?php } ?>
	</div>
</div>