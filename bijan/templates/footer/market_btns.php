<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<?php if( !empty( $args['footer_before_market_btns']['before_market_btns'] ) ) { ?>
	<div id="footer-before-market-items" class="footer-custom-section">
		<?php
		foreach( $args['footer_before_market_btns']['before_market_btns'] as $index => $item ) {
			echo '<div class="footer-custom-item">' . $item . '</div>';
		}
		?>
	</div>
<?php } ?>

<?php if( !empty( $args['footer_market_btns']['market_logos'] ) ) { ?>
	<div id="footer-market-items" class="footer-custom-section">
		<?php
		foreach( $args['footer_market_btns']['market_logos'] as $index => $logo ) {
			get_template_part( "templates/components/market_button", null, [
				'market'	=> $logo,
				'top_text'	=> $args['footer_market_btns']['market_btn_top_text'][$index],
				'text'		=> $args['footer_market_btns']['market_btn_text'][$index],
				'link'		=> $args['footer_market_btns']['market_btn_link'][$index],
				'size'		=> 'auto',
			] );
		}
		?>
	</div>
<?php } ?>

<?php if( !empty( $args['footer_after_market_btns']['after_market_btns'] ) ) { ?>
	<div id="footer-after-market-items" class="footer-custom-section">
		<?php
		foreach( $args['footer_after_market_btns']['after_market_btns'] as $index => $item ) {
			echo '<div class="footer-custom-item">' . $item . '</div>';
		}
		?>
	</div>
<?php } ?>