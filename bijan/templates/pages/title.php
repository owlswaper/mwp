<?php

use Bijan\Utils;
use Bijan\Utils\Archive;
use Bijan\Utils\Options;
use MJ\Whitebox\Utils as WhiteboxUtils;

if( !defined( 'ABSPATH' ) ) exit;
$options = $args['options'];

$settings = Options::get_options( [
	'page-title-tag'	=> 'h1',
	'archive-title-tag'	=> 'h1',
] );

$title_tag = !empty( $args['is_archive'] ) ? $settings['archive-title-tag'] : $settings['page-title-tag'];
?>
<header id="page-header">
	<div id="page-title-wrap" class="section-title">
		<?php
		if( !empty( $options['page_icon'] ) ) {
			echo WhiteboxUtils::get_icon( $options['page_icon'], 'section-title-icon page-title-icon' );
		}
		?>
		<<?php echo tag_escape( $title_tag ) ?> id="page-title" class="section-title-title"><?php bijan_archive_title() ?></<?php echo tag_escape( $title_tag ) ?>>
		<div class="section-title-divider"></div>

	</div>
	<?php
	if( !empty( $args['show_sort_archive'] ) ) {
		$settings = Options::get_options( [
			'default_archive_sort'	=> 'newest'
		] );
		$sorts = Archive::sorts();

		$selected_archive_sort = $settings['default_archive_sort'];
		if( !empty( $_GET['sort'] ) && in_array( $_GET['sort'], array_keys( $sorts ) ) ) {
			$selected_archive_sort = Utils::convert_chars( $_GET['sort'] );
		}
		?>
		<div id="sort-wrap" class="no-scrollbar">
			<div id="sort-label"><?php esc_html_e( 'Sort', 'bijan' ) ?><span id="sort-separator">:</span></div>
			<?php foreach( $sorts as $sort_name => $label ) { ?>
				<a
					href="<?php echo esc_url( add_query_arg( "sort", $sort_name ) ) ?>"
					class="sort-item<?php echo $sort_name == $selected_archive_sort ? ' sort-item-active' : '' ?>"
					data-sort="<?php echo esc_attr( $sort_name ) ?>"
				>
					<?php echo $label ?>
				</a>
			<?php } ?>
		</div>
	<?php } ?>
</header>