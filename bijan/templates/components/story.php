<?php

use Bijan\Utils;
use Bijan\Utils\Story as UtilsStory;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'		=> [],
	'grayscale'	=> false,
] );

$posts_args = [
	'post_type'	=> 'story',
	'orderby'	=> $args['orderby'],
	'order'		=> $args['order'],
];
if( $args['query_type'] == 'by_id' ) {
	$posts_args['post__in'] = $args['items'];
} else {
	$posts_args['numberposts'] = $args['ppp'];
	$posts_args['offset'] = $args['offset'];
}
$posts = get_posts( $posts_args );
$items = [];
if( !empty( $posts ) ) {
	$items = array_map( fn( $post ) => UtilsStory::get( $post ), $posts );
}

$display_attributes = Elementor::get_display_attributes( $args, true );

$attributes = [
	'class'			=> array_merge( [
		"story-items",
		"bijan-slider-wrap",
		"swiper",
	], $display_attributes['classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];

if( $args['grayscale'] ) {
	$attributes['class'][] = 'story-grayscale';
}
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<div class="wrapper swiper-wrapper">
		<?php foreach( $items as $item ) { ?>
			<div class="story-item slider-slide swiper-slide" title="<?php echo esc_attr( $item['title'] ) ?>" data-id="<?php echo esc_attr( absint( $item['id'] ) ) ?>" data-nonce="<?php echo wp_create_nonce( "story_{$item['id']}_view" ) ?>">
				<div class="story-item-small-img"><?php echo wp_get_attachment_image( $item['small_img'], [76,76] ) ?></div>
				<div class="story-item-title line-clamp line-clamp-2"><?php echo esc_html( $item['title'] ) ?></div>
			</div>
		<?php } ?>
	</div>
</div>