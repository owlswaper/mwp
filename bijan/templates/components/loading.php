<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'text'		=> '',
	'classes'	=> [],
] );

$class = array_merge( ['bijan-loading'], $args['classes'] );
?>
<div class="<?php echo Utils::prepare_html_classes( $class ) ?>">
	<?php echo file_get_contents( BIJAN_DIR . "assets/img/loading.svg" ) ?>
	<?php if( $args['text'] !== '' ) { ?>
		<span class="bijan-loading-text"><?php echo wp_kses_post( $args['text'] ) ?></span>
	<?php } ?>
</div>