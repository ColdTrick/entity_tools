define(['jquery'], function ($) {
	if ($('.elgg-input-datetime').length) {
		$('.elgg-input-datetime').datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm',
			ampm: false,
			maxDate: 0,
			hideIfNoPrevNext: true,
			onSelect: function(dateText) {
				if ($(this).is('.elgg-input-timestamp')) {
					// convert to unix timestamp
					var textParts = dateText.split(" ");
					var dateParts = textParts[0].split("-");
					var timeParts = textParts[1].split(":");
	
					var timestamp = Date.UTC(dateParts[0], dateParts[1] - 1, dateParts[2], timeParts[0], timeParts[1]);
					
					timestamp = timestamp / 1000;
	
					var id = $(this).attr('id');
					$('input[name="' + id + '"]').val(timestamp);
				}
			}
		});
	}
});
