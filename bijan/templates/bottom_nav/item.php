<?php

use Bijan\Utils;
use Bijan\Utils\Notifications;
use Bijan\Utils\Options;
use MJ\Whitebox\Utils\WC as WhiteboxWC;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'icon'		=> '',
	'text'		=> '',
	'url'		=> '',
	'special'	=> 'none',
] );

$options = Options::get_options( [
	'notifications'	=> true,
] );

$classes = ['bottom-nav-item'];
if( $args['special'] && $args['special'] != 'none' ) {
	$classes[] = 'bottom-nav-item-' . $args['special'];
	if( $args['special'] == 'categories' ) {
		$classes[] = "toggle-mobile-menu";
	} else if( $args['special'] == 'account' && is_user_logged_in() ) {
		$classes[] = "toggle-account-menu";
	}
}
?>
<a href="<?php echo $args['url'] ? esc_url( $args['url'] ) : '#' ?>" class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
	<div class="bottom-nav-item-icon">
		<i class="<?php echo esc_attr( $args['icon'] ) ?>"></i>
		<?php if( $args['special'] == 'cart' ) { ?>
			<div class="bottom-nav-cart-count cart-count bijan-count-badge"><?php echo WhiteboxWC::get_cart_count() ?></div>
		<?php } ?>
		<?php
		if( $args['special'] == 'account' ) {
			if( Utils::to_bool( $options['notifications'] ) ) {
				if( $count = Notifications::count_user_unread() ) {
					?>
					<div class="bottom-nav-notifications-count bijan-count-badge"><?php echo $count ?></div>
					<?php
				}
			}
		}
		?>
	</div>
	<div class="bottom-nav-item-text"><?php echo esc_html( $args['text'] ) ?></div>
</a>