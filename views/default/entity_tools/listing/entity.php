<?php

$entity = elgg_extract('entity', $vars);
if (!($entity instanceof \ElggEntity)) {
	return;
}

$row_data = [];

$row_data[] = elgg_view('output/url', [
	'text' => elgg_get_excerpt($entity->title, 30),
	'title' => $entity->title,
	'href' => $entity->getURL(),
]);

$row_data[] = elgg_view('input/datetimepicker', [
	'name' => "params[{$entity->getGUID()}][time_created]",
	'value' => $entity->time_created,
	'timestamp' => true,
	'readonly' => true,
]);

$row_data[] = elgg_view('input/userpicker', [
	'name' => "params[{$entity->getGUID()}][owner_guid]",
	'values' => $entity->getOwnerGUID(),
	'limit' => 1,
]);

$row_data[] = elgg_view('input/dropdown_label', [
	'name' => "params[{$entity->getGUID()}][container_guid]",
	'value' => $entity->getContainerGUID(),
	'options_values' => entity_tools_get_container_options($entity),
]);

$row = implode('</td><td>', $row_data);

echo "<tr><td>$row</td></tr>";