<?php

elgg_load_js('jquery.timepicker');
elgg_load_js('jquery.slider');
elgg_load_css('jquery.timepicker');
elgg_load_css('jquery.slider');

elgg_require_js('input/datetimepicker');

$vars['class'] = elgg_extract_class($vars, ['elgg-input-datetime']);

$defaults = [
	'value' => '',
	'disabled' => false,
];

$vars = array_merge($defaults, $vars);

$timestamp = elgg_extract('timestamp', $vars, false);
unset($vars['timestamp']);

if ($timestamp) {
	echo elgg_view_('input/hidden', [
		'name' => elgg_extract('name', $vars),
		'value' => elgg_extract('value', $vars),
	]);

	$vars['class'][] = 'elgg-input-timestamp';
	$vars['id'] = elgg_extract('name', $vars);
	unset($vars['name']);
	unset($vars['internalname']);
}

// convert timestamps to text for display
if (is_numeric($vars['value'])) {
	$vars['value'] = gmdate('Y-m-d H:i', elgg_extract('value', $vars));
}

echo elgg_view('input/text', $vars);
