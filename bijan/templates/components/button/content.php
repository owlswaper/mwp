<?php
if( !defined( 'ABSPATH' ) ) exit;

$prefix = '';
if( isset( $args["prefix"] ) ) {
	$prefix = $args["prefix"];
}

if( $args["{$prefix}icon_align"] == 'left' && $args["{$prefix}icon"] ) {
	echo $args["{$prefix}icon"];
}
?>
<?php if( $args["{$prefix}text"] !== '' ) { ?>
	<span class="button-text"><?php echo $args["{$prefix}text"] ?></span>
<?php } ?>
<?php
if( $args["{$prefix}icon_align"] == 'right' && $args["{$prefix}icon"] ) {
	echo $args["{$prefix}icon"];
}
if( !empty( $args["{$prefix}loading"] ) ) {
	echo '<div class="button-loading">' . file_get_contents( BIJAN_DIR . "assets/img/loading.svg" ) . "</div>";
}