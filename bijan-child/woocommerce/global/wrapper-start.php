<?php
/** Keep archive wrappers balanced; single-product markup stays with the parent. */
defined( 'ABSPATH' ) || exit;
if ( ! Cloz_Archive_Ajax_Filters::is_product_archive() ) {
	include get_template_directory() . '/woocommerce/global/wrapper-start.php';
	return;
}
$GLOBALS['cloz_archive_wrapper_open'] = false;
?>
<div id="page-body" class="page-width">
	<main id="page-main">
		<?php do_action( 'bijan/wc/archive/before_primary' ); ?>
