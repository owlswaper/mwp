(function($) {
	$(document).ready(function(){
		$('.notification-head').on('click', function() {
			let notification = $(this).parent();
			if(notification.hasClass('notification-unread')) {
				$.ajax({
					url: bijanVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'bijan_set_notification_read',
						id: notification.attr('data-id'),
						nonce: bijanNotifs.nonces[notification.attr('data-id')]
					},
					success: function(res) {
						if(res.success) {
							notification.find('.notification-status').text(res.data.readText);
							notification.removeClass('notification-unread').addClass('notification-read');
							$('#myaccount-head-notifs .notifications-count').text(res.data.unreadCount);
						}
					}
				});
			}
			notification.toggleClass('opened');
			notification.find('.notification-text').slideToggle();
			notification.find('.notification-view').text(notification.hasClass('opened') ? bijanNotifs.i18n.close : bijanNotifs.i18n.open);
		})
	});
})(jQuery);
