<?php

use Bijan\Utils;
use Bijan\Utils\WC;
use Bijan\Utils\WC\PriceHistory;
use MJ\Whitebox\Utils\Formatters as WhiteboxFormatters;

global $product;

$now = current_time( 'U' );
$one_week_time = date_i18n( 'Y-m-d', ( $now-WEEK_IN_SECONDS ) );
$one_month_time = date_i18n( 'Y-m-d', ( $now-MONTH_IN_SECONDS ) );
$three_month_time = date_i18n( 'Y-m-d', ( $now-MONTH_IN_SECONDS*3 ) );
$one_year_time = date_i18n( 'Y-m-d', ( $now-YEAR_IN_SECONDS ) );

$history_list = PriceHistory::list( $product->get_id() );
$history_list[$now] = $product->get_price();

// Convert times to day time(00:00:00)
$new_history_list = [];
WC::apply_custom_toman( false );
foreach( $history_list as $time => $value ) {
	// Get 00:00:00 of the day
	$day_time = date_i18n( 'Y-m-d', $time );
	$new_history_list[$day_time] = [
		'price'		=> floatval( $value ),
		'priceHtml'	=> WhiteboxFormatters::price( $value, true ),
		'change'	=> '',
	];
}
$history_list = $new_history_list;
WC::apply_custom_toman( true );

// Set price change
// چون توی حلقه قبلی تایم ها دارن تغییر میکنن، باید بعد از تغییر چک بشه اولی هست یا نه و اینکه نسبت به قبلی چه تغییری داشته.
$history_list_times = array_keys( $history_list );
foreach( $history_list as $time => $value ) {
	if( $time == $history_list_times[0] ) continue;

	$current_time_index = array_search( $time, $history_list_times );
	$prev_time = $history_list_times[$current_time_index-1];
	if( $value['price'] > $history_list[$prev_time]['price'] ) {
		$history_list[$time]['change'] = 'up';
	} else {
		$history_list[$time]['change'] = 'down';
	}
}
// print_r( $history_list ); die;

$all = [];
$one_week = [];
$one_month = [];
$three_month = [];
$one_year = [];

foreach( $history_list as $time => $value ) {
	$time_str = $time;
	if( is_rtl() ) {
		$time_str = Utils::convert_chars( $time_str, false, '', true );
	}

	$all[$time_str] = $value;

	if( $time >= $one_week_time ) {
		$one_week[$time_str] = $value;
	}

	if( $time >= $one_month_time ) {
		$one_month[$time_str] = $value;
	}

	if( $time >= $three_month_time ) {
		$three_month[$time_str] = $value;
	}

	if( $time >= $one_year_time ) {
		$one_year[$time_str] = $value;
	}
}

$price_history_result = [
	'all'			=> $all,
	'one_week'		=> $one_week,
	'one_month'		=> $one_month,
	'three_month'	=> $three_month,
	'one_year'		=> $one_year,
];
?>
<div id="price-history-overlay"></div>
<div id="price-history-popup">
	<script>var priceHistory = <?php echo wp_json_encode( $price_history_result ) ?>;</script>
	<div id="price-history-popup-head">
		<div id="price-history-popup-head-texts">
			<div id="price-history-popup-head-title">
				<i class="bijan-icon-diagram"></i>
				<div id="price-history-popup-head-title-text"><?php esc_html_e( 'Selling price chart', 'bijan' ) ?></div>
			</div>
			<div id="price-history-popup-head-product" class="line-clamp line-clamp-1"><?php echo esc_html( $product->get_name() ) ?></div>
		</div>

		<i class="bijan-icon-close-square price-history-popup-close"></i>
	</div>

	<div id="price-history-popup-body">
		<canvas id="price-history-popup-chart-all" class="price-history-popup-chart"></canvas>
		<canvas id="price-history-popup-chart-one_week" class="price-history-popup-chart"></canvas>
		<canvas id="price-history-popup-chart-one_month" class="price-history-popup-chart"></canvas>
		<canvas id="price-history-popup-chart-three_month" class="price-history-popup-chart"></canvas>
		<canvas id="price-history-popup-chart-one_year" class="price-history-popup-chart"></canvas>
		<div id="price-history-popup-tooltip">
			<div id="price-history-popup-tooltip-price-row" class="price-history-popup-tooltip-row">
				<i class="price-history-popup-tooltip-icon bijan-icon-dollar-square"></i>
				<div class="price-history-popup-tooltip-text"><?php echo esc_html_x( "Price:", 'chart', 'bijan' ) ?> <span id="price-history-popup-tooltip-price"></span></div>
			</div>

			<div id="price-history-popup-tooltip-price-change-row" class="price-history-popup-tooltip-row">
				<i id="price-history-popup-tooltip-price-change-up" class="price-history-popup-tooltip-icon bijan-icon-arrow-up-3"></i>
				<i id="price-history-popup-tooltip-price-change-down" class="price-history-popup-tooltip-icon bijan-icon-arrow-bottom"></i>
				<div id="price-history-popup-tooltip-price-change-text" class="price-history-popup-tooltip-text"></div>
			</div>

			<div id="price-history-popup-tooltip-date-row" class="price-history-popup-tooltip-row">
				<i class="price-history-popup-tooltip-icon bijan-icon-calendar"></i>
				<div class="price-history-popup-tooltip-text"><?php echo esc_html_x( "Price change:", 'chart', 'bijan' ) ?> <span id="price-history-popup-tooltip-date"></span></div>
			</div>
		</div>
	</div>

	<div id="price-history-popup-footer">
		<?php
		get_template_part( "templates/components/button", null, [
			'type'		=> 'gray',
			'text'		=> esc_html__( "All times", 'bijan' ),
			'id'		=> 'price-history-popup-show-all',
			'classes'	=> ['price-history-popup-show-chart-btn', 'active'],
			'atts'		=> [
				'type'		=> 'button',
				'data-time'	=> 'all'
			],
		] );
		get_template_part( "templates/components/button", null, [
			'type'		=> 'gray',
			'text'		=> esc_html__( "One week ago", 'bijan' ),
			'id'		=> 'price-history-popup-show-one_week',
			'classes'	=> ['price-history-popup-show-chart-btn'],
			'atts'		=> [
				'type'		=> 'button',
				'data-time'	=> 'one_week'
			],
		] );
		get_template_part( "templates/components/button", null, [
			'type'		=> 'gray',
			'text'		=> esc_html__( "One month ago", 'bijan' ),
			'id'		=> 'price-history-popup-show-one_month',
			'classes'	=> ['price-history-popup-show-chart-btn'],
			'atts'		=> [
				'type'		=> 'button',
				'data-time'	=> 'one_month'
			],
		] );
		get_template_part( "templates/components/button", null, [
			'type'		=> 'gray',
			'text'		=> esc_html__( "Three month ago", 'bijan' ),
			'id'		=> 'price-history-popup-show-three_month',
			'classes'	=> ['price-history-popup-show-chart-btn'],
			'atts'		=> [
				'type'		=> 'button',
				'data-time'	=> 'three_month'
			],
		] );
		get_template_part( "templates/components/button", null, [
			'type'		=> 'gray',
			'text'		=> esc_html__( "One year ago", 'bijan' ),
			'id'		=> 'price-history-popup-show-one_year',
			'classes'	=> ['price-history-popup-show-chart-btn'],
			'atts'		=> [
				'type'		=> 'button',
				'data-time'	=> 'one_year'
			],
		] );
		?>
	</div>
</div>