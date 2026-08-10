<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'show-cart'					=> true,
	'cart-icon'					=> 'bijan-icon-shopping-cart',
	'show-mini-cart'			=> true,
	'show-account-btn'			=> true,
	'account-btn-icon'			=> 'bijan-icon-user',
	'account-btn-link'			=> '',
	'account-btn-link-newtab'	=> false,
] );
if( Utils::to_bool( $options['show-cart'] ) && Utils::is_wc_active() ) {
	get_template_part( "templates/header/action", 'mini_cart', [
		'cart-icon'			=> WhiteboxUtils::get_icon( $options['cart-icon'], 'header-action-icon header-cart-icon' ),
		'show-mini-cart'	=> $options['show-mini-cart'],
	] );
}
if( Utils::to_bool( $options['show-account-btn'] ) ) {
	get_template_part( "templates/components/account-btn", null, [
		'classes'		=> ['header-action', 'header-account-wrap'],
		'link'			=> $options['account-btn-link'],
		'newtab'		=> $options['account-btn-link-newtab'],
		'link_classes'	=> ['header-account'],
		'link_id'		=> 'header-account',
		'btn_icon'		=> $options['account-btn-icon'],
		'icon_classes'	=> ['header-action-icon', 'header-account-icon'],
	] );
}