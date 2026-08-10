<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use MJ\Whitebox\Utils\Sanitizers;

$args = Utils::check_default( $args, [
	'tag'		=> 'h2',
	'top_text'	=> '',
	'title'		=> '',
	'link'		=> [],
], ['link'] );

$title = wp_kses_post( $args['title'] );
$title = preg_replace( '/\{([^}]+)\}/', '<span>$1</span>', $title );

$tag = 'div';
$html_attrs = [
	'classes'	=> ['section-title-3'],
];
if( Elementor::has_link( $args['link'] ) ) {
	$tag = 'a';
	$html_attrs = array_merge( $html_attrs, Elementor::get_link_attributes( $args['link'] ) );
}
?>
<<?php echo "{$tag} " . Utils::get_html_attributes( $html_attrs ) ?>>
	<<?php echo tag_escape( Sanitizers::tag( $args['tag'] ) ) ?> class="section-title-3-content">
		<?php if( $args['top_text'] ) { ?>
			<div class="section-title-3-top"><?php echo wp_kses_post( $args['top_text'] ) ?></div>
		<?php } ?>
		<div class="section-title-3-title"><?php echo $title ?></div>
	</<?php echo tag_escape( Sanitizers::tag( $args['tag'] ) ) ?>>
</<?php echo $tag ?>>