<?php

$owner = elgg_extract('owner', $vars);
$subtype = elgg_extract('subtype', $vars);

// show some description
echo elgg_view('output/longtext', [
	'value' => elgg_echo('entity_tools:forms:owner_listing:description'),
]);

$entity_options = [
	'type' => 'object',
	'subtype' => $subtype,
	'limit' => 50,
];
if ($owner instanceof \ElggUser) {
	$entity_options['owner_guid'] = $owner->guid;
	
	echo elgg_view_input('hidden', [
		'name' => 'owner_guid',
		'value' => $owner->getGUID(),
	]);
} elseif ($owner instanceof \ElggGroup) {
	$entity_options['container_guid'] = $owner->guid;
	
	echo elgg_view_input('hidden', [
		'name' => 'container_guid',
		'value' => $owner->guid,
	]);
}

$entities = elgg_get_entities($entity_options);

$supported = entity_tools_get_supported_entity_types();
$class = $supported[$subtype];

if ($entities) {
	// using the first entity to check supported options
	$migrate = new $class($entities[0]);

	$rows = '<tr>';
	$rows .= '<th>' . elgg_echo('title') . '</th>';
	if ($migrate->canBackDate()) {
		$rows .= '<th>' . elgg_echo('entity_tools:created') . '</th>';
	}
	if ($migrate->canChangeOwner()) {
		$rows .= '<th>' . elgg_echo('entity_tools:owner') . '</th>';
	}
	if ($migrate->canChangeContainer()) {
		$rows .= '<th>' . elgg_echo('entity_tools:container') . '</th>';
	}
	$rows .= '</tr>';
	
	foreach ($entities as $entity) {
		$rows .= elgg_view('entity_tools/listing/entity', ['entity' => $entity]);
	}
	
	echo elgg_format_element('table', [
		'id' => 'entity-tools-listing-table',
		'class' => 'elgg-table mbm',
	], $rows);
} else {
	echo elgg_echo('notfound');
}

if (elgg_get_page_owner_guid() != elgg_get_logged_in_user_guid()) {
	echo elgg_format_element('div', ['class' => 'elgg-subtext'], elgg_echo('entity_tools:forms:owner_listing:disclaimer'));
}

echo elgg_view_input('hidden', [
	'name' => 'subtype',
	'value' => $subtype,
]);

echo '<div class="elgg-foot">';
echo elgg_view('input/submit', [
	'value' => elgg_echo('save'),
	'class' => 'elgg-button-submit',
	'data-confirm' => elgg_echo('question:areyousure'),
]);
echo '</div>';
