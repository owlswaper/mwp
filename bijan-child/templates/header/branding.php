<?php
use Bijan\Utils;
use Bijan\Utils\Options;

defined( 'ABSPATH' ) || exit;

$default_options = array(
	'show-logo'                => true,
	'logo-link'                => home_url(),
	'logo-type'                => 'img',
	'logo-img'                 => BIJAN_URI . 'assets/img/logo.svg',
	'logo-img-size'            => array(
		'width'  => 108,
		'height' => 30,
	),
	'homepage-site-title-tag'  => 'h1',
	'otherpage-site-title-tag' => 'div',
);
$options = Options::get_options( $default_options );

if ( ! Utils::to_bool( $options['show-logo'] ) ) {
	return;
}

// Backward compatibility with older theme option data.
if ( empty( $options['logo-img-size'] ) ) {
	$options['logo-img-size'] = $default_options['logo-img-size'];
}
$options['logo-img-size']['width']  = ! empty( $options['logo-img-size']['width'] ) ? $options['logo-img-size']['width'] : $default_options['logo-img-size']['width'];
$options['logo-img-size']['height'] = ! empty( $options['logo-img-size']['height'] ) ? $options['logo-img-size']['height'] : $default_options['logo-img-size']['height'];

$site_title_tag = ( is_front_page() || is_home() ) ? $options['homepage-site-title-tag'] : $options['otherpage-site-title-tag'];
?>
<<?php echo tag_escape( $site_title_tag ); ?> class="site-title">
	<?php if ( ! empty( $options['logo-link'] ) ) : ?>
		<a href="<?php echo esc_url( $options['logo-link'] ); ?>" class="site-title-inner" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<?php else : ?>
		<div class="site-title-inner">
	<?php endif; ?>
		<span id="site-logo">
			<?php
			echo Options::get_logo( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'type'        => 'logo-type',
					'text-type'   => 'logo-text-type',
					'text-custom' => 'logo-text-custom',
					'img'         => 'logo-img',
					'img-size'    => 'logo-img-size',
				),
				$default_options
			);
			?>
		</span>
	<?php if ( ! empty( $options['logo-link'] ) ) : ?>
		</a>
	<?php else : ?>
		</div>
	<?php endif; ?>
</<?php echo tag_escape( $site_title_tag ); ?>>

<a
	class="clz-mobile-support hide-desktop"
	href="<?php echo esc_url( apply_filters( 'clz_mobile_support_url', clz_contact_page_url() ) ); ?>"
	aria-label="پشتیبانی و تماس با ما"
	title="پشتیبانی و تماس با ما"
>
	<span class="clz-mobile-support-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M4.5 12.5v-1a7.5 7.5 0 0 1 15 0v1" />
			<path d="M6.8 11.5H5.7a2.2 2.2 0 0 0-2.2 2.2v2.1A2.2 2.2 0 0 0 5.7 18h1.1a.7.7 0 0 0 .7-.7v-5.1a.7.7 0 0 0-.7-.7ZM17.2 11.5h1.1a2.2 2.2 0 0 1 2.2 2.2v2.1a2.2 2.2 0 0 1-2.2 2.2h-1.1a.7.7 0 0 1-.7-.7v-5.1a.7.7 0 0 1 .7-.7Z" />
			<path d="M17.5 18c-.45 1.45-1.65 2.2-3.6 2.2h-1.1" />
			<circle cx="11.7" cy="20.2" r="1" fill="currentColor" stroke="none" />
		</svg>
		<span class="clz-mobile-support-status"></span>
	</span>
	<span class="clz-mobile-support-label">پشتیبانی</span>
</a>
