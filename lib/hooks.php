<?php
/**
 * All plugin hook handlers are bundled here
 */

/**
 * Add menu items to the filter menu
 *
 * @param string         $hook         the name of the hook
 * @param string         $type         the type of the hook
 * @param ElggMenuItem[] $return_value current return value
 * @param array          $params       supplied params
 *
 * @return ElggMenuItem[]
 */
function entity_tools_filter_menu_hook($hook, $type, $return_value, $params) {
	
	if (!elgg_in_context("entities")) {
		return $return_value;
	}
	
	$return_value = array();
	
	$page_owner = elgg_get_page_owner_entity();
	$priority = 10;
	
	if (elgg_instanceof($page_owner, "group")) {
		$href_prefix = "entities/group/" . $page_owner->getGUID(). "/";
	} else {
		$href_prefix = "entities/owner/" . $page_owner->username . "/";
	}
	
	$types = entity_tools_get_suported_entity_types();
	if (empty($types)) {
		return $return_value;
	}
	
	foreach ($types as $type => $subtypes) {
		if (!empty($subtypes)) {
			
			if (is_array($subtypes)) {
				foreach ($subtypes as $subtype) {
					$return_value[] = ElggMenuItem::factory(array(
						"name" => $type . ":" . $subtype,
						"text" => elgg_echo("item:" . $type . ":" . $subtype),
						"href" => $href_prefix . $subtype,
						"priority" => $priority
					));
				}
			} else {
				$return_value[] = ElggMenuItem::factory(array(
					"name" => $type . ":" . $subtypes,
					"text" => elgg_echo("item:" . $type . ":" . $subtypes),
					"href" => $href_prefix . $subtypes,
					"priority" => $priority
				));
			}
		} else {
			$return_value[] = ElggMenuItem::factory(array(
				"name" => $type,
				"text" => elgg_echo("item:" . $type),
				"href" => $href_prefix . $type,
				"priority" => $priority
			));
		}
		
		$priority += 10;
	}
	
	return $return_value;
}

/**
 * Set first item selected in filter menu
 *
 * @param string         $hook         the name of the hook
 * @param string         $type         the type of the hook
 * @param ElggMenuItem[] $return_value current return value
 * @param array          $params       supplied params
 *
 * @return ElggMenuItem[]
 */
function entity_tools_filter_menu_prepare_hook($hook, $type, $return_value, $params) {
	
	if (!elgg_in_context("entities")) {
		return $return_value;
	}
	
	if (empty($params) || !is_array($params)) {
		return $return_value;
	}
	
	if (!empty($params["selected_item"])) {
		return $return_value;
	}
	
	foreach ($return_value as $section => $items) {
		if (empty($items) || !is_array($items)) {
			continue;
		}
		
		foreach ($items as $index => $item) {
			$item->setSelected(true);
			break(2);
		}
	}
	
	return $return_value;
}

/**
 * Add menu item to user hover menu (admin section)
 *
 * @param string         $hook         the name of the hook
 * @param string         $type         the type of the hook
 * @param ElggMenuItem[] $return_value current return value
 * @param array          $params       supplied params
 *
 * @return ElggMenuItem[]
 */
function entity_tools_user_hover_menu_hook($hook, $type, $return_value, $params) {
	
	if (!elgg_is_admin_logged_in()) {
		return $return_value;
	}
	
	if (empty($params) || !is_array($params)) {
		return $return_value;
	}
	
	$user = elgg_extract("entity", $params);
	if (empty($user) || !elgg_instanceof($user, "user")) {
		return $return_value;
	}
	
	// add the admin menu
	$return_value[] = ElggMenuItem::factory(array(
		"name" => "entity_tools:admin",
		"text" => elgg_echo("entity_tools:menu:user_hover"),
		"href" => "entities/owner/" . $user->username,
		"section" => "admin",
		"priority" => 500
	));
	
	return $return_value;
}

/**
 * Add menu item to user owner block
 *
 * @param string         $hook         the name of the hook
 * @param string         $type         the type of the hook
 * @param ElggMenuItem[] $return_value current return value
 * @param array          $params       supplied params
 *
 * @return ElggMenuItem[]
 */
function entity_tools_owner_block_menu_hook($hook, $type, $return_value, $params) {
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	if (empty($loggedin_user)) {
		return $return_value;
	}
	
	if (empty($params) || !is_array($params)) {
		return $return_value;
	}
	
	$owner = elgg_extract("entity", $params);
	if (empty($owner) || (!elgg_instanceof($owner, "user") && !elgg_instanceof($owner, "group"))) {
		return $return_value;
	}
	
	if (elgg_instanceof($owner, "user") && (entity_tools_get_edit_access_setting() == "user")) {
		// depending on the plugin setting a user can go to the edit page
		if ($loggedin_user->getGUID() != $owner->getGUID()) {
			return $return_value;
		}
		
		$return_value[] = ElggMenuItem::factory(array(
			"name" => "entity_tools:user",
			"text" => elgg_echo("entity_tools:menu:owner_block"),
			"href" => "entities/owner/" . $owner->username,
			"context" => "profile",
			"priority" => 500
		));
	} elseif (elgg_instanceof($owner, "group")) {
		// admins always allowed
		// group owners only if setting is !'admin'
		if ($loggedin_user->isAdmin() || ((entity_tools_get_edit_access_setting() != "admin") && $owner->canEdit())) {
			$return_value[] = ElggMenuItem::factory(array(
				"name" => "entity_tools:group",
				"text" => elgg_echo("entity_tools:menu:owner_block:group"),
				"href" => "entities/group/" . $owner->getGUID(),
			));
		}
	}
	
	return $return_value;
}
