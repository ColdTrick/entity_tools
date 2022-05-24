define(['jquery', 'jquery-datetimepicker/jquery.datetimepicker.full.min'], function ($) {
	
	if ($('.elgg-input-datetime').length) {
		$('.elgg-input-datetime').datetimepicker({
			format: 'Y-m-d H:i',
			step: 1,
			maxDate: 0,
			onChangeDateTime: function (current_time, $input) {
				if ($input.hasClass('elgg-input-timestamp')) {
					var timestamp = current_time.getTime();
					timestamp = timestamp / 1000;
					
					var id = $input.attr('id');
					$('input[name="' + id + '"]').val(timestamp);
				}
			}
		});
	}
});
