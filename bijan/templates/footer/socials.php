<?php

use Bijan\Utils;

if( !empty( $args['footer_socials_items']['footer_social_icon'] ) ) {
	$wrap_attrs = [
		'id'	=> 'footer-social-items'
	];
	if( $args['footer_socials_position'] == 'bottom_copyright' ) {
		$wrap_attrs['class'][] = 'fullwidth';
	}
	?>
	<div <?php echo Utils::get_html_attributes( $wrap_attrs ) ?>>
		<?php
		if( $args['show_footer_socials_items'] ) {
			foreach( $args['footer_socials_items']['footer_social_icon'] as $index => $social_icon ) {
				if( empty( $args['footer_socials_items']['footer_social_link'][$index] ) ) continue;
				?>
				<a href="<?php echo esc_url( $args['footer_socials_items']['footer_social_link'][$index] ) ?>" target="_blank" rel="nofollow noopener" class="footer-social-item">
					<i class="footer-social-icon <?php echo esc_attr( $social_icon ) ?>"></i>
				</a>
				<?php
			}
		}
		?>
	</div>
<?php } ?>