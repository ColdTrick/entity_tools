<?php

use Elgg\Exceptions\DataFormatException;

elgg_require_css('jquery-datetimepicker/jquery.datetimepicker.min.css');

elgg_require_js('input/datetimepicker');

$vars['class'] = elgg_extract_class($vars, ['elgg-input-datetime']);

$defaults = [
	'value' => '',
	'disabled' => false,
];

$vars = array_merge($defaults, $vars);

$timestamp = elgg_extract('timestamp', $vars, false);
unset($vars['timestamp']);

$value = elgg_extract('value', $vars);

$value_date = '';
$value_timestamp = '';

if ($value) {
	try {
		$dt = \Elgg\Values::normalizeTime($value);

		$value_timestamp = $dt->getTimestamp();
		$value_date = date('Y-m-d H:i', $value_timestamp);
	} catch (DataFormatException $ex) {
		// ignore these exceptions
	}
}

if ($timestamp) {
	echo elgg_view('input/hidden', [
		'name' => elgg_extract('name', $vars),
		'value' => $value_timestamp,
	]);

	$vars['class'][] = 'elgg-input-timestamp';
	$vars['id'] = elgg_extract('name', $vars);
	unset($vars['name']);
	unset($vars['internalname']);
}

$vars['value'] = $value_date;

echo elgg_view('input/text', $vars);
