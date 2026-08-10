<?php
namespace Bijan\ElementorControls;

use MJ\Whitebox\ElementorControls\Slider as WhiteboxSlider;

class Slider extends WhiteboxSlider {
	public static $default_next_arrow_icon = [
		'library'	=> 'bijan-icon',
		'value'		=> 'bijan-icon-arrow-left',
	];
	public static $default_prev_arrow_icon = [
		'library'	=> 'bijan-icon',
		'value'		=> 'bijan-icon-arrow-right',
	];
}