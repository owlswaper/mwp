<?php

use Bijan\Utils\UI;

?>
<div id="compare-popup-overlay"></div>
<div id="compare-popup">
	<?php get_template_part( "templates/modals/compare/head" ) ?>

	<div id="compare-popup-body">
		<div id="compare-popup-loading">
			<?php UI::loading() ?>
			<div id="compare-popup-loading-text"><?php esc_html_e( 'Retrieving information. Please wait...', 'bijan' ) ?></div>
		</div>

		<div id="compare-popup-result">
			<?php get_template_part( "templates/modals/compare/add" ) ?>
		</div>
	</div>
</div>