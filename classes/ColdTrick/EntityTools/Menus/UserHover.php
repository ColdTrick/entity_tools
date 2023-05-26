<?php

namespace ColdTrick\EntityTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the user_hover menu
 */
class UserHover {
	
	/**
	 * Add menu item to user hover menu (admin section)
	 *
	 * @param \Elgg\Event $event 'register', 'menu:user_hover'
	 *
	 * @return null|MenuItems
	 */
	public static function register(\Elgg\Event $event): ?MenuItems {
		if (!elgg_is_admin_logged_in()) {
			return null;
		}
		
		$user = $event->getEntityParam();
		if (!$user instanceof \ElggUser) {
			return null;
		}
		
		/* @var $return MenuItems */
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
}
