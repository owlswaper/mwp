<?php
/** Readable store pages with native editable content. */
defined( 'ABSPATH' ) || exit;
// template-loader includes this file in global scope. Keep all presentation data
// local: WP_Query::the_post() replaces the global $pages with pagination content.
( static function () {
$key = clz_information_key();
$pages = clz_information_pages();
if ( ! isset( $pages[ $key ] ) ) { return; }
$data = $pages[ $key ];
get_header();
?>
<div id="page-body" class="clz-info clz-info--<?php echo esc_attr( $key ); ?>" dir="rtl">
	<main id="page-main" class="clz-info-shell">
		<nav class="clz-info-breadcrumb" aria-label="مسیر صفحه"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html( $data['title'] ); ?></span></nav>
		<header class="clz-info-heading"><p class="clz-info-eyebrow"><?php echo esc_html( $data['eyebrow'] ); ?></p><h1><?php echo esc_html( $data['title'] ); ?></h1><p class="clz-info-lead"><?php echo esc_html( $data['lead'] ); ?></p></header>
		<div class="clz-info-content"><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div>
		<nav class="clz-info-related" aria-label="راهنمای خرید و اطلاعات کلوز"><h2>بیشتر درباره کلوز و خریدت بدان</h2><ul>
			<?php foreach ( $pages as $slug => $related ) : if ( $slug === $key ) { continue; } ?>
				<li><a href="<?php echo esc_url( clz_information_url( $slug ) ); ?>"><?php echo esc_html( $related['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul></nav>
	</main>
</div>
<?php
get_footer();
} )();
