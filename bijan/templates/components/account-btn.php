<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;
use Bijan\Utils\Notifications;
use Bijan\Utils\Options;
use Bijan\Utils\User;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'notifications'	=> true,
] );

$account_btn_link = Utils::is_wc_active() ? home_url( 'my-account' ) : home_url();
if( !is_user_logged_in() ) {
	$account_btn_link = '';
}
$args = Utils::check_default( $args, [
	'id'			=> '',
	'classes'		=> [],
	'link'			=> home_url(),
	'newtab'		=> false,
	'link_classes'	=> [],
	'link_id'		=> '',
	'icon'			=> 'bijan-icon-user',
	'icon_classes'	=> [],
], ['link', 'icon'] );
$account_btn_link = $args['link'];
if( !$account_btn_link && is_user_logged_in() ) {
	$account_btn_link = Utils::is_wc_active() ? home_url( 'my-account' ) : home_url();
}
$account_btn_items = User::get_account_menu_items();

$wrap_attrs = [
	'class'	=> array_merge( ['account-btn-wrap'], $args['classes'] ),
];
if( $args['id'] ) {
	$wrap_attrs['id'] = $args['id'];
}

if( is_string( $account_btn_link ) ) {
	$account_btn_args = [
		'href'	=> $account_btn_link ? esc_url( $args['link'] ) : '#',
	];
	if( $args['newtab'] ) {
		$account_btn_args['target'] = '_blank';
		$account_btn_args['rel'] = 'noopener';
	}
} else {
	$account_btn_args = Elementor::get_link_attributes( $args['link'] );
}
$account_btn_args['class'] = array_merge( ['account-btn-link'], $args['link_classes'] );
if( $args['link_id'] ) {
	$account_btn_args['id'] = $args['link_id'];
}
?>
<div <?php echo Utils::get_html_attributes( $wrap_attrs ) ?>>
	<a <?php echo Utils::get_html_attributes( $account_btn_args ) ?>><?php echo WhiteboxUtils::get_icon( $args['icon'], Utils::prepare_html_classes( array_merge( ['account-btn-icon'], $args['icon_classes'] ) ) ); ?></a>
	<?php if( !empty( $account_btn_items ) ) { ?>
		<i class="account-btn-arrow bijan-icon-bottom"></i>
	<?php } ?>
	<?php
	if( Utils::to_bool( $options['notifications'] ) ) {
		if( $count = Notifications::count_user_unread() ) {
			?>
			<div class="bijan-count-badge bottom"><?php echo $count ?></div>
			<?php
		}
	}
	?>
	<?php if( !empty( $account_btn_items ) ) { ?>
		<ul class="bijan-popover account-items">
			<?php if( is_user_logged_in() ) { ?>
				<li class="account-name">
					<a href="<?php echo !empty( $account_btn_args['href'] ) ? esc_url( $account_btn_args['href'] ) : '#' ?>" class="account-name-link">
						<span class="account-name-text line-clamp line-clamp-1"><?php echo esc_html( wp_get_current_user()->display_name ) ?></span>
						<?php if( is_rtl() ) { ?>
							<i class="bijan-icon-left"></i>
						<?php } else { ?>
							<i class="bijan-icon-right"></i>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
			<?php foreach( $account_btn_items as $index => $item ) { ?>
				<li class="account-item" data-index="<?php echo esc_attr( $index ) ?>">
					<a href="<?php echo $item['link'] ?>" class="account-item-link">
						<?php if( !empty( $item['icon'] ) ) { ?>
							<i class="account-item-icon <?php echo $item['icon'] ?>"></i>
						<?php } ?>
						<span class="account-item-label"><?php echo esc_html( $item['label'] ) ?></span>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
</div>