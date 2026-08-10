<?php
use Bijan\Utils;
use Bijan\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$default_options = [
	'show-logo'					=> true,
	'logo-link'					=> home_url(),
	'logo-type'					=> 'img',
	'logo-img'					=> BIJAN_URI . "assets/img/logo.svg",
	'logo-img-size'				=> [
		'width'		=> 108,
		'height'	=> 30,
	],
	'homepage-site-title-tag'	=> 'h1',
	'otherpage-site-title-tag'	=> 'div',
];
$options = Options::get_options( $default_options );
if( !Utils::to_bool( $options['show-logo'] ) ) return;

// Backward compatibility
if( empty( $options['logo-img-size'] ) ) {
	$options['logo-img-size'] = [
		'width'		=> $default_options['logo-img-size']['width'],
		'height'	=> $default_options['logo-img-size']['height'],
	];
}
if( empty( $options['logo-img-size']['width'] ) ) {
	$options['logo-img-size']['width'] = $default_options['logo-img-size']['width'];
}
if( empty( $options['logo-img-size']['height'] ) ) {
	$options['logo-img-size']['height'] = $default_options['logo-img-size']['height'];
}

$site_title_tag = $options['otherpage-site-title-tag'];
if( is_front_page() || is_home() ) {
	$site_title_tag = $options['homepage-site-title-tag'];
}
?>
<<?php echo tag_escape( $site_title_tag ) ?> class="site-title">
	<?php if( !empty( $options['logo-link'] ) ) { ?>
		<a href="<?php echo esc_url( $options['logo-link'] ) ?>" class="site-title-inner" title="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
	<?php } else { ?>
		<div class="site-title-inner">
	<?php } ?>
			<span id="site-logo">
				<?php echo Options::get_logo( [
					'type'			=> 'logo-type',
					'text-type'		=> 'logo-text-type',
					'text-custom'	=> 'logo-text-custom',
					'img'			=> 'logo-img',
					'img-size'		=> 'logo-img-size',
				], $default_options ) ?>
			</span>
	<?php if( !empty( $options['logo-link'] ) ) { ?>
		</a>
	<?php } else { ?>
		</div>
	<?php } ?>
</<?php echo tag_escape( $site_title_tag ) ?>>