define(['jquery', 'jqueryui-timepicker-addon/jquery-ui-timepicker-addon.min', 'jqueryui-timepicker-addon/jquery-ui-sliderAccess'], function ($) {
	
	if ($('.elgg-input-datetime').length) {
		$('.elgg-input-datetime').datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'H:mm',
			maxDate: 0,
			onSelect: function(dateText) {
				
				if ($(this).is('.elgg-input-timestamp')) {
					var dt = new Date(dateText);
					var timestamp = dt.getTime();
					
					timestamp = timestamp / 1000;
	
					var id = $(this).attr('id');
					$('input[name="' + id + '"]').val(timestamp);
				}
			}
		});
	}
});
