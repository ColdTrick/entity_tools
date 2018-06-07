<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

$supported = entity_tools_get_supported_entity_types();
$class = $supported[$entity->getSubtype()];
$migrate = new $class($entity);

$row_data = [];

$title = $entity->getDisplayName();
if (empty($title)) {
	$title = elgg_get_excerpt($entity->description, 100);
}

$row_data[] = elgg_format_element('td', [], elgg_view('output/url', [
	'text' => elgg_get_excerpt($title, 30),
	'title' => $title,
	'href' => $entity->getURL(),
]));

if ($migrate->canBackDate()) {
	$row_data[] = elgg_format_element('td', [], elgg_view_field([
		'#type' => 'datetimepicker',
		'name' => "params[{$entity->guid}][time_created]",
		'value' => $entity->time_created,
		'timestamp' => true,
		'readonly' => true,
	]));
}

if ($migrate->canChangeOwner()) {
	$row_data[] = elgg_format_element('td', [], elgg_view_field([
		'#type' => 'userpicker',
		'name' => "params[{$entity->guid}][owner_guid]",
		'values' => [
			$entity->owner_guid,
		],
		'limit' => 1,
		'show_friends' => false,
	]));
}

if ($migrate->canChangeContainer()) {
	$view = 'input/entity_tools_container';
	$type = 'entity_tools_container';
	if (elgg_view_exists("{$view}/{$entity->getSubtype()}")) {
		$type .= "/{$entity->getSubtype()}";
	}
	
	$row_data[] = elgg_format_element('td', [], elgg_view_field([
		'#type' => $type,
		'entity' => $entity,
		'name' => "params[{$entity->guid}][container_guid]",
		'value' => $entity->container_guid,
	]));
}

echo elgg_format_element('tr', [], implode(PHP_EOL, $row_data));
