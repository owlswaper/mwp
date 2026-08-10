<?php

use Bijan\Utils;

$is_rtl = is_rtl();

$default_args = [
	'transparent'		=> true,
	'small'				=> true,
	'classes'			=> ['bijan-slider-nav-btn'],
	'next_arrow_icon'	=> $is_rtl ? 'bijan-icon-arrow-left' : 'bijan-icon-arrow-right',
	'prev_arrow_icon'	=> $is_rtl ? 'bijan-icon-arrow-right' : 'bijan-icon-arrow-left',
];
$args = Utils::check_default( $args, $default_args, ['next_arrow_icon', 'prev_arrow_icon'] );

$nav_btn = [
	'transparent'	=> $args['transparent'],
	'small'			=> $args['small'],
	'classes'		=> $args['classes'],
];
$next_nav_btn = $nav_btn+['icon' => $args['next_arrow_icon']];
$next_nav_btn['classes'][] = 'bijan-slider-nav-next';
$next_nav_btn['classes'][] = 'swiper-button-next';
$prev_nav_btn = $nav_btn+['icon' => $args['prev_arrow_icon']];
$prev_nav_btn['classes'][] = 'bijan-slider-nav-prev';
$prev_nav_btn['classes'][] = 'swiper-button-prev';
get_template_part( "templates/components/button", null, $next_nav_btn );
get_template_part( "templates/components/button", null, $prev_nav_btn );