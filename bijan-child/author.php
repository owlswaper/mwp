<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$author = function_exists( 'cloz_blog_author' ) ? cloz_blog_author() : null;
$queried = get_queried_object();
if ( ! $author || ! $queried instanceof WP_User || (int) $queried->ID !== (int) $author['user_id'] ) {
	include get_template_directory() . '/archive.php';
	return;
}

$page = max( 1, absint( get_query_var( 'paged' ) ) );
$posts = new WP_Query( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => (int) get_option( 'posts_per_page', 10 ), 'paged' => $page, 'ignore_sticky_posts' => true ] );
get_header();
?>
<div id="page-body" class="page-width cloz-author-page" dir="rtl">
	<main id="page-main">
		<?php if ( function_exists( 'bijan_breadcrumb' ) ) { bijan_breadcrumb(); } ?>
		<div class="cloz-author-profile" aria-labelledby="cloz-author-title">
			<div class="cloz-author-profile-head">
				<?php if ( $author['image_id'] ) : ?><div class="cloz-author-profile-avatar"><?php echo wp_get_attachment_image( absint( $author['image_id'] ), 'thumbnail', false, [ 'alt' => $author['name'], 'decoding' => 'async' ] ); ?></div><?php endif; ?>
				<div><span class="cloz-author-eyebrow">درباره نویسنده</span><h1 id="cloz-author-title"><?php echo esc_html( $author['name'] ); ?></h1><?php if ( $author['summary'] ) : ?><p class="cloz-author-intro"><?php echo nl2br( esc_html( $author['summary'] ) ); ?></p><?php endif; ?></div>
			</div>
			<?php if ( $author['bio'] ) : ?><div class="cloz-author-biography"><?php echo wp_kses_post( wpautop( $author['bio'] ) ); ?></div><?php endif; ?>
			<?php echo cloz_blog_author_socials( $author ); ?>
		</div>
		<section class="cloz-author-articles" aria-labelledby="cloz-author-articles-title">
			<div class="cloz-author-articles-head"><h2 id="cloz-author-articles-title">مقاله‌های <?php echo esc_html( $author['name'] ); ?></h2><span><?php echo esc_html( number_format_i18n( $posts->found_posts ) ); ?> مقاله</span></div>
			<?php if ( $posts->have_posts() ) : ?><div class="cloz-author-post-grid list-posts">
				<?php while ( $posts->have_posts() ) : $posts->the_post(); get_template_part( 'templates/archives/post' ); endwhile; wp_reset_postdata(); ?>
			</div>
			<?php echo paginate_links( [ 'total' => $posts->max_num_pages, 'current' => $page, 'type' => 'list', 'prev_text' => 'قبلی', 'next_text' => 'بعدی' ] ); ?>
			<?php else : ?><p>هنوز مقاله‌ای منتشر نشده است.</p><?php endif; ?>
		</section>
	</main>
</div>
<?php get_footer(); ?>
