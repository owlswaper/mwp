<?php

use Bijan\Utils;
use MJ\Whitebox\Utils\Sanitizers as WhiteboxSanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$phones = [];
if( !empty( $args['footer_contact_info'] ) ) {
	$contact_infos = [];
	$is_rtl = is_rtl();
	foreach( $args['footer_contact_info'] as $item ) {
		if( is_email( $item ) ) {
			$contact_infos[] = '<a href="mailto:' . $item . '" class="footer-phone">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		} else if( is_numeric( WhiteboxSanitizers::phone( $item ) ) ) {
			$contact_infos[] = '<a href="tel:' . WhiteboxSanitizers::phone( $item ) . '" class="footer-phone">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		} else {
			$contact_infos[] = '<a href="' . $item . '" class="footer-phone">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		}
	}
}
?>
<div id="footer-more-info-inner">
	<div id="footer-more-info-top">
		<div id="footer-more-info-title"><?php echo $args['footer_more_info_title'] ?></div>
		<div id="footer-more-info-subtitle" class="footer-more-info-subtitle"><?php echo $args['footer_more_info_subtitle'] ?></div>
	</div>
	
	<div id="footer-more-info-contact-wrap">
		<?php if( !empty( $contact_infos ) ) { ?>
			<div id="footer-more-info-phones" class="footer-more-info-phones-<?php echo esc_attr( $args['footer_contact_info_color_type'] ) ?>">
				<?php echo implode( '<span class="footer-phone-sep">-</span>', $contact_infos ) ?>
			</div>
		<?php } ?>

		<div id="footer-more-info-contact-subtitle" class="footer-more-info-subtitle"><?php echo wpautop( $args['footer_contact_subtitle'] ) ?></div>
	</div>
</div>
<?php

if( $args['footer_org_logos_position'] == 'contact' ) {
	get_template_part( "templates/footer/org_logos", null, $args );
}

if( $args['footer_socials_position'] == 'contact' ) {
	get_template_part( "templates/footer/socials", null, $args );
}