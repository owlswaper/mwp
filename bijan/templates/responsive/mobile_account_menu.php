<?php

use Bijan\Utils;
use Bijan\Utils\User;

if( !defined( 'ABSPATH' ) ) exit;

if( !is_user_logged_in() ) return;

$account_btn_items = User::get_account_menu_items();
$home_url = home_url();
$account_btn_link = '';
$my_account_link = Utils::is_wc_active() ? home_url( 'my-account' ) : $home_url;
if( is_user_logged_in() ) {
	$account_btn_link = $my_account_link;
} else {
	$account_btn_link = '#';
}
?>
<div id="mobile-account-menu-container" class="mobile-menu-container hide-desktop">
	<ul id="mobile-account-menu-items" class="account-items">
		<li class="account-name">
			<a href="<?php echo esc_url( $account_btn_link ) ?>" class="account-name-link">
				<div id="mobile-account-menu-avatar-wrap">
					<i class="bijan-icon-user"></i>
				</div>
				<span class="account-name-text line-clamp line-clamp-1"><?php echo esc_html( wp_get_current_user()->display_name ) ?></span>
				<?php if( is_rtl() ) { ?>
					<i class="bijan-icon-left"></i>
				<?php } else { ?>
					<i class="bijan-icon-right"></i>
				<?php } ?>
			</a>
		</li>
		<?php foreach( $account_btn_items as $index => $item ) { ?>
			<li class="account-item"<?php echo is_string( $index ) ? ' id="account-' . $index . '"' : '' ?>>
				<a href="<?php echo $item['link'] ?>" class="account-item-link">
					<?php if( !empty( $item['icon'] ) ) { ?>
						<i class="account-item-icon <?php echo $item['icon'] ?>"></i>
						<span class="account-item-label"><?php echo esc_html( $item['label'] ) ?></span>
					<?php } ?>
				</a>
			</li>
		<?php } ?>
	</ul>
</div>