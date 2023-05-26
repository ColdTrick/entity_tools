<?php

namespace ColdTrick\EntityTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the filter menus
 */
class Filter {
	
	/**
	 * Add menu items to the filter menu
	 *
	 * @param \Elgg\Event $event 'register', 'filter:entity_tools'
	 *
	 * @return null|MenuItems
	 */
	public static function registerEntityTools(\Elgg\Event $event): ?MenuItems {
		$types = array_keys(entity_tools_get_supported_entity_types());
		if (empty($types)) {
			return null;
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
			}
			
			return elgg_generate_url('entity_tools:site', [
				'subtype' => $subtype,
			]);
		};
		
		/* @var $return MenuItems */
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
}
