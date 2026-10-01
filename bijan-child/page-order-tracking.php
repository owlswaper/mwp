<?php
/** Tracking uses the native header, footer and login modal. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div id="page-body" class="clz-tracking-page"><main id="page-main"><?php echo CLZ_Order_Tracking::shortcode(); ?></main></div>
<?php get_footer();
