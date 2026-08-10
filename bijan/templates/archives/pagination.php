<?php if( !defined( 'ABSPATH' ) ) exit; ?>
<div class="pagination">
	<?php
	$is_rtl = is_rtl();

	$prev_text = $is_rtl ? "<i class='bijan-icon-right'></i>" : "<i class='bijan-icon-left'></i>";
	$prev_text .= esc_html__( 'Previous page', 'bijan' );
	$next_text = esc_html__( 'Next page', 'bijan' );
	$next_text .= $is_rtl ? "<i class='bijan-icon-left'></i>" : "<i class='bijan-icon-right'></i>";

	the_posts_pagination(
		[
			'prev_text'	=> $prev_text,
			'next_text'	=> $next_text,
			'mid_size'	=> 3,
			'class'		=> '',
			/* translators: Hidden accessibility text. */
			'before_page_number'	=> '<span class="meta-nav screen-reader-text">' . __( 'Page', 'bijan' ) . ' </span>',
		]
	);
	?>
</div>