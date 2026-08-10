<?php

use Bijan\Utils;
use Bijan\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'secondary'	=> false,
	'total'		=> 0,
	'remaining'	=> 0,
	'title'		=> '',
] );

$classes = ['product-progress'];
if( $args['secondary'] ) {
	$classes[] = 'product-progress-secondary';
}
if( $args['title'] ) {
	$classes[] = 'bijan-title-wrap';
}
?>
<div class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
	<div class="product-progress-line" style="--progress-width:<?php echo !empty( $args['total'] ) ? ($args['remaining']/$args['total'])*100 . "%" : '0' ?>"></div>
	<?php
	if( $args['title'] ) {
		UI::title( $args['title'] );
	}
	?>
</div>