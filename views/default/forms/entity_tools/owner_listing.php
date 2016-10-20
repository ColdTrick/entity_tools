<?php

$owner = elgg_extract('owner', $vars);

// show some description
echo elgg_view('output/longtext', [
	'value' => elgg_echo('entity_tools:forms:owner_listing:description'),
]);

// show entities
echo elgg_view('entity_tools/listing/wrapper', $vars);

if (elgg_get_page_owner_guid() != elgg_get_logged_in_user_guid()) {
	echo elgg_format_element('div', ['class' => 'elgg-subtext'], elgg_echo('entity_tools:forms:owner_listing:disclaimer'));
}

echo elgg_view_input('hidden', [
	'name' => 'owner_guid',
	'value' => $owner->getGUID(),
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
