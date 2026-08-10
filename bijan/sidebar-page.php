<?php

use Bijan\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'sidebar-mobile-opener-icon'	=> is_rtl() ? 'bijan-icon-double-arrow-left' : 'bijan-icon-double-arrow-right',
	'sidebar-mobile-close-icon'		=> is_rtl() ? 'bijan-icon-double-arrow-right' : 'bijan-icon-double-arrow-left',
] );
?>
<div class="sidebar-mobile-expand-btn" tabindex="0" role="link" aria-label="<?php esc_attr_e( "Open sidebar", 'bijan' ) ?>" aria-controls="sidebar" aria-expanded="false">
	<span class="screen-reader-text"><?php esc_html_e( "Open sidebar", 'bijan' ) ?></span>
	<i class="<?php echo $options['sidebar-mobile-opener-icon'] ?> sidebar-open-icon" aria-hidden="true"></i>
	<i class="<?php echo $options['sidebar-mobile-close-icon'] ?> sidebar-close-icon" aria-hidden="true"></i>
</div>

<aside id="sidebar" class="sidebar sidebar-page col-md-3 col-sm-12" aria-label="<?php esc_attr_e( 'Page Sidebar', 'bijan' ) ?>">
	<section id="widget-area" class="widget-area" role="complementary" aria-label="<?php esc_attr_e( 'Page Widgets', 'bijan' ) ?>">
		<?php dynamic_sidebar( 'page' ); ?>
	</section>
</aside>