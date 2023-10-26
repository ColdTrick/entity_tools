<?php

namespace ColdTrick\EntityTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the admin_header menu
 */
class AdminHeader {
	
	/**
	 * Add menu items to the admin page menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:admin_header'
	 *
	 * @return null|MenuItems
	 */
	public static function register(\Elgg\Event $event): ?MenuItems {
		if (!elgg_is_admin_logged_in()) {
			return null;
		}
		
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'entity_tools',
			'href' => elgg_generate_url('entity_tools:site'),
			'text' => elgg_echo('entity_tools:menu:admin'),
			'parent_name' => 'utilities',
		]);
		
		return $return;
	}
}
