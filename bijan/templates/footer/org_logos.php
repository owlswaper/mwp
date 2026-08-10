<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<?php if( $args['show_footer_org_logos'] ) { ?>
	<div id="footer-org-items-wrap">
		<?php if( !empty( $args['footer_before_org_items']['before_org_logos'] ) ) { ?>
			<div id="footer-before-org-items" class="footer-custom-section">
				<?php
				foreach( $args['footer_before_org_items']['before_org_logos'] as $index => $item ) {
					echo '<div class="footer-custom-item">' . $item . '</div>';
				}
				?>
			</div>
		<?php } ?>
	
		<?php if( !empty( $args['footer_orgs_logo_items']['org_logos'] ) ) { ?>
			<div id="footer-orgs-logo-section" class="footer-custom-section">
				<?php
				foreach( $args['footer_orgs_logo_items']['org_logos'] as $index => $item ) {
					echo '<div class="footer-custom-item">' . $item . '</div>';
				}
				?>
			</div>
		<?php } ?>
	
		<?php if( !empty( $args['footer_after_org_items']['after_org_logos'] ) ) { ?>
			<div id="footer-after-orgs-logo-section" class="footer-custom-section">
				<?php
				foreach( $args['footer_after_org_items']['after_org_logos'] as $index => $item ) {
					echo '<div class="footer-custom-item">' . $item . '</div>';
				}
				?>
			</div>
		<?php } ?>
	</div>
<?php } ?>