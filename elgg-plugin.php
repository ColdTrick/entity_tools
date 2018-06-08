<?php

use ColdTrick\EntityTools\Bootstrap;
use ColdTrick\EntityTools\Gatekeeper;

require_once(__DIR__ . '\lib\functions.php');

$composer_path = '';
if (is_dir(__DIR__ . '/vendor')) {
	$composer_path = __DIR__ . '/';
}

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
	'views' => [
		'default' => [
			'jqueryui-timepicker-addon/' => $composer_path . 'vendor/bower-asset/jqueryui-timepicker-addon/dist/',
			'jqueryui/css/' => 'vendor/bower-asset/jquery-ui/themes/base/',
		],
	],
];
