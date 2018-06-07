<?php

use ColdTrick\EntityTools\Bootstrap;
use ColdTrick\EntityTools\Gatekeeper;

require_once(__DIR__ . '\lib\functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'actions' => [
		'entity_tools/update_entities' => [],
	],
	'routes' => [
		'entity_tools:owner' => [
			'path' => 'entities/owner/{username}/{subtype?}',
			'resource' => 'entity_tools/owner',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'entity_tools:group' => [
			'path' => 'entities/group/{guid}/{subtype?}',
			'resource' => 'entity_tools/group',
			'middleware' => [
				Gatekeeper::class,
			],
		],
	],
	'settings' => [
		'edit_access' => 'admin',
	],
];
