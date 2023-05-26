<?php

namespace ColdTrick\EntityTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the owner_block menu
 */
class OwnerBlock {
	
	/**
	 * Add menu item to user owner block
	 *
	 * @param \Elgg\Event $event 'register', 'menu:owner_block'
	 *
	 * @return null|MenuItems
	 */
	public static function register(\Elgg\Event $event): ?MenuItems {
		$loggedin_user = elgg_get_logged_in_user_entity();
		if (empty($loggedin_user)) {
			return null;
		}
		
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		$owner = $event->getEntityParam();
		$access_setting = elgg_get_plugin_setting('edit_access', 'entity_tools');
		if ($owner instanceof \ElggUser && ($access_setting === 'user')) {
			// depending on the plugin setting a user can go to the edit page
			if ($loggedin_user->guid !== $owner->guid) {
				return null;
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
}
