<?php

namespace ColdTrick\EntityTools;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 */
	public function init() {
		
		// register JS / CSS
		elgg_register_css('jquery.timepicker', elgg_get_simplecache_url('jqueryui-timepicker-addon/jquery-ui-timepicker-addon.min.css'));
		elgg_register_css('jqueryui', elgg_get_simplecache_url('jqueryui/css/jquery-ui.min.css'));
		
		elgg_extend_view('css/elgg', 'css/entity_tools/site.css');
		
		// register plugin hooks
		$hooks = $this->elgg()->hooks;
		$hooks->registerHandler('register', 'menu:filter:entity_tools', __NAMESPACE__ . '\Menus::registerFilter');
		$hooks->registerHandler('register', 'menu:user_hover', __NAMESPACE__ . '\Menus::registerUserHover');
		$hooks->registerHandler('register', 'menu:owner_block', __NAMESPACE__ . '\Menus::registerOwnerBlock');
	}
}
