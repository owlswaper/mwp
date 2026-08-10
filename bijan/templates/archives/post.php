<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$classes = [get_post_type()];

if( !empty( $args['slider-mode'] ) ) {
	$classes[] = 'post-grid-item';
}
?>
<article <?php post_class( Utils::prepare_html_classes( $classes ) ); ?>>
	<a href="<?php echo get_permalink() ?>" title="<?php echo get_the_title() ?>">
		<?php bijan_post_thumbnail( null, null, false ) ?>

		<div class="post-texts">
			<div class="post-top-texts">
				<h2 class="post-title line-clamp line-clamp-1"><?php echo bijan_get_post_title() ?></h2>
				<time datetime="<?php echo get_the_date( 'Y-m-d H:i:s' ) ?>" class="post-time"><?php printf( esc_html__( '%s ago', 'bijan' ), human_time_diff( get_the_date( "U" ), Utils::convert_chars( date_i18n( "U" ) ) ) ) ?></time>
			</div>

			<div class="post-excerpt">
				<?php echo get_the_excerpt() ?>
			</div>
		</div>
	</a>
</article>