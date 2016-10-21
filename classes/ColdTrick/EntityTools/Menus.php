<?php

namespace ColdTrick\EntityTools;

class Menus {
	/**
	 * Add menu items to the filter menu
	 *
	 * @param string $hook   hook name
	 * @param string $type   hook type
	 * @param array  $return current return value
	 * @param array  $params parameters
	 *
	 * @return array
	 */
	public static function registerFilter($hook, $type, $return, $params) {
		if (!elgg_in_context('entities')) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		
		if (elgg_instanceof($page_owner, 'group')) {
			$href_prefix = "entities/group/{$page_owner->getGUID()}/";
		} else {
			$href_prefix = "entities/owner/{$page_owner->username}/";
		}
		
		$types = array_keys(entity_tools_get_supported_entity_types());
		if (empty($types)) {
			return;
		}
		
		$return = [];
		$priority = 10;
		
		foreach ($types as $type) {
			$return[] = \ElggMenuItem::factory([
				'name' => $type,
				'text' => elgg_echo("item:object:{$type}"),
				'href' => $href_prefix . $type,
				'priority' => $priority,
			]);
			
			$priority += 10;
		}
		
		return $return;
	}

	/**
	 * Set first item selected in filter menu
	 *
	 * @param string $hook   hook name
	 * @param string $type   hook type
	 * @param array  $return current return value
	 * @param array  $params parameters
	 *
	 * @return array
	 */
	public static function prepareFilter($hook, $type, $return, $params) {
		if (!elgg_in_context('entities')) {
			return;
		}
		
		if (!empty(elgg_extract('selected_item', $params))) {
			return;
		}
		
		foreach ($return as $section => $items) {
			if (empty($items) || !is_array($items)) {
				continue;
			}
			
			foreach ($items as $index => $item) {
				$item->setSelected(true);
				break(2);
			}
		}
		
		return $return;
	}

	/**
	 * Add menu item to user hover menu (admin section)
	 *
	 * @param string $hook   hook name
	 * @param string $type   hook type
	 * @param array  $return current return value
	 * @param array  $params parameters
	 *
	 * @return array
	 */
	public static function registerUserHover($hook, $type, $return, $params) {
		if (!elgg_is_admin_logged_in()) {
			return;
		}
		
		$user = elgg_extract('entity', $params);
		if (!($user instanceof \ElggUser)) {
			return;
		}
		
		// add the admin menu
		$return[] = \ElggMenuItem::factory([
			'name' => 'entity_tools:admin',
			'text' => elgg_echo('entity_tools:menu:user_hover'),
			'href' => 'entities/owner/' . $user->username,
			'section' => 'admin',
			'priority' => 500,
		]);
		
		return $return;
	}

	/**
	 * Add menu item to user owner block
	 *
	 * @param string $hook   hook name
	 * @param string $type   hook type
	 * @param array  $return current return value
	 * @param array  $params parameters
	 *
	 * @return array
	 */
	public static function registerOwnerBlock($hook, $type, $return, $params) {
		$loggedin_user = elgg_get_logged_in_user_entity();
		if (empty($loggedin_user)) {
			return;
		}
	
		$owner = elgg_extract('entity', $params);
		$access_setting = elgg_get_plugin_setting('edit_access', 'entity_tools', 'admin');
		if ($owner instanceof \ElggUser && ($access_setting == 'user')) {
			// depending on the plugin setting a user can go to the edit page
			if ($loggedin_user->getGUID() != $owner->getGUID()) {
				return;
			}
			
			$return[] = \ElggMenuItem::factory([
				'name' => 'entity_tools:user',
				'text' => elgg_echo('entity_tools:menu:owner_block'),
				'href' => 'entities/owner/' . $owner->username,
				'context' => 'profile',
				'priority' => 500,
			]);
		} elseif ($owner instanceof \ElggGroup) {
			// admins always allowed
			// group owners only if setting is !'admin'
			if ($loggedin_user->isAdmin() || (($access_setting !== 'admin') && $owner->canEdit())) {
				$return[] = \ElggMenuItem::factory([
					'name' => 'entity_tools:group',
					'text' => elgg_echo('entity_tools:menu:owner_block:group'),
					'href' => 'entities/group/' . $owner->getGUID(),
				]);
			}
		}
		
		return $return;
	}
}