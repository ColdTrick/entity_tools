<?php

$entities = elgg_extract('entities', $vars);
$container = elgg_extract('container', $vars);

// show some description
echo elgg_view('output/longtext', [
	'value' => elgg_echo('entity_tools:forms:container_listing:description'),
]);

// show entities
echo elgg_view('entity_tools/listing/wrapper', $vars);

echo elgg_view_input('hidden', [
	'name' => 'container_guid',
	'value' => $container->getGUID(),
]);

echo elgg_view_input('hidden', [
	'name' => 'type',
	'value' => elgg_extract('type', $vars),
]);

echo elgg_view_input('hidden', [
	'name' => 'subtype',
	'value' => elgg_extract('subtype', $vars),
]);

echo '<div class="elgg-foot">';
echo elgg_view('input/submit', [
	'value' => elgg_echo('save'),
	'class' => 'elgg-button-submit',
	'data-confirm' => elgg_echo('question:areyousure'),
]);
echo '</div>';
