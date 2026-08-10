<?php

use Bijan\Utils;

$args = Utils::check_default( $args, [
	'image'	=> [
		'id'	=> 0,
		'url'	=> '',
	],
	'show_dots'	=> true,
] );

$html_attrs = [
	'classes'	=> ['arax-circle', 'arax-circle-image'],
];
if( !$args['show_dots'] ) {
	$html_attrs['classes'][] = 'hide-dots';
}
?>
<div <?php echo Utils::get_html_attributes( $html_attrs ) ?>>
	<div class="arax-circle-inner">
		<?php echo !empty( $args['image']['id'] ) ? wp_get_attachment_image( $args['image']['id'], 'full' ) : '<img src="' . $args['image']['url'] . '" alt="">' ?>
	</div>
</div>