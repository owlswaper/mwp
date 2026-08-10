<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<?php if( !empty( $args['footer_about'] ) ) { ?>
	<div id="footer-about">
		<?php echo wpautop( $args['footer_about'] ); ?>
	</div>
<?php
}

if( $args['footer_org_logos_position'] == 'about' ) {
	get_template_part( "templates/footer/org_logos", null, $args );
}

if( $args['footer_socials_position'] == 'about' ) {
	get_template_part( "templates/footer/socials", null, $args );
}