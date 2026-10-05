<?php
/** Native footer menu links with named navigation and compact group headings. */
defined( 'ABSPATH' ) || exit;
$title = wp_strip_all_tags( $args['title'] ?? '' );
$heading_id = wp_unique_id( 'cloz-footer-menu-' );
?>
<nav class="footer-menu-wrap cloz-footer-menu" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<h3 class="footer-section-title" id="<?php echo esc_attr( $heading_id ); ?>">
		<i class="footer-section-title-icon <?php echo esc_attr( $args['icon'] ?? '' ); ?>" aria-hidden="true"></i>
		<span class="footer-section-title-text"><?php echo esc_html( $title ); ?></span>
	</h3>
	<?php wp_nav_menu( [
		'theme_location' => 'footer-' . $args['menu'],
		'container_class' => 'footer-' . $args['menu'],
		'menu_class' => 'menu cloz-footer-links',
		'fallback_cb' => false,
	] ); ?>
</nav>
