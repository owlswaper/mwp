(function ($) {
    'use strict';
    $(function () {
        var root = $('.clz-tracker');
        if (!root.length || !window.clzTracking || !clzTracking.loggedIn) return;
        var results = root.find('#clz-track-results'), message = root.find('.clz-track-message');
        var page = 1, pending = false;
        function request(action, data, done) {
            if (pending) return;
            pending = true;
            message.text('');
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
            });
        }
        function showDetail(data) {
            results.html(data.html);
            var detail = results.find('.clz-order-detail');
            if (detail.length) detail[0].focus();
        }
        function loadList(nextPage) {
            request('clz_tracking_list', {page: nextPage}, function (data) {
                page = data.page;
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
        root.on('submit', '#clz-track-code-form', function (event) {
            event.preventDefault();
            var code = $('#clz-track-code').val().trim();
            if (!code) { message.text('لطفاً کد اختصاصی سفارش را وارد کنید.'); return; }
            request('clz_tracking_lookup', {tracking_code: code}, showDetail);
        });
        root.on('click', '[data-track-order]', function () {
            request('clz_tracking_detail', {order_id: $(this).attr('data-track-order'), access: $(this).attr('data-track-access')}, showDetail);
        });
        root.on('click', '[data-track-reset], [data-track-back]', function () { loadList(page); });
        root.on('click', '[data-track-page]', function () { loadList(Number($(this).attr('data-track-page'))); });
        loadList(1);
    });
})(jQuery);
