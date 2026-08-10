<?php
if( !defined( 'ABSPATH' ) ) exit;

$post_types = $args['post_types'];
$posts = $args['posts'];
?>
<?php if( count( $post_types ) > 1 ) { ?>
	<div class="bijan-search-post-types">
		<div class="bijan-search-post-type bijan-search-post-type-all bijan-search-post-type-active" data-post_type="all"><?php esc_html_e( 'All', 'bijan' ) ?></div>
		<?php foreach( $post_types as $post_type => $post_type_label ) { ?>
			<div class="bijan-search-post-type bijan-search-post-type-<?php echo esc_attr( $post_type ) ?>" data-post_type="<?php echo esc_attr( $post_type ) ?>"><?php echo esc_html( $post_type_label ) ?></div>
		<?php } ?>
	</div>
<?php } ?>

<div class="bijan-search-posts">
	<?php foreach( $posts as $post ) { ?>
		<a class="bijan-search-post" href="<?php echo esc_url( $post['permalink'] ) ?>" data-post_type="<?php echo $post['post_type'] ?>" data-id="<?php echo esc_attr( $post['id'] ) ?>">
			<?php echo $post['image'] ?>
			<div class="bijan-search-post-texts">
				<span class="bijan-search-post-title line-clamp line-clamp-1"><?php echo esc_html( $post['title'] ) ?></span>
				<div class="bijan-search-post-extras">
					<?php
					if( $post['post_type'] == 'product' ) {
						echo $post['extra']['price'];
					} else {
						echo '<time class="bijan-search-post-time">' . $post['extra']['date'] . '</time>';
					}
					?>
				</div>
			</div>
		</a>
	<?php } ?>

	<a href="<?php echo home_url( "?s={$args['text']}" ) ?>" class="bijan-search-show-all">
		<span class="bijan-search-show-all-label"><?php esc_html_e( 'Show all', 'bijan' ) ?></span>
		<?php if( is_rtl() ) { ?>
			<i class="bijan-icon-arrow-left"></i>
		<?php } else { ?>
			<i class="bijan-icon-arrow-right"></i>
		<?php } ?>
	</a>
</div>