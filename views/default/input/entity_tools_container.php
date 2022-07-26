<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggEntity) {
	return;
}

$page_owner = elgg_get_page_owner_entity();
$owner = $entity->getOwnerEntity();
$container = $entity->getContainerEntity();
$user = elgg_get_logged_in_user_entity();
$site = elgg_get_site_entity();

$add_site = (bool) elgg_extract('add_site', $vars, false);
$add_users = (bool) elgg_extract('add_users', $vars, true);
$add_groups = (bool) elgg_extract('add_groups', $vars, true);

// log unique guids
$temp_array = [$container->guid];

$container_display_name = $container->getDisplayName();
if ($container instanceof \ElggSite) {
	$container_display_name = elgg_echo('item:site:site') . ': ' . $container_display_name;
}

// add the current container
$options_values = [
	[
		'label' => elgg_echo('entity_tools:dropdown:label:current_value'),
		'options' => [
			[
				'text' => $container_display_name,
				'value' => $container->guid,
			],
		],
	]
];

// allow moving to site
if ($add_site) {
	// add the owner (if not the current container)
	if ($container->guid !== $site->guid) {
		$options_values[] = [
			'label' => elgg_echo('entity_tools:dropdown:label:site'),
			'options' => [
				[
					'text' => $site->getDisplayName(),
					'value' => $site->guid,
				],
			],
		];
		
		// add the guid to the filter
		$temp_array[] = $site->guid;
	}
}

// allow moving to user
if ($add_users) {
	// add the owner (if not the current container)
	if ($container->guid !== $owner->guid) {
		$options_values[] = [
			'label' => elgg_echo('entity_tools:dropdown:label:owner'),
			'options' => [
				[
					'text' => $owner->getDisplayName(),
					'value' => $owner->guid,
				],
			],
		];
		
		// add the guid to the filter
		$temp_array[] = $owner->guid;
	}
}

// allow moving to group
if ($add_groups) {
	// build default group options
	$group_options = [
		'sort_by' => [
			'property' => 'name',
			'direction' => 'ASC',
		],
		'limit' => false,
		'batch' => true,
	];
	
	// add the groups of the current (page)owner
	if ($page_owner instanceof \ElggUser) {
		// make label
		if ($owner->guid === $user->guid) {
			$label = elgg_echo('entity_tools:dropdown:label:my_groups');
		} else {
			$label = elgg_echo('entity_tools:dropdown:label:owner_groups');
		}
		
		$groups = [
			'label' => $label,
			'options' => [],
		];
		
		$owner_groups = $owner->getGroups($group_options);
		/* @var $group ElggGroup */
		foreach ($owner_groups as $group) {
			// check if group not already proccessed
			if (in_array($group->guid, $temp_array)) {
				continue;
			}
			
			// add group
			$groups['options'][] = [
				'text' => $group->getDisplayName(),
				'value' => $group->guid,
			];
			
			// add the guid to the filter
			$temp_array[] = $group->guid;
		}
		
		// check for empty label
		if (!empty($groups['options'])) {
			$options_values[] = $groups;
		}
	}
	
	// add the groups of the current user (if not the owner)
	if (empty($page_owner) || ($page_owner->guid !== $user->guid)) {
		$groups = [
			'label' => elgg_echo('entity_tools:dropdown:label:my_groups'),
			'options' => [],
		];
		
		$user_groups = $user->getGroups($group_options);
		/* @var $group ElggGroup */
		foreach ($user_groups as $group) {
			if (in_array($group->guid, $temp_array)) {
				continue;
			}
			
			$postfix = '';
			if ($owner instanceof \ElggUser && !$group->isMember($owner)) {
				$postfix = '*';
			}
			
			// add group
			$groups['options'][] = [
				'text' => $group->getDisplayName() . $postfix,
				'value' => $group->guid,
			];
			
			// add the guid to the filter
			$temp_array[] = $group->guid;
		}
		
		// check for empty label
		if (!empty($groups['options'])) {
			$options_values[] = $groups;
		}
	}
}

$vars['options_values'] = $options_values;
echo elgg_view('input/select', $vars);
