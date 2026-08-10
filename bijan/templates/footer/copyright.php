<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<?php if( !empty( $args['footer_copyright'] ) ) { ?>
	<div id="footer-copyright">
		<?php echo wpautop( $args['footer_copyright'] ) ?>
	</div>
<?php
}

if( $args['footer_socials_position'] == 'front_copyright' || $args['footer_socials_position'] == 'bottom_copyright' ) {
	get_template_part( "templates/footer/socials", null, $args );
}