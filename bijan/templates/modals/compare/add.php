<div id="compare-popup-add-wrap">
	<?php
	get_template_part( "templates/components/ajax-search", null, [
		'form_classes'	=> ['compare-popup-search'],
		'form_id'		=> 'compare-popup-search',
		'input_id'		=> 'compare-popup-search-field',
		'button_id'		=> 'compare-popup-search-submit',
		'popover_id'	=> 'compare-popup-search-popup',
		'post_type'		=> 'product',
		'query_args'	=> [
			'exclude'	=> !empty( $_COOKIE['bijan_compare_products'] ) ? explode( ",", $_COOKIE['bijan_compare_products'] ) : [],
		],
	] );
	?>
</div>