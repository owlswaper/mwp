<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\Product;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'archive_breadcrumb'	=> true,
	'archive_show_title'	=> true,
	'archive_show_sidebar'	=> true,
	'archive_sidebar'		=> 'blog',
	'archive_desktop_cols'	=> 3,
	'archive_tablet_cols'	=> 2,
	'archive_mobile_cols'	=> 2,
] );

$has_sidebar = Utils::to_bool( $options['archive_show_sidebar'] ) && is_active_sidebar( $options['archive_sidebar'] );
get_header();
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
	?>
	<div id="page-body" class="page-width">
		<main id="page-main">
			<?php get_template_part( "templates/pages/head", null, [
				'options'	=> [
					'show_breadcrumb'	=> Utils::to_bool( $options['archive_breadcrumb'] ),
					'show_title'		=> Utils::to_bool( $options['archive_show_title'] ),
					'use_content_style'	=> false,
					'page_icon'			=> 'bijan-icon-book'
				],
				'is_archive'		=> true,
				'show_sort_archive'	=> true,
			] ); ?>

			<div id="primary" class="content-area archive row<?php echo $has_sidebar ? " content-area-with-sidebar" : '' ?>">
				<?php
				if( $has_sidebar ) {
					get_sidebar( $options['archive_sidebar'] );
				}

				$posts_args = [
					'id'	=> 'posts',
					'class'	=> [
						'site-content' ,'list-posts' ,'desktop-columns', 'tablet-columns', 'mobile-columns',
						"desktop-columns-{$options['archive_desktop_cols']}",
						"tablet-columns-{$options['archive_tablet_cols']}",
						"mobile-columns-{$options['archive_mobile_cols']}",
					],
					'role'		=> 'main',
					'style'		=> [
						'--desktop-cols'	=> $options['archive_desktop_cols'],
						'--tablet-cols'	=> $options['archive_tablet_cols'],
						'--mobile-cols'	=> $options['archive_mobile_cols'],
					]
				];
				?>

				<div id="search-results" class="<?php echo $has_sidebar ? 'col-md-9 col-sm-12' : 'col-12' ?>">
					<?php
					if( have_posts() ) {
						$categorized_posts = [];
						while( have_posts() ) {
							the_post();

							$categorized_posts[get_post_type()][] = get_post();
						}

						Utils::reposition_array_element( $categorized_posts, 'post', 9999 ); // Move the posts to end of the search

						$post_type_icons = [
							'post'		=> 'bijan-icon-book',
							'product'	=> 'bijan-icon-cart-happy',
						];

						foreach( $categorized_posts as $post_type => $posts ) {
							$post_type_object = get_post_type_object( $post_type );
							get_template_part( "templates/components/section_title", null, [
								'icon'	=> isset( $post_type_icons[$post_type] ) ? $post_type_icons[$post_type] : 'bijan-icon-flash',
								'title'	=> isset( $post_type_object->labels->name ) ? $post_type_object->labels->name : $post_type,
							] );
							if( $post_type == 'product' ) {
								wc_set_loop_prop( 'bijan_loop_props', Product::get_default_props() );
								woocommerce_product_loop_start();
							} else {
								echo "<div " . Utils::get_html_attributes( $posts_args ) . ">";
							}
							foreach( $posts as $index => $post ) {
								if( $post_type == 'product' ) {
									setup_postdata( $GLOBALS['post'] =& $post );
									$GLOBALS['product'] = $product;
									/**
									 * Hook: woocommerce_shop_loop.
									 */
									do_action( 'woocommerce_shop_loop' );
									wc_get_template_part( 'content', 'product' );
								} else {
									setup_postdata( $post );
									get_template_part( "templates/archives/post" );
								}
							}
							if( $post_type == 'product' ) {
								woocommerce_product_loop_end();
							} else {
								echo "</div>";
							}
							wp_reset_postdata();
						}

						get_template_part( "templates/archives/pagination" );
					} else {
						if( is_search() ) {
							$general_search_no_results = Options::get_options( [
								'general_search_no_results'	=> __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'bijan' ),
							] )['general_search_no_results'];
							echo '<div class="empty-page">';
							echo '<i class="empty-page-icon bijan-icon-search-cross"></i>';
							echo '<p class="empty-page-text no-results">' . $general_search_no_results . '</p>';
							echo '</div>';
						}
					}
					?>
				</div>
			</div>
		</main>
	</div>
	<?php
}
get_footer();