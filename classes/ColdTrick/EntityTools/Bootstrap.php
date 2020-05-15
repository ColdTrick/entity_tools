<?php

namespace ColdTrick\EntityTools;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 */
	public function init() {
		
		elgg_extend_view('css/elgg', 'css/entity_tools/site.css');
		
		// register plugin hooks
		$hooks = $this->elgg()->hooks;
		$hooks->registerHandler('register', 'menu:filter:entity_tools', __NAMESPACE__ . '\Menus::registerFilter');
		$hooks->registerHandler('register', 'menu:user_hover', __NAMESPACE__ . '\Menus::registerUserHover');
		$hooks->registerHandler('register', 'menu:owner_block', __NAMESPACE__ . '\Menus::registerOwnerBlock');
		$hooks->registerHandler('register', 'menu:page', __NAMESPACE__ . '\Menus::registerAdmin');
	}
}
