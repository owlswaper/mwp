(function($) {
	$(document).ready(function(){
		$('#bijan_notification_all_users').on('change', function() {
			let row = $('#bijan_notification_select_user')
			if($(this).prop('checked')) {
				row.hide();
			} else {
				row.show();
			}
		})
	});
})(jQuery);