<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\Page;

if( !defined( 'ABSPATH' ) ) exit;

$home = home_url();
$default_options = [
	'show_footer'		=> true,
	'footer_menu_count'	=> 2,

	'footer_show_newsletter'			=> true,
	'footer_newsletter_icon'			=> 'bijan-icon-message-text',
	'footer_newsletter_title'			=> __( 'Subscribe to newsletter', 'bijan' ),
	'footer_newsletter_subtitle'		=> __( 'Get the latest notifications', 'bijan' ),
	'footer_newsletter_shortcode'		=> 'newsletter_form',
	'footer_newsletter_sms_shortcode'	=> 'contact-form-7 id="28f84a4"',

	'footer_show_menu1'		=> true,
	'footer_menu1_icon'		=> 'bijan-icon-grid',
	'footer_menu1_title'	=> __( 'Menu', 'bijan' ),

	'footer_show_menu2'		=> true,
	'footer_menu2_icon'		=> 'bijan-icon-call',
	'footer_menu2_title'	=> __( 'Contact us', 'bijan' ),

	'footer_about'	=> __( "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.", 'bijan' ),

	'show_footer_org_logos'		=> true,
	'footer_org_logos_position'	=> 'about',
	'footer_before_org_items'	=> [],
	'footer_orgs_logo_items'	=> [
		'org_logos'	=> [
			'<img src="https://mjkhajeh.ir/bijan/wp-content/uploads/2024/11/samandehi.png" alt="">',
			'<img src="https://mjkhajeh.ir/bijan/wp-content/uploads/2024/11/enamad.png" alt="">',
			'<img src="https://mjkhajeh.ir/bijan/wp-content/uploads/2024/11/kasbokar.png" alt="">',
		]
	],
	'footer_after_org_items'	=> [],

	'footer_more_info_title'	=> __( "A real hypermarket!", 'bijan' ),
	'footer_more_info_subtitle'	=> __( "With a professional and powerful team, we will always be by your side.", 'bijan' ),
	'footer_contact_info'	=> [
		'0215853202',
		'0213456700',
	],
	'footer_contact_info_color_type'	=> 'just_first',
	'footer_contact_subtitle'	=> __( 'We are available 24/7 to answer your questions.', 'bijan' ),

	'show_footer_market_btns'	=> true,
	'footer_before_market_btns'	=> [],
	'footer_market_btns'		=> [
		'market_logos'			=> [
			'google_play',
			'cafebazar',
			'app_store',
		],
		'market_btn_top_text'	=> [
			__( 'Download from', 'bijan' ),
			__( 'Download from', 'bijan' ),
			__( 'PWA version', 'bijan' ),
		],
		'market_btn_text'		=> [
			__( "Play store", 'bijan' ),
			__( "Cafebazar", 'bijan' ),
			__( "Webapp", 'bijan' ),
		],
		'market_btn_link'		=> [
			'#',
			'#',
			'#',
		],
	],
	'footer_after_market_btns'	=> [],

	'show_footer_socials_items'	=> true,
	'footer_socials_position'	=> 'front_copyright',
	'footer_socials_items'		=> [
		'footer_social_icon'	=> [
			'bijan-icon-instagram',
			'bijan-icon-linkedin',
			'bijan-icon-twitter',
		],
		'footer_social_link'	=> [
			'#',
			'#',
			'#',
		]
	],
	'footer_copyright' => __( "All rights of this website belong to Bijan store", 'bijan' ),
];
$options = Options::get_options( $default_options );

$disable_footer = !Utils::to_bool( $options['show_footer'] );

if( is_page() ) {
	$page_options = Page::get_options();
	if( $page_options['disable_footer'] === true ) {
		if(
			$page_options['disable_footer_user'] === 'all' ||
			( !$logged_in && $page_options['disable_footer_user'] === 'guests' ) ||
			( $logged_in && $page_options['disable_footer_user'] === 'users' )
		) {
			$disable_footer = true;
		}
	}
}
?>
			<?php if( $disable_footer === false ) { ?>
				<?php if( !function_exists( 'elementor_theme_do_location' ) || !elementor_theme_do_location( 'footer' ) ) { ?>
					<footer id="site-footer" class="site-footer">
						<div class="page-width" id="footer">
							<?php
							if( Utils::to_bool( $options['footer_show_newsletter'] ) ) {
								get_template_part( "templates/footer/newsletter", null, $options );
							}
							?>
							<div class="footer-content" id="top-footer">
								<div class="footer-menus" style="--menus-count:<?php echo esc_attr( $options['footer_menu_count'] ) ?>">
									<?php
									for( $index = 1; $index <= $options['footer_menu_count']; $index++ ) {
										if( !empty( $options["footer_show_menu" . $index] ) && Utils::to_bool( $options["footer_show_menu" . $index] ) ) {
											$menu = 'menu';
											// Backward
											if( $index === 2 ) {
												$menu = 'contact-menu';
											} else if( $index === 3 ) {
												$menu = 'menu-3';
											}
											get_template_part( "templates/footer/menu", null, [
												'menu'	=> $menu,
												'icon'	=> $options["footer_menu{$index}_icon"],
												'title'	=> $options["footer_menu{$index}_title"],
											] );
										}
									}
									?>
								</div>

								<div id="footer-about-wrap">
									<?php
									if( !empty( $options['footer_about'] ) || ( Utils::to_bool( $options['show_footer_org_logos'] ) && ( !empty( $options['footer_before_org_items'] ) || !empty( $options['footer_orgs_logo_items'] ) || !empty( $options['footer_after_org_items'] ) ) ) ) {
										get_template_part( "templates/footer/about", null, $options );
									}
									?>
								</div>

								<div id="footer-more-info-wrap">
									<?php
									if( !empty( $options['footer_more_info_title'] ) || !empty( $options['footer_more_info_subtitle'] ) || !empty( $options['footer_contact_info'] ) ) {
										get_template_part( "templates/footer/more_info", null, $options );
									}
									?>
								</div>
							</div>

							<?php
							if( Utils::to_bool( $options['show_footer_market_btns'] ) ) {
								if( !empty( $options['footer_before_market_btns'] ) || !empty( $options['footer_market_btns'] ) || !empty( $options['footer_after_market_btns'] ) ) { ?>
									<div class="footer-content" id="footer-market-buttons">
										<?php get_template_part( "templates/footer/market_btns", null, $options ) ?>
									</div>
									<?php
								}
							}
							?>

							<?php if( !empty( $options['footer_copyright'] ) || ( Utils::to_bool( $options['show_footer_socials_items'] ) && !empty( $options['footer_socials_items'] ) ) ) { ?>
								<div class="footer-content" id="footer-copyright-wrap">
									<?php get_template_part( "templates/footer/copyright", null, $options ) ?>
								</div>
							<?php } ?>
						</div>
					</footer>
				<?php } ?>
			<?php } ?>
		</div>
		<?php wp_footer(); ?>
	</body>
</html>