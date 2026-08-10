<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<div id="mobile-menu-container" class="mobile-menu-container hide-desktop" role="navigation" aria-label="<?php echo esc_attr__( 'Mobile menu', 'bijan' ) ?>">
	<div class="mobile-menu-panel-title"><?php esc_html_e( 'Menu', 'bijan' ) ?></div>
	<div class="mobile-menu-search-wrap">
		<?php get_template_part( "templates/bottom_nav/search" ) ?>
	</div>

	<div class="mobile-menu-scroll">
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
</div>
