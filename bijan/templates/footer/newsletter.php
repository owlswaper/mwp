<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<div id="newsletter-wrap">
	<div id="newsletter-icon-wrap">
		<i class="<?php echo $args['footer_newsletter_icon'] ?>"></i>
	</div>

	<div id="newsletter-texts">
		<div id="newsletter-title"><?php echo $args['footer_newsletter_title'] ?></div>
		<div id="newsletter-subtitle"><?php echo $args['footer_newsletter_subtitle'] ?></div>
	</div>

	<div id="newsletter-forms-wrap">
		<?php if( $args['footer_newsletter_shortcode'] ) { ?>
			<div id="newsletter-form-email" class="footer-newsletter-form">
				<?php
				$args['footer_newsletter_shortcode'] = str_replace( ["[", "]"], "", $args['footer_newsletter_shortcode'] );
				echo do_shortcode( "[{$args['footer_newsletter_shortcode']}]" );
				?>
			</div>
		<?php } ?>

		<?php if( $args['footer_newsletter_sms_shortcode'] ) { ?>
			<div id="newsletter-form-sms" class="footer-newsletter-form">
				<?php
				if( BIJAN_DEV ) {
					$args['footer_newsletter_sms_shortcode'] = '[contact-form-7 id="634e66a" title="خبرنامه پیامکی"]';
				}
				$args['footer_newsletter_sms_shortcode'] = str_replace( ["[", "]"], "", $args['footer_newsletter_sms_shortcode'] );
				echo do_shortcode( "[{$args['footer_newsletter_sms_shortcode']}]" );
				?>
			</div>
		<?php } ?>
	</div>
</div>