<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\Page;

if( !defined( 'ABSPATH' ) ) exit;

$disable_header = false;

$options = Options::get_options( [
	'show_header'				=> true,
	'sticky_header'				=> true,
	'auto_hide_header'			=> true,
	'show_bottom_header'		=> true,
	'sticky_bottom_header'		=> true,
	'show_footer'				=> false,
	'show-header-menu'			=> true,
	'show-header-second-menu'	=> true,
] );

$disable_header = !Utils::to_bool( $options['show_header'] );
$is_sticky = Utils::to_bool( $options['sticky_header'] );
$disable_footer = !Utils::to_bool( $options['show_footer'] );

$body_classes = [];

if( is_page() ) {
	$page_options = Page::get_options();
	if( $page_options['disable_header'] === true ) {
		if(
			$page_options['disable_header_user'] === 'all' ||
			( !$logged_in && $page_options['disable_header_user'] === 'guests' ) ||
			( $logged_in && $page_options['disable_header_user'] === 'users' )
		) {
			$disable_header = true;
		}
	}
	if( $page_options['disable_footer'] === true ) {
		if(
			$page_options['disable_footer_user'] === 'all' ||
			( !$logged_in && $page_options['disable_footer_user'] === 'guests' ) ||
			( $logged_in && $page_options['disable_footer_user'] === 'users' )
		) {
			$disable_footer = true;
		}
	}
}

if( $disable_header ) {
	$body_classes[] = 'header_disabled';
} else {
	$body_classes[] = $is_sticky ? 'sticky-header' : 'static-header';
	if( $is_sticky && $options['auto_hide_header'] ) {
		$body_classes[] = 'auto-hide-header';
	}
}
if( !$options['show_bottom_header'] ) {
	$body_classes[] = 'bottom_header_disabled';
} else {
	$body_classes[] = $options['sticky_bottom_header'] ? 'sticky-bottom-header' : 'static-bottom-header';
}
if( $disable_footer ) {
	$body_classes[] = 'footer_disabled';
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php wp_head(); ?>
	</head>

	<body <?php body_class( $body_classes ); ?>>
		<?php wp_body_open(); ?>
		<div id="container">
			<?php if( !$disable_header ) { ?>
				<?php if( !function_exists( 'elementor_theme_do_location' ) || !elementor_theme_do_location( 'header' ) ) { ?>
					<header id="header-container">
						<div class="page-width">
							<div class="header" id="header">
								<?php echo file_get_contents( BIJAN_DIR . "assets/img/curves/header.svg" ) ?>
								<div id="header-toggle-mobile-menu" class="hide-desktop toggle-mobile-menu" tabindex="1"><i class="bijan-icon-grid"></i></div>
								<div id="branding"><?php get_template_part( "templates/header/branding" ); ?></div>
								<nav id="header-search-wrap" class="show-only-desktop"><?php get_template_part( "templates/header/search" ); ?></nav>
								<div id="header-actions" class="show-only-desktop"><?php get_template_part( "templates/header/actions" ); ?></div>
							</div>
						</div>
					</header>

					<?php
					if( $options['show_bottom_header'] ) {
						if( $options['show-header-menu'] || $options['show-header-second-menu'] ) {
							if( has_nav_menu( 'main-menu' ) || has_nav_menu( 'header-second-menu' ) ) {
								?>
								<div id="bottom-header" class="show-only-desktop">
									<div id="bottom-header-inner" class="page-width">
										<?php if( $options['show-header-menu'] ) { ?>
											<div id="bottom-header-menu" class="menu-wrap">
												<?php
												wp_nav_menu( [
													'theme_location'	=> 'main-menu',
													'container_class'	=> 'main-menu',
													'fallback_cb'		=> false,
												] );
												?>
											</div>
										<?php } ?>
				
										<?php if( $options['show-header-second-menu'] ) { ?>
											<div id="bottom-header-second-menu" class="menu-wrap">
												<?php
												wp_nav_menu( [
													'theme_location'	=> 'header-second-menu',
													'container_class'	=> 'header-second-menu',
													'fallback_cb'		=> false,
												] );
												?>
											</div>
										<?php } ?>
									</div>
								</div>
								<?php
							}
						}
					}
				}
			}