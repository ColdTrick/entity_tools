<?php

namespace ColdTrick\EntityTools;

use Elgg\Menu\MenuItems;

/**
 * Menu callbacks
 */
class Menus {
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param \Elgg\Event $event 'register', 'filter:entity_tools'
	 *
	 * @return void|MenuItems
	 */
	public static function registerFilter(\Elgg\Event $event) {
		
		$types = array_keys(entity_tools_get_supported_entity_types());
		if (empty($types)) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		
		$generate_url = function($subtype) use ($page_owner) {
			if ($page_owner instanceof \ElggGroup) {
				return elgg_generate_url('entity_tools:group', [
					'guid' => $page_owner->guid,
					'subtype' => $subtype,
				]);
			} elseif ($page_owner instanceof \ElggUser) {
				return elgg_generate_url('entity_tools:owner', [
					'username' => $page_owner->username,
					'subtype' => $subtype,
				]);
			} else {
				return elgg_generate_url('entity_tools:site', [
					'subtype' => $subtype,
				]);
			}
			
			return false;
		};
		
		$return = $event->getValue();
		$selected = $event->getParam('filter_value');
		
		$priority = 10;
		
		foreach ($types as $type) {
			$key = "collection:object:{$type}";
			if (!elgg_language_key_exists($key)) {
				$key = "item:object:{$type}";
			}
			
			$return[] = \ElggMenuItem::factory([
				'name' => $type,
				'text' => elgg_echo($key),
				'href' => $generate_url($type),
				'priority' => $priority,
				'selected' => $type === $selected,
			]);
			
			$priority += 10;
		}
		
		return $return;
	}

	/**
	 * Add menu item to user hover menu (admin section)
	 *
	 * @param \Elgg\Event $event 'register', 'menu:user_hover'
	 *
	 * @return void|MenuItems
	 */
	public static function registerUserHover(\Elgg\Event $event) {
		
		if (!elgg_is_admin_logged_in()) {
			return;
		}
		
		$user = $event->getEntityParam();
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		$return = $event->getValue();
		
		// add the admin menu
		$return[] = \ElggMenuItem::factory([
			'name' => 'entity_tools:admin',
			'text' => elgg_echo('entity_tools:menu:user_hover'),
			'href' => elgg_generate_url('entity_tools:owner', [
				'username' => $user->username,
			]),
			'icon' => 'archive',
			'section' => 'admin',
			'priority' => 500,
		]);
		
		return $return;
	}

	/**
	 * Add menu item to user owner block
	 *
	 * @param \Elgg\Event $event 'register', 'menu:owner_block'
	 *
	 * @return void|MenuItems
	 */
	public static function registerOwnerBlock(\Elgg\Event $event) {
		
		$loggedin_user = elgg_get_logged_in_user_entity();
		if (empty($loggedin_user)) {
			return;
		}
		
		$return = $event->getValue();
		
		$owner = $event->getEntityParam();
		$access_setting = elgg_get_plugin_setting('edit_access', 'entity_tools');
		if ($owner instanceof \ElggUser && ($access_setting === 'user')) {
			// depending on the plugin setting a user can go to the edit page
			if ($loggedin_user->guid !== $owner->guid) {
				return;
			}
			
			$return[] = \ElggMenuItem::factory([
				'name' => 'entity_tools:user',
				'text' => elgg_echo('entity_tools:menu:owner_block'),
				'href' => elgg_generate_url('entity_tools:owner', [
					'username' => $owner->username,
				]),
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
					'href' => elgg_generate_url('entity_tools:group', [
						'guid' => $owner->guid,
					]),
				]);
			}
		}
		
		return $return;
	}
	
	/**
	 * Add menu items to the admin page menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:admin_header'
	 *
	 * @return void|MenuItems
	 */
	public static function registerAdmin(\Elgg\Event $event) {
		
		if (!elgg_is_admin_logged_in()) {
			return;
		}
		
		$return = $event->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'entity_tools',
			'href' => elgg_generate_url('entity_tools:site'),
			'text' => elgg_echo('entity_tools:menu:admin'),
			'parent_name' => 'administer_utilities',
		]);
		
		return $return;
	}
}
