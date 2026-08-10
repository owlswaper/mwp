<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use Bijan\Utils\UI;

$args = Utils::check_default( $args, [
	'items'				=> [],
	'desktop_columns'	=> 5,
	'tablet_columns'	=> 3,
	'mobile_columns'	=> 3,
] );

$default_item = [
	'img'	=> [],
	'name'	=> '',
	'link'	=> [],
];
$args['items'] = array_map( fn( $item ) => Utils::check_default( $item, $default_item ), $args['items'] );

$attributes = [
	'class'	=> ['bijan-brands'],
	'style'	=> [
		'--desktop-columns'		=> $args['desktop_columns'],
		'--tablet-columns'		=> $args['tablet_columns'],
		'--mobile-columns'		=> $args['mobile_columns'],
		'--desktop-row-gap'		=> "{$args['desktop_row_gap']}px",
		'--desktop-column-gap'	=> "{$args['desktop_column_gap']}px",
		'--tablet-row-gap'		=> "{$args['tablet_row_gap']}px",
		'--tablet-column-gap'	=> "{$args['tablet_column_gap']}px",
		'--mobile-row-gap'		=> "{$args['mobile_row_gap']}px",
		'--mobile-column-gap'	=> "{$args['mobile_column_gap']}px",
	]
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php foreach( $args['items'] as $index => $item ) { ?>
		<?php if( !empty( $item['link'] ) && !empty( $item['link']['url'] ) ) { ?>
			<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $item['link'] ) ) ?> class="bijan-brand bijan-title-wrap" style="z-index:<?php echo count( $args['items'] )-$index ?>">
		<?php } else { ?>
			<div class="bijan-brand bijan-title-wrap">
		<?php } ?>
			<?php if( !empty( $item['img']['id'] ) ) { ?>
				<?php echo wp_get_attachment_image( $item['img']['id'], [58, 58] ) ?>
			<?php } else { ?>
				<img src="<?php echo $item['img']['url'] ?>" alt="">
			<?php } ?>

			<?php
			if( $item['name'] ) {
				UI::title( $item['name'] );
			}
			?>
		<?php if( !empty( $item['link'] ) && !empty( $item['link']['url'] ) ) { ?>
			</a>
		<?php } else { ?>
			</div>
		<?php } ?>
	<?php } ?>
</div>