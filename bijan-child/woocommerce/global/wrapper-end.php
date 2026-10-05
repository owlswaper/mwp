<?php
/** Close the product-list container before the primary archive container. */
defined( 'ABSPATH' ) || exit;
if ( ! Cloz_Archive_Ajax_Filters::is_product_archive() ) {
	include get_template_directory() . '/woocommerce/global/wrapper-end.php';
	return;
}
?>
			</div><!-- .entry-container -->
			<?php do_action( 'bijan/wc/archive/end_primary' ); ?>
		</div><!-- #primary -->
		<?php do_action( 'bijan/wc/archive/after_primary' ); ?>
	</main>
</div>
