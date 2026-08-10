<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<div id="mobile-menu-container" class="mobile-menu-container hide-desktop">
	<?php get_template_part( "templates/bottom_nav/search" ) ?>

	<?php
	if( has_nav_menu( 'mobile-menu' ) ) {
		wp_nav_menu( [
			'theme_location'	=> 'mobile-menu',
			'container_class'	=> 'mobile-menu-wrap'
		] );
	}

	if( has_nav_menu( 'mobile-second-menu' ) ) {
		wp_nav_menu( [
			'theme_location'	=> 'mobile-second-menu',
			'container_class'	=> 'mobile-menu-wrap',
			'fallback_cb'		=> false,
		] );
	}
	?>
</div>