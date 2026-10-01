(function ($) {
    'use strict';
    $(function () {
        var root = $('.clz-tracker');
        if (!root.length || !window.clzTracking || !clzTracking.loggedIn) return;
        var results = root.find('#clz-track-results'), message = root.find('.clz-track-message');
        var page = 1, pending = false;
        function digits(value) {
            return String(value).replace(/[۰-۹٠-٩]/g, function (digit) {
                var persian = '۰۱۲۳۴۵۶۷۸۹', arabic = '٠١٢٣٤٥٦٧٨٩';
                var index = persian.indexOf(digit);
                return String(index < 0 ? arabic.indexOf(digit) : index);
            });
        }
        function request(action, data, done) {
            if (pending) return;
            pending = true;
            message.text('');
            root.find('#clz-track-lookup-form button span').text('در حال بررسی…');
            results.attr('aria-busy', 'true');
            root.find('button').prop('disabled', true);
            $.ajax({url: clzTracking.ajaxUrl, method: 'POST', dataType: 'json', timeout: 20000,
                data: $.extend({action: action, nonce: clzTracking.nonce}, data)
            }).done(function (response) {
                if (response && response.success) done(response.data);
                else message.text(response && response.data && response.data.message || 'دریافت اطلاعات انجام نشد. لطفاً دوباره تلاش کنید.');
            }).fail(function (xhr) {
                var response = xhr.responseJSON;
                message.text(response && response.data && response.data.message || 'ارتباط برقرار نشد. لطفاً دوباره تلاش کنید.');
            }).always(function () {
                pending = false;
                results.find('[data-track-loading]').remove();
                results.attr('aria-busy', 'false');
                root.find('button').prop('disabled', false);
                root.find('#clz-track-lookup-form button span').text('مشاهده وضعیت سفارش');
            });
        }
        function showDetail(data) {
            results.html(data.html);
            root.find('.clz-track-list-tools h2').text(data.view === 'list' ? 'سفارش‌های پیدا شده در این حساب' : 'نتیجه پیگیری سفارش');
            var detail = results.find('.clz-order-detail');
            if (detail.length) detail[0].focus();
        }
        function loadList(nextPage) {
            request('clz_tracking_list', {page: nextPage}, function (data) {
                page = data.page;
                root.find('.clz-track-list-tools h2').text('سفارش‌های حساب شما');
                results.html(data.html);
                if (data.pages > 1) {
                    var nav = $('<nav class="clz-track-pagination" aria-label="صفحه‌های سفارش‌ها"></nav>');
                    if (page > 1) nav.append($('<button type="button" data-track-page></button>').attr('data-track-page', page - 1).text('صفحه قبل'));
                    nav.append($('<span></span>').text('صفحه ' + page + ' از ' + data.pages));
                    if (page < data.pages) nav.append($('<button type="button" data-track-page></button>').attr('data-track-page', page + 1).text('صفحه بعد'));
                    results.append(nav);
                }
            });
        }
        root.on('submit', '#clz-track-lookup-form', function (event) {
            event.preventDefault();
            var number = digits(root.find('#clz-track-number').val()).trim();
            var phone = digits(root.find('#clz-track-phone').val()).replace(/[\s\-()]/g, '');
            if (!number && !phone) {
                message.text('شماره سفارش یا شماره همراه را وارد کنید.');
                root.find('#clz-track-number').trigger('focus'); return;
            }
            if (number && !/^[1-9][0-9]{0,11}$/.test(number)) {
                message.text('شماره سفارش را با اعداد روی رسید خرید وارد کنید.');
                root.find('#clz-track-number').trigger('focus'); return;
            }
            if (phone && !/^(?:0|\+98|0098|98)?9[0-9]{9}$/.test(phone)) {
                message.text('شماره همراه ثبت‌شده هنگام خرید را کامل وارد کنید.');
                root.find('#clz-track-phone').trigger('focus'); return;
            }
            request('clz_tracking_lookup', {order_number: number, phone: phone}, showDetail);
        });
        root.on('click', '[data-track-order]', function () {
            request('clz_tracking_detail', {order_id: $(this).attr('data-track-order'), access: $(this).attr('data-track-access')}, showDetail);
        });
        root.on('click', '[data-track-reset], [data-track-back]', function () { loadList(page); });
        root.on('click', '[data-track-page]', function () { loadList(Number($(this).attr('data-track-page'))); });
        loadList(1);
    });
})(jQuery);
