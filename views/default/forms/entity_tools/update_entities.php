<?php

$owner_guid = (int) elgg_extract('owner_guid', $vars);
$container_guid = (int) elgg_extract('container_guid', $vars);
$subtype = elgg_extract('subtype', $vars);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'subtype',
	'value' => $subtype,
]);

// show some description
echo elgg_view('output/longtext', [
	'value' => elgg_echo('entity_tools:forms:owner_listing:description'),
]);

$offset = abs((int) elgg_extract('offset', $vars, get_input('offset', 0)));
// because you can say $vars['limit'] = 0
if (!$limit = (int) elgg_extract('limit', $vars, elgg_get_config('default_limit'))) {
	$limit = 10;
}

$entity_options = [
	'type' => 'object',
	'subtype' => $subtype,
	'offset' => $offset,
	'limit' => $limit,
];
if (!empty($owner_guid)) {
	$entity_options['owner_guid'] = $owner_guid;
	
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'owner_guid',
		'value' => $owner_guid,
	]);
}

if (!empty($container_guid)) {
	$entity_options['container_guid'] = $container_guid;
	
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'container_guid',
		'value' => $container_guid,
	]);
}

$entity_options = array_merge($entity_options, elgg_extract('entity_options', $vars, []));

$entities = elgg_get_entities($entity_options);

$supported = entity_tools_get_supported_entity_types();
$class = $supported[$subtype];

if ($entities) {
	// using the first entity to check supported options
	$migrate = new $class($entities[0]);

	$table_content = '';
	
	// header row
	$row = [];
	$row[] = elgg_format_element('th', [], elgg_echo('title'));
	if ($migrate->canBackDate()) {
		$row[] = elgg_format_element('th', [], elgg_echo('entity_tools:created'));
	}
	if ($migrate->canChangeOwner()) {
		$row[] = elgg_format_element('th', [], elgg_echo('entity_tools:owner'));
	}
	if ($migrate->canChangeContainer()) {
		$row[] = elgg_format_element('th', [], elgg_echo('entity_tools:container'));
	}
	$table_content .= elgg_format_element('thead', [], elgg_format_element('tr', [], implode(PHP_EOL, $row)));
	
	// content
	$rows = [];
	foreach ($entities as $entity) {
		$rows[] = elgg_view('entity_tools/listing/entity', ['entity' => $entity]);
	}
	$table_content .= elgg_format_element('tbody', [], implode(PHP_EOL, $rows));
	
	// draw table
	echo elgg_format_element('table', [
		'id' => 'entity-tools-listing-table',
		'class' => 'elgg-table mbm',
	], $table_content);
	
	$entity_options['count'] = true;
	$count = elgg_get_entities($entity_options);
	echo elgg_view('navigation/pagination', [
		'count' => $count,
		'offset' => $offset,
		'limit' => $limit,
	]);
	
	if (elgg_get_page_owner_guid() !== elgg_get_logged_in_user_guid()) {
		echo elgg_format_element('div', ['class' => 'elgg-subtext'], elgg_echo('entity_tools:forms:owner_listing:disclaimer'));
	}
} else {
	echo elgg_echo('notfound');
	return;
}

// form footer
$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
	'data-confirm' => elgg_echo('question:areyousure'),
]);
elgg_set_form_footer($footer);
