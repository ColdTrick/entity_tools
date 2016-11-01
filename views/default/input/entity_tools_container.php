<?php

$entity = elgg_extract('entity', $vars);
if (empty($entity)) {
	return;
}

$page_owner = elgg_get_page_owner_entity();
$owner = $entity->getOwnerEntity();
$container = $entity->getContainerEntity();
$user = elgg_get_logged_in_user_entity();

$add_users = (bool) elgg_extract('add_users', $vars, true);
$add_groups = (bool) elgg_extract('add_groups', $vars, true);

// log unique guids
$temp_array = [$container->getGUID()];

// add the current container
$result[elgg_echo('entity_tools:dropdown:label:current_value')] = [$container->getGUID() => $container->name];

// allow moving to user
if ($add_users) {
	// add the owner (if not the current container)
	if ($container->getGUID() != $owner->getGUID()) {
		$result[elgg_echo('entity_tools:dropdown:label:owner')] = [$owner->getGUID() => $owner->name];
		
		// add the guid to the filter
		$temp_array[] = $owner->getGUID();
	}
}

// allow moving to group
if ($add_groups) {
	// build default group options
	$dbprefix = elgg_get_config('dbprefix');
	$group_options = [
		'limit' => false,
		'joins' => [
			"JOIN {$dbprefix}groups_entity ge ON e.guid = ge.guid",
		],
		'order_by' => 'ge.name',
	];
	
	if (elgg_instanceof($page_owner, 'user')) {
		// add the groups of the current owner
		$owner_groups = $owner->getGroups($group_options);
		if (!empty($owner_groups)) {
			if ($owner->getGUID() == $user->getGUID()) {
				$label = elgg_echo('entity_tools:dropdown:label:my_groups');
			} else {
				$label = elgg_echo('entity_tools:dropdown:label:owner_groups');
			}
			
			// add label
			$result[$label] = [];
			
			foreach ($owner_groups as $group) {
				// check if group not already proccessed
				if (in_array($group->getGUID(), $temp_array)) {
					continue;
				}
				
				// add group
				$result[$label][$group->getGUID()] = $group->name;
				
				// add the guid to the filter
				$temp_array[] = $group->getGUID();
			}
			
			// check for empty label
			if (empty($result[$label])) {
				unset($result[$label]);
			}
		}
	}
	
	// add the groups of the current user (if not the owner)
	if ($page_owner->getGUID() !== $user->getGUID()) {
		$user_groups = $user->getGroups($group_options);
		if (!empty($user_groups)) {
			// add label
			$result[elgg_echo('entity_tools:dropdown:label:my_groups')] = [];
			
			foreach ($user_groups as $group) {
				if (in_array($group->getGUID(), $temp_array)) {
					continue;
				}
				
				$postfix = (!$group->isMember($owner)) ? '*' : '';
				
				// add group
				$result[elgg_echo('entity_tools:dropdown:label:my_groups')][$group->getGUID()] = $group->name . $postfix;
				
				// add the guid to the filter
				$temp_array[] = $group->getGUID();
			}
			
			// check for empty label
			if (empty($result[elgg_echo('entity_tools:dropdown:label:my_groups')])) {
				unset($result[elgg_echo('entity_tools:dropdown:label:my_groups')]);
			}
		}
	}
}

$vars['options_values'] = $result;

echo elgg_view('input/dropdown_label', $vars);
