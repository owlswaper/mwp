<?php
if( !defined( 'ABSPATH' ) ) exit;

$query = $args['query'];
$query_arg_name = !empty( $args['query_arg_name'] ) ? sanitize_text_field( $args['query_arg_name'] ) : '';

if( $query_arg_name ) {
	$link = esc_url( add_query_arg( $query_arg_name, '%#%' ) );
} else {
	$link = str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) );
}
?>
<div class="pagination">
	<?php
	$is_rtl = is_rtl();

	$prev_text = $is_rtl ? "<i class='bijan-icon-right'></i>" : "<i class='bijan-icon-left'></i>";
	$prev_text .= esc_html__( 'Previous page', 'bijan' );
	$next_text = esc_html__( 'Next page', 'bijan' );
	$next_text .= $is_rtl ? "<i class='bijan-icon-left'></i>" : "<i class='bijan-icon-right'></i>";

	echo paginate_links( array(
		'base'					=> $link,
		'total'					=> $query->max_num_pages,
		'current'				=> max( 1, $args['paged'] ),
		'format'				=> '?paged=%#%',
		'prev_next'				=> true,
		'type'					=> 'plain',
		'prev_text'				=> $prev_text,
		'next_text'				=> $next_text,
		'mid_size'				=> 3,
		/* translators: Hidden accessibility text. */
		'before_page_number'	=> '<span class="meta-nav screen-reader-text">' . __( 'Page', 'bijan' ) . ' </span>',
	) );
	?>
</div>