<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use MJ\Whitebox\Utils\Sanitizers;

$args = Utils::check_default( $args, [
	'title'		=> '',
	'title_tag'	=> 'h2',
	'icon'		=> [],
	'link'		=> [],
], ['icon'] );

$icon = Utils::get_icon( $args['icon'], 'bijan-ribbon-icon' );

$title = wp_kses_post( $args['title'] );
$title = preg_replace( '/\{([^}]+)\}/', '<span>$1</span>', $title );

$tag = 'div';
$html_attrs = [
	'classes'	=> ['bijan-ribbon'],
];
if( Elementor::has_link( $args['link'] ) ) {
	$tag = 'a';
	$html_attrs = array_merge( $html_attrs, Elementor::get_link_attributes( $args['link'] ) );
}
?>
<<?php echo "{$tag} " . Utils::get_html_attributes( $html_attrs ) ?>>
	<div class="bijan-ribbon-triangles"></div>
	<div class="bijan-ribbon-content">
		<?php
		if( $icon ) {
			echo $icon;
		}
		?>
		<<?php echo tag_escape( Sanitizers::tag( $args['title_tag'] ) ) ?> class="bijan-ribbon-title"><?php echo $title ?></<?php echo tag_escape( Sanitizers::tag( $args['title_tag'] ) ) ?>>
	</div>
</<?php echo $tag ?>>