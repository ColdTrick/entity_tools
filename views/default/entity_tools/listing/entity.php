<?php

$entity = elgg_extract('entity', $vars);
if (!($entity instanceof \ElggEntity)) {
	return;
}

$supported = entity_tools_get_supported_entity_types();
$class = $supported[$entity->getSubtype()];
$migrate = new $class($entity);

$row_data = [];

$row_data[] = elgg_view('output/url', [
	'text' => elgg_get_excerpt($entity->title, 30),
	'title' => $entity->title,
	'href' => $entity->getURL(),
]);

if ($migrate->canBackDate()) {
	$row_data[] = elgg_view('input/datetimepicker', [
		'name' => "params[{$entity->guid}][time_created]",
		'value' => $entity->time_created,
		'timestamp' => true,
		'readonly' => true,
	]);
}

if ($migrate->canChangeOwner()) {
	$row_data[] = elgg_view('input/userpicker', [
		'name' => "params[{$entity->guid}][owner_guid]",
		'values' => $entity->getOwnerGUID(),
		'limit' => 1,
	]);
}

if ($migrate->canChangeContainer()) {
	$view = 'input/entity_tools_container';
	if (elgg_view_exists("{$view}/{$entity->getSubtype()}")) {
		$view = "{$view}/{$entity->getSubtype()}";
	}
	
	$row_data[] = elgg_view($view, [
		'entity' => $entity,
		'name' => "params[{$entity->guid}][container_guid]",
		'value' => $entity->getContainerGUID(),
	]);
}

$row = implode('</td><td>', $row_data);

echo "<tr><td>$row</td></tr>";