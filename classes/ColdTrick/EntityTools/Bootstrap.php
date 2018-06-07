<?php

namespace ColdTrick\EntityTools;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 */
	public function init() {
		
		// register JS / CSS
		$base_url = elgg_get_site_url() . 'mod/entity_tools/vendors/jquery/';
		elgg_register_js('jquery.timepicker', $base_url . 'jquery-ui-timepicker-addon.js');
		elgg_register_js('jquery.slider', $base_url . 'jquery-ui-slider.js');
		elgg_register_css('jquery.timepicker', $base_url . 'jquery-ui-timepicker-addon.css');
		elgg_register_css('jquery.slider', $base_url . 'jquery-ui-slider.css');
		
		elgg_extend_view('css/elgg', 'css/entity_tools/site.css');
		
		// register plugin hooks
		$hooks = $this->elgg()->hooks;
		$hooks->registerHandler('register', 'menu:filter', __NAMESPACE__ . '\Menus::registerFilter');
		$hooks->registerHandler('register', 'menu:user_hover', __NAMESPACE__ . '\Menus::registerUserHover');
		$hooks->registerHandler('register', 'menu:owner_block', __NAMESPACE__ . '\Menus::registerOwnerBlock');
		$hooks->registerHandler('prepare', 'menu:filter', __NAMESPACE__ . '\Menus::prepareFilter');
	}
}
