<?php

use Bijan\Utils;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'placeholder'		=> esc_html__( "Search...", 'bijan' ),
	'show_icon'			=> true,
	'icon'				=> 'bijan-icon-search-normal',
	'form_classes'		=> [],
	'form_id'			=> '',
	'input_classes'		=> [],
	'input_id'			=> '',
	'button_classes'	=> [],
	'button_id'			=> '',
	'popover_classes'	=> [],
	'popover_id'		=> '',
	'post_type'			=> '', // empty(all) | product | post | post type name
	'query_args'		=> [], // Other query args for WP_Query
], ['icon'] );

$form_attrs = [
	'action'	=> "",
	'method'	=> 'post',
	'class'		=> array_merge( ['bijan-search', 'bijan-search-ajax'], $args['form_classes'] ),
];
if( $args['form_id'] ) {
	$form_attrs['id'] = $args['form_id'];
}
if( $args['post_type'] ) {
	$form_attrs['data-post_type'] = $args['post_type'];
}
if( !empty( $args['query_args'] ) ) {
	$form_attrs['data-args'] = $args['query_args'];
}

$input_attrs = [
	'type'			=> 'search',
	'name'			=> 's',
	'class'			=> array_merge( ['bijan-search-field'], $args['input_classes'] ),
	'placeholder'	=> $args['placeholder'],
	'value'			=> get_search_query(),
];
if( $args['input_id'] ) {
	$input_attrs['id'] = $args['input_id'];
}

$popover_attrs = [
	'class'	=> array_merge( ['bijan-popover', 'bijan-search-results'], $args['popover_classes'] ),
];
if( $args['popover_id'] ) {
	$popover_attrs['id'] = $args['popover_id'];
}

$icon = '';
if( $args['show_icon'] ) {
	$icon = WhiteboxUtils::get_icon( $args['icon'], 'bijan-search-icon' );
}
?>
<form <?php echo Utils::get_html_attributes( $form_attrs ) ?>>
	<label class="screen-reader-text"><?php esc_html_e( 'Search for:', 'bijan' ) ?></label>
	<input <?php echo Utils::get_html_attributes( $input_attrs ) ?>>
	<?php
	if( $icon ) {
		$button_attrs = [
			'type'	=> "submit",
			'class'	=> array_merge( ["button-transparent", "circle"], $args['button_classes'] ),
			'title'	=> esc_html__( "Search", 'bijan' ),
		];
		if( $args['button_id'] ) {
			$button_attrs['id'] = $args['button_id'];
		}
		?>
		<button <?php echo Utils::get_html_attributes( $button_attrs ) ?>><?php echo $icon ?></button>
	<?php } ?>
	<div <?php echo Utils::get_html_attributes( $popover_attrs ) ?>></div>
	<?php
	get_template_part( "templates/components/loading", null, [
		'text'		=> esc_html__( "Searching ... Please wait ...", 'bijan' ),
		'classes'	=> ['bijan-popover'],
	] );
	?>
</form>