<?php

use ColdTrick\EntityTools\Gatekeeper;

require_once(__DIR__ . '/lib/functions.php');

$composer_path = '';
if (is_dir(__DIR__ . '/vendor')) {
	$composer_path = __DIR__ . '/';
}

return [
	'plugin' => [
		'version' => '8.0',
	],
	'actions' => [
		'entity_tools/update_entities' => [],
	],
	'events' => [
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
			'menu:admin_header' => [
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
			'detect_page_owner' => true,
		],
		'entity_tools:group' => [
			'path' => 'entities/group/{guid}/{subtype?}',
			'resource' => 'entity_tools/group',
			'middleware' => [
				Gatekeeper::class,
			],
			'detect_page_owner' => true,
		],
	],
	'settings' => [
		'edit_access' => 'admin',
	],
	'views' => [
		'default' => [
			'jquery-datetimepicker/' => $composer_path . 'vendor/npm-asset/jquery-datetimepicker/build/',
			'jquery-mousewheel.js' => $composer_path . 'vendor/npm-asset/jquery-mousewheel/jquery.mousewheel.js',
			'php-date-formatter.js' => $composer_path . 'vendor/npm-asset/php-date-formatter/js/php-date-formatter.min.js',
		],
	],
];
