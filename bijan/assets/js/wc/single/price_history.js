(function($) {
	$(document).ready(function(){
		if( typeof priceHistory == 'undefined' ) {
			return;
		}
		
		// Close
		function closePopup() {
			$('#price-history-overlay, #price-history-popup').fadeOut();
		}
		$('.price-history-popup-close, #price-history-overlay').on('click', closePopup);

		// Show
		$(document).on('click', '.bijan-show-price-chart', function(e) {
			e.preventDefault();
			$('#price-history-overlay').fadeIn();
			$('#price-history-popup').fadeIn({
				start: function() {
					$(this).css('display', 'flex')
				}
			});
		});

		$(document).on('keydown', function(e) {
			if (e.key === "Escape" || e.keyCode === 27) {
				closePopup();
			}
		});

		// Charts
		let chartConfig = {
			type: 'line',
			data: {
				labels: {},
				datasets: [
					{
						label: '',
						fill: false,
						borderColor: '#252627',
						tension: 0.01,

						// Point style
						pointBackgroundColor: '#FEF5DB',
						pointBorderColor: '#252627',
						pointBorderWidth: 1,
						pointRadius: 4,
						pointHoverBackgroundColor: bijanPriceHistory.colors.primary,
						pointHoverBorderWidth: 2,
						pointHoverRadius: 8,
					}
				]
			},
			options: {
				scales: {
					x: {
						ticks: {
							font: {
								family: bijanPriceHistory.font,
								size: 14
							},
							color: '#878787'
						}
					},
					y: {
						ticks: {
							callback: function(value) {
								value = Number(value).toLocaleString();
								return bijan.persianNumbers(value);
							},
							font: {
								family: bijanPriceHistory.font,
								size: 14
							},
							color: '#878787'
						}
					}
				},
				plugins: {
					legend: {
						display: false,
					},
					title: {
						display: false,
					},
					tooltip: {
						enabled: false,
						external: function(context) {
							let tooltipEl = document.getElementById('price-history-popup-tooltip');

							const tooltipModel = context.tooltip;

							// Hide tooltip when not needed
							if(tooltipModel.opacity === 0) {
								tooltipEl.style.opacity = 0;
								return;
							}

							// Set content
							const dataPoint = tooltipModel.dataPoints[0];
							let itemData = priceHistory.all[dataPoint.label];
							document.getElementById('price-history-popup-tooltip-price').innerHTML = itemData.priceHtml;
							document.getElementById('price-history-popup-tooltip-date').innerHTML = dataPoint.label;
							if( !itemData.change ) {
								$('#price-history-popup-tooltip-price-change-row').hide();
							} else {
								$('#price-history-popup-tooltip-price-change-row').show();
								let up = $('#price-history-popup-tooltip-price-change-up'),
									down = $('#price-history-popup-tooltip-price-change-down'),
									text = $('#price-history-popup-tooltip-price-change-text');
								if( itemData.change == 'up' ) {
									up.show();
									down.hide();
									text.text(bijanPriceHistory.tooltip.up);
								} else {
									up.hide();
									down.show();
									text.text(bijanPriceHistory.tooltip.down);
								}
							}

							// Get chart position relative to container
							const chartRect = context.chart.canvas.getBoundingClientRect();
							const containerRect = document.getElementById('price-history-popup').getBoundingClientRect();

							// Calculate position relative to container
							let left = tooltipModel.caretX;
							let top = tooltipModel.caretY;

							 // Measure tooltip width
							tooltipEl.style.left = '0px'; // temporarily reset to measure
							tooltipEl.style.right = 'auto';
							tooltipEl.style.opacity = 1; // make sure it's visible for measurement
							const tooltipWidth = tooltipEl.offsetWidth;
							const containerWidth = containerRect.width;

							// If tooltip would overflow right side, align to the right
							if (left + tooltipWidth > containerWidth) {
								tooltipEl.style.left = 'auto';
								tooltipEl.style.right = (containerWidth - left) + 'px';
							} else {
								tooltipEl.style.left = left + 'px';
								tooltipEl.style.right = 'auto';
							}

							tooltipEl.style.top = top + 'px';
						}
					}
				}
			}
		};

		// All
		let allChartConfig = bijan.deepClone(chartConfig);
		allChartConfig.data.labels = Object.keys(priceHistory.all)
		allChartConfig.data.datasets[0].data = Object.values(priceHistory.all).map(item => item.price)
		new Chart( document.getElementById('price-history-popup-chart-all'), allChartConfig )

		// one_week
		let oneWeekChartConfig = bijan.deepClone(chartConfig);
		oneWeekChartConfig.data.labels = Object.keys(priceHistory.one_week)
		oneWeekChartConfig.data.datasets[0].data = Object.values(priceHistory.one_week).map(item => item.price)
		let oneWeekChart = document.getElementById('price-history-popup-chart-one_week')
		new Chart( oneWeekChart, oneWeekChartConfig )
		oneWeekChart.style.display = 'none';

		// one_month
		let oneMonthChartConfig = bijan.deepClone(chartConfig);
		oneMonthChartConfig.data.labels = Object.keys(priceHistory.one_month)
		oneMonthChartConfig.data.datasets[0].data = Object.values(priceHistory.one_month).map(item => item.price)
		let oneMonthChart = document.getElementById('price-history-popup-chart-one_month')
		new Chart( oneMonthChart, oneMonthChartConfig )
		oneMonthChart.style.display = 'none';

		// three_month
		let threeMonthChartConfig = bijan.deepClone(chartConfig);
		threeMonthChartConfig.data.labels = Object.keys(priceHistory.three_month)
		threeMonthChartConfig.data.datasets[0].data = Object.values(priceHistory.three_month).map(item => item.price)
		let threeMonthChart = document.getElementById('price-history-popup-chart-three_month')
		new Chart( threeMonthChart, threeMonthChartConfig )
		threeMonthChart.style.display = 'none';

		// one_year
		let oneYearChartConfig = bijan.deepClone(chartConfig);
		oneYearChartConfig.data.labels = Object.keys(priceHistory.one_year)
		oneYearChartConfig.data.datasets[0].data = Object.values(priceHistory.one_year).map(item => item.price)
		let oneYearChart = document.getElementById('price-history-popup-chart-one_year')
		new Chart( oneYearChart, oneYearChartConfig )
		oneYearChart.style.display = 'none';

		// Toggle charts
		$('.price-history-popup-show-chart-btn').on('click', function(e) {
			e.preventDefault();
			if(!$(this).hasClass('active')) {
				let time = $(this).attr('data-time');
				$(this).siblings('.active').removeClass('active');
				$(this).addClass('active');
				$('.price-history-popup-chart').hide();
				$(`#price-history-popup-chart-${time}`).show();
			}
		})
	});
})(jQuery);