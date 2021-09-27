<?php

use ColdTrick\EntityTools\Gatekeeper;

require_once(__DIR__ . '/lib/functions.php');

$composer_path = '';
if (is_dir(__DIR__ . '/vendor')) {
	$composer_path = __DIR__ . '/';
}

return [
	'plugin' => [
		'version' => '7.0',
	],
	'actions' => [
		'entity_tools/update_entities' => [],
	],
	'hooks' => [
		'register' => [
			'menu:filter:entity_tools' => [
				'\ColdTrick\EntityTools\Menus::registerFilter' => []
			],
			'menu:user_hover' => [
				'\ColdTrick\EntityTools\Menus::registerUserHover' => []
			],
			'menu:owner_block' => [
				'\ColdTrick\EntityTools\Menus::registerOwnerBlock' => []
			],
			'menu:page' => [
				'\ColdTrick\EntityTools\Menus::registerAdmin' => []
			],
		],
	],
	'routes' => [
		'entity_tools:site' => [
			'path' => 'entities/site/{subtype?}',
			'resource' => 'entity_tools/site',
			'middleware' => [
				Gatekeeper::class,
			],
		],
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
			'jqueryui-timepicker-addon/' => $composer_path . 'vendor/npm-asset/jquery-ui-timepicker-addon/dist/',
			'jqueryui/css/' => 'vendor/npm-asset/components-jqueryui/themes/base/',
			'jquery-ui.js' => 'vendor/npm-asset/components-jqueryui/jquery-ui.min.js',
		],
	],
	'view_extensions' => [
		'elgg.css' => [
			'entity_tools/site.css' => [],
		],
	],
];
