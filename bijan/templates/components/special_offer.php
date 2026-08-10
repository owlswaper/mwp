<?php

use Bijan\Utils;
use Bijan\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'title'				=> '',
	'end_time'			=> '',
	'show_percentage'	=> true,
	'show_button'		=> true,
] );
if( $args['show_button'] ) {
	$args = Elementor::check_button_defaults( $args );
}
$end_timestamp = 0;
if( $args['end_time'] ) {
	$end_timestamp = strtotime( $args['end_time'] );
	$now = (new \DateTime())->setTimestamp( Utils::convert_chars( date_i18n( 'U' ) ) );
	$end = (new \DateTime())->setTimestamp( $end_timestamp );
	$diff = $now->diff( $end );
	if( !$diff->invert ) {
		$days		= Utils::add_zero( $diff->format( '%a' ) );
		$hours		= Utils::add_zero( $diff->format( '%h' ) );
		$minutes	= Utils::add_zero( $diff->format( '%i' ) );
		$seconds	= Utils::add_zero( $diff->format( '%s' ) );
	} else {
		$days		= "00";
		$hours		= "00";
		$minutes	= "00";
		$seconds	= "00";
	}
}

$classes = ["special-offer"];
if( Utils::to_bool( $args['show_percentage'] ) ) {
	$classes[] = "special-offer-with-percentage";
}
if( Utils::to_bool( $args['show_button'] ) ) {
	$classes[] = "special-offer-with-button";
}
?>
<div class="<?php echo Utils::prepare_html_classes( $classes ) ?>" data-end="<?php echo $end_timestamp*1000 ?>">
	<div class="special-offer-title line-clamp line-clamp-1"><?php echo wp_kses_post( $args['title'] ) ?></div>
	<?php if( $end_timestamp ) { ?>
		<div class="special-offer-timer">
			<?php if( $days !== '00' ) { ?>
				<div class="special-offer-timer-wrap special-offer-timer-days">
					<div class="special-offer-timer-number"><?php echo substr( $days, 0, 1 ) ?></div>
					<div class="special-offer-timer-number"><?php echo substr( $days, 1, 1 ) ?></div>
				</div>
				<div class="special-offer-timer-separator"><?php echo file_get_contents( BIJAN_DIR . "assets/img/timer-separator.svg" ) ?></div>
			<?php } ?>

			<div class="special-offer-timer-wrap special-offer-timer-hours">
				<div class="special-offer-timer-number"><?php echo substr( $hours, 0, 1 ) ?></div>
				<div class="special-offer-timer-number"><?php echo substr( $hours, 1, 1 ) ?></div>
			</div>

			<div class="special-offer-timer-separator"><?php echo file_get_contents( BIJAN_DIR . "assets/img/timer-separator.svg" ) ?></div>

			<div class="special-offer-timer-wrap special-offer-timer-minutes">
				<div class="special-offer-timer-number"><?php echo substr( $minutes, 0, 1 ) ?></div>
				<div class="special-offer-timer-number"><?php echo substr( $minutes, 1, 1 ) ?></div>
			</div>

			<div class="special-offer-timer-separator"><?php echo file_get_contents( BIJAN_DIR . "assets/img/timer-separator.svg" ) ?></div>

			<div class="special-offer-timer-wrap special-offer-timer-seconds">
				<div class="special-offer-timer-number"><?php echo substr( $seconds, 0, 1 ) ?></div>
				<div class="special-offer-timer-number"><?php echo substr( $seconds, 1, 1 ) ?></div>
			</div>
		</div>
	<?php } ?>

	<?php if( Utils::to_bool( $args['show_percentage'] ) ) { ?>
		<div class="special-offer-percentage"><?php echo file_get_contents( BIJAN_DIR . "assets/img/percentage.svg" ) ?></div>
	<?php } ?>

	<?php
	if( Utils::to_bool( $args['show_button'] ) ) {
		$args['prefix'] = 'button_';
		$args['button_align'] = 'center';
		get_template_part( "templates/components/button", null, $args );
	}
	?>
</div>