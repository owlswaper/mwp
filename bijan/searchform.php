<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<form role="search" method="get" class="searchform" action="<?php echo home_url() ?>">
	<label class="screen-reader-text"><?php esc_html_e( 'Search for:', 'bijan' ) ?></label>
	<div class="input-wrap">
		<button type="submit" class="button-transparent circle" title="<?php echo esc_attr_e( "Search", 'bijan' ) ?>"><i class="bijan-icon-search-normal"></i></button>
		<input type="search" name="s" class="search-field" placeholder="<?php echo esc_attr_x( 'Search…', 'placeholder', 'bijan' ) ?>" value="<?php echo get_search_query() ?>" title="<?php echo esc_attr_x( 'Search for:', 'label', 'bijan' ) ?>" />
	</div>
</form>