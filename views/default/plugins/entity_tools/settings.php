<?php

$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('entity_tools:settings:edit_access'),
	'name' => 'params[edit_access]',
	'value' => $plugin->edit_access,
	'options_values' => [
		'admin' => elgg_echo('entity_tools:settings:edit_access:admin'),
		'group' => elgg_echo('entity_tools:settings:edit_access:group'),
		'user' => elgg_echo('entity_tools:settings:edit_access:user'),
	],
]);
