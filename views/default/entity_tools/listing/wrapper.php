<?php

$entities = elgg_extract('entities', $vars);
if (empty($entities)) {
	return;
}

$rows = '<tr>';
$rows .= '<th>' . elgg_echo('title') . '</th>';
$rows .= '<th>' . elgg_echo('entity_tools:created') . '</th>';
$rows .= '<th>' . elgg_echo('entity_tools:owner') . '</th>';
$rows .= '<th>' . elgg_echo('entity_tools:container') . '</th>';
$rows .= '</tr>';

foreach ($entities as $entity) {
	$rows .= elgg_view('entity_tools/listing/entity', ['entity' => $entity]);
}

echo elgg_format_element('table', [
	'id' => 'entity-tools-listing-table',
	'class' => 'elgg-table mbm',
], $rows);
