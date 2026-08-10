<?php
use Bijan\Utils;
use Bijan\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$default_options = [
	'show-mobile-search'		=> true,
	'mobile-search-placeholder'	=> esc_html__( "Search...", 'bijan' ),
	'mobile-search-icon'		=> "bijan-icon-search-normal",
];
$options = Options::get_options( $default_options );
if( !Utils::to_bool( $options['show-mobile-search'] ) ) return;

get_template_part( "templates/components/ajax-search", null, [
	'placeholder'	=> $options['mobile-search-placeholder'],
	'icon'			=> $options['mobile-search-icon'],
	'form_classes'	=> ['mobile-search'],
	'form_id'		=> 'mobile-search',
	'input_id'		=> 'mobile-search-field',
	'button_id'		=> 'mobile-search-submit',
	'popover_id'	=> 'mobile-search-popup',
] );