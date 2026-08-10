<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<nav class="footer-menu-wrap">
	<div class="footer-section-title">
		<i class="footer-section-title-icon <?php echo $args['icon'] ?>"></i>
		<span class="footer-section-title-text"><?php echo $args['title'] ?></span>
	</div>
	<?php
	wp_nav_menu( [
		'theme_location'	=> "footer-{$args['menu']}",
		'container_class'	=> "footer-{$args['menu']}",
		'fallback_cb'		=> false,
	] );
	?>
</nav>