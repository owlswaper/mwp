(function($) {
	$(document).ready(function(){
		// Date picker
		let pdConfigs = {
			inline: false,
			format: "YYYY-MM-DD HH:mm",
			viewMode: "day",
			initialValue: '',
			autoClose: true,
			position: "auto",
			onlySelectOnDate: true,
			calendarType: bijanPriceHistory.rtl ? 'persian' : 'gregorian',
			inputDelay: 1,
			observer: false,
			maxDate: new Date().getTime(),
			calendar: {
				persian: {
					locale: "fa",
					showHint: true,
					leapYearMode: "astronomical"
				},
				gregorian: {
					locale: "en",
					showHint: true
				}
			},
			navigator: {
				enabled: true,
				scroll: {
					enabled: true
				},
				text: {
					btnNextText: "<",
					btnPrevText: ">"
				}
			},
			toolbox: {
				enabled: true,
				calendarSwitch: {
					enabled: true,
					format: "MMMM"
				},
				todayButton: {
					enabled: true,
					text: {
						fa: bijanPriceHistory.i18n.today,
						en: "Today"
					}
				},
				submitButton: {
					enabled: true,
					text: {
						fa: bijanPriceHistory.i18n.submit,
						en: "Submit"
					}
				},
				text: {
					btnToday: bijanPriceHistory.i18n.today
				},
			},
			timePicker: {
				enabled: true,
				step: 1,
				hour: {
					enabled: true,
					step: null
				},
				minute: {
					enabled: true,
					step: null
				},
				second: {
					enabled: false,
					step: null
				},
				meridian: {
					enabled: false
				}
			},
			dayPicker: {
				enabled: true,
				titleFormat: "YYYY MMMM"
			},
			monthPicker: {
				enabled: true,
				titleFormat: "YYYY"
			},
			yearPicker: {
				enabled: true,
				titleFormat: "YYYY"
			},
			responsive: true,
			template: "<div id=\"plotId\" class=\"mj-datepicker-plot-area datepicker-plot-area {{cssClass}}\">\n    {{#navigator.enabled}}\n        <div data-navigator class=\"datepicker-navigator\">\n            <div class=\"pwt-btn pwt-btn-next\">{{navigator.text.btnNextText}}</div>\n            <div class=\"pwt-btn pwt-btn-switch\">{{navigator.switch.text}}</div>\n            <div class=\"pwt-btn pwt-btn-prev\">{{navigator.text.btnPrevText}}</div>\n        </div>\n    {{/navigator.enabled}}\n    <div class=\"datepicker-grid-view\" >\n    {{#days.enabled}}\n        {{#days.viewMode}}\n        <div class=\"datepicker-day-view\" >    \n            <div class=\"month-grid-box\">\n                <div class=\"header\">\n                    <div class=\"title\"></div>\n                    <div class=\"header-row\">\n                        {{#weekdays.list}}\n                            <div class=\"header-row-cell\">{{.}}</div>\n                        {{/weekdays.list}}\n                    </div>\n                </div>    \n                <table cellspacing=\"0\" class=\"table-days\">\n                    <tbody>\n                        {{#days.list}}\n                           \n                            <tr>\n                                {{#.}}\n                                    {{#enabled}}\n                                        <td data-date=\"{{dataDate}}\" data-unix=\"{{dataUnix}}\" >\n                                            <span  class=\"{{#otherMonth}}other-month{{/otherMonth}}\">{{title}}</span>\n                                            {{#altCalendarShowHint}}\n                                            <i  class=\"alter-calendar-day\">{{alterCalTitle}}</i>\n                                            {{/altCalendarShowHint}}\n                                        </td>\n                                    {{/enabled}}\n                                    {{^enabled}}\n                                        <td data-date=\"{{dataDate}}\" data-unix=\"{{dataUnix}}\" class=\"disabled\">\n                                            <span class=\"{{#otherMonth}}other-month{{/otherMonth}}\">{{title}}</span>\n                                            {{#altCalendarShowHint}}\n                                            <i  class=\"alter-calendar-day\">{{alterCalTitle}}</i>\n                                            {{/altCalendarShowHint}}\n                                        </td>\n                                    {{/enabled}}\n                                    \n                                {{/.}}\n                            </tr>\n                        {{/days.list}}\n                    </tbody>\n                </table>\n            </div>\n        </div>\n        {{/days.viewMode}}\n    {{/days.enabled}}\n    \n    {{#month.enabled}}\n        {{#month.viewMode}}\n            <div class=\"datepicker-month-view\">\n                {{#month.list}}\n                    {{#enabled}}               \n                        <div data-month=\"{{dataMonth}}\" class=\"month-item {{#selected}}selected{{/selected}}\">{{title}}</small></div>\n                    {{/enabled}}\n                    {{^enabled}}               \n                        <div data-month=\"{{dataMonth}}\" class=\"month-item month-item-disable {{#selected}}selected{{/selected}}\">{{title}}</small></div>\n                    {{/enabled}}\n                {{/month.list}}\n            </div>\n        {{/month.viewMode}}\n    {{/month.enabled}}\n    \n    {{#year.enabled }}\n        {{#year.viewMode }}\n            <div class=\"datepicker-year-view\" >\n                {{#year.list}}\n                    {{#enabled}}\n                        <div data-year=\"{{dataYear}}\" class=\"year-item {{#selected}}selected{{/selected}}\">{{title}}</div>\n                    {{/enabled}}\n                    {{^enabled}}\n                        <div data-year=\"{{dataYear}}\" class=\"year-item year-item-disable {{#selected}}selected{{/selected}}\">{{title}}</div>\n                    {{/enabled}}                    \n                {{/year.list}}\n            </div>\n        {{/year.viewMode }}\n    {{/year.enabled }}\n    \n    </div>\n    {{#time}}\n    {{#enabled}}\n    <div class=\"datepicker-time-view\">\n        {{#hour.enabled}}\n            <div class=\"hour time-segment\" data-time-key=\"hour\">\n                <div class=\"up-btn\" data-time-key=\"hour\">▲</div>\n                <input value=\"{{hour.title}}\" type=\"text\" placeholder=\"hour\" class=\"hour-input\">\n                <div class=\"down-btn\" data-time-key=\"hour\">▼</div>                    \n            </div>       \n            <div class=\"divider\">\n                <span>:</span>\n            </div>\n        {{/hour.enabled}}\n        {{#minute.enabled}}\n            <div class=\"minute time-segment\" data-time-key=\"minute\" >\n                <div class=\"up-btn\" data-time-key=\"minute\">▲</div>\n                <input disabled value=\"{{minute.title}}\" type=\"text\" placeholder=\"minute\" class=\"minute-input\">\n                <div class=\"down-btn\" data-time-key=\"minute\">▼</div>\n            </div>        \n            <div class=\"divider second-divider\">\n                <span>:</span>\n            </div>\n        {{/minute.enabled}}\n        {{#second.enabled}}\n            <div class=\"second time-segment\" data-time-key=\"second\"  >\n                <div class=\"up-btn\" data-time-key=\"second\" >▲</div>\n                <input disabled value=\"{{second.title}}\"  type=\"text\" placeholder=\"second\" class=\"second-input\">\n                <div class=\"down-btn\" data-time-key=\"second\" >▼</div>\n            </div>\n            <div class=\"divider meridian-divider\"></div>\n            <div class=\"divider meridian-divider\"></div>\n        {{/second.enabled}}\n        {{#meridian.enabled}}\n            <div class=\"meridian time-segment\" data-time-key=\"meridian\" >\n                <div class=\"up-btn\" data-time-key=\"meridian\">▲</div>\n                <input disabled value=\"{{meridian.title}}\" type=\"text\" class=\"meridian-input\">\n                <div class=\"down-btn\" data-time-key=\"meridian\">▼</div>\n            </div>\n        {{/meridian.enabled}}\n    </div>\n    {{/enabled}}\n    {{/time}}\n    \n    {{#toolbox}}\n    {{#enabled}}\n    <div class=\"toolbox\">\n        {{#toolbox.submitButton.enabled}}\n            <div class=\"pwt-btn-submit\">{{submitButtonText}}</div>\n        {{/toolbox.submitButton.enabled}}        \n        {{#toolbox.todayButton.enabled}}\n            <div class=\"pwt-btn-today\">{{todayButtonText}}</div>\n        {{/toolbox.todayButton.enabled}}        \n        {{#toolbox.calendarSwitch.enabled}}\n            <div class=\"pwt-btn-calendar\">{{calendarSwitchText}}</div>\n        {{/toolbox.calendarSwitch.enabled}}\n    </div>\n    {{/enabled}}\n    {{^enabled}}\n        {{#onlyTimePicker}}\n        <div class=\"toolbox\">\n            <div class=\"pwt-btn-exit\">{{text.btnExit}}</div>\n        </div>\n        {{/onlyTimePicker}}\n    {{/enabled}}\n    {{/toolbox}}\n</div>\n",
		};
		function initDatePicker() {
			if($('.bijan-datepicker-input').length) {
				$('.bijan-datepicker-input:not(.pwt-datepicker-input-element)').each(function() {
					let pd = $(this).mjpersianDatepicker(pdConfigs),
						time = $(this).attr('data-time');
					if(parseInt(time) > 0) {
						time *= 1000;
						let localOffset = new Date(time).getTimezoneOffset() * 60000;
						time = new Date(time + localOffset);
						pd.setDate(time);
					} else {
						$(this).val('');
					}
				})
			}
		}
		initDatePicker();

		// Remove
		$(document).on('click', '.bijan_price_history_item-remove', function(e) {
			e.preventDefault();
			$(this).closest('.bijan_price_history_item:not(:last-child)').remove();
			updateItemsIndexes();
		})
		$(document).on('click', '.bijan_price_history_item-add', function(e) {
			e.preventDefault();
			let thisItem = $(this).closest('.bijan_price_history_item');
			let template = wp.template('bijan-price_history-item');
			let html = template({
				index: Number(thisItem.attr('data-index'))+1
			});
			$(html).insertAfter(thisItem);
			updateItemsIndexes();
			initDatePicker();
		})

		function updateItemsIndexes() {
			let index = 0;
			$('.bijan_price_history_item').each(function() {
				$(this).attr('data-index', index);
				$(this).find('.bijan_price_history_time').attr('name', `bijan_price_history_item[${index}][time]`);
				$(this).find('.bijan_price_history_value').attr('name', `bijan_price_history_item[${index}][value]`);
				index++;
			})
		}

		$('#product-type').on('change', function() {
			let wrap = $('#bijan_price_history_wrap'),
				inputs = wrap.find('input');
			if($(this).val() == 'simple') {
				wrap.show();
				inputs.prop('disabled', false);
			} else {
				wrap.hide();
				inputs.prop('disabled', true);
			}
		})
	});
})(jQuery);