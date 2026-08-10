<?php
use Bijan\Utils;
use Bijan\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$default_options = [
	'show-header-search'		=> true,
	'header-search-placeholder'	=> esc_html__( "Search...", 'bijan' ),
	'header-search-ajax'		=> true,
	'header-search-icon'		=> "bijan-icon-search-normal",
];
$options = Options::get_options( $default_options );
if( !Utils::to_bool( $options['show-header-search'] ) ) return;

get_template_part( "templates/components/ajax-search", null, [
	'placeholder'	=> $options['header-search-placeholder'],
	'icon'			=> $options['header-search-icon'],
	'form_classes'	=> ['header-search'],
	'form_id'		=> 'header-search',
	'input_id'		=> 'header-search-field',
	'button_id'		=> 'header-search-submit',
	'popover_id'	=> 'header-search-popup',
] );