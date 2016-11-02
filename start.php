<?php

// register default Elgg events
elgg_register_event_handler('init', 'system', 'entity_tools_init');

/**
 * Called during system init
 *
 * @return void
 */
function entity_tools_init() {
	// register page handler
	elgg_register_page_handler('entities', 'entity_tools_page_handler');
	
	// register JS / CSS
	$base_url = elgg_get_site_url() . 'mod/entity_tools/vendors/jquery/';
	elgg_register_js('jquery.timepicker', $base_url . 'jquery-ui-timepicker-addon.js');
	elgg_register_js('jquery.slider', $base_url . 'jquery-ui-slider.js');
	elgg_register_css('jquery.timepicker', $base_url . 'jquery-ui-timepicker-addon.css');
	elgg_register_css('jquery.slider', $base_url . 'jquery-ui-slider.css');
	
	elgg_extend_view('css/elgg', 'css/entity_tools/site.css');
	
	// register plugin hooks
	elgg_register_plugin_hook_handler('register', 'menu:filter', '\ColdTrick\EntityTools\Menus::registerFilter');
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', '\ColdTrick\EntityTools\Menus::registerUserHover');
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', '\ColdTrick\EntityTools\Menus::registerOwnerBlock');
	elgg_register_plugin_hook_handler('prepare', 'menu:filter', '\ColdTrick\EntityTools\Menus::prepareFilter');
	
	// register actions
	elgg_register_action('entity_tools/update_entities', dirname(__FILE__) . '/actions/update_entities.php');
}

/**
 * Entity tools page handler
 *
 * @param array $page url parts
 *
 * @return true|void
 */
function entity_tools_page_handler($page) {
	$supported = array_keys(entity_tools_get_supported_entity_types());
	$subtype = elgg_extract(2, $page, $supported[0]);
	
	switch ($page[0]) {
		case 'owner':
		case 'group':
			echo elgg_view_resource('entity_tools/list', ['subtype' => $subtype]);
			return true;
	}
}

/**
 * Get the currently supported type/subtypes
 *
 * @return array
 */
function entity_tools_get_supported_entity_types() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$defaults = [];
	if (elgg_is_active_plugin('blog')) {
		$defaults['blog'] = '\ColdTrick\EntityTools\MigrateBlog';
	}
	if (elgg_is_active_plugin('discussions')) {
		$defaults['discussion'] = '\ColdTrick\EntityTools\MigrateDiscussion';
	}
	if (elgg_is_active_plugin('thewire')) {
		$defaults['thewire'] = '\ColdTrick\EntityTools\MigrateTheWire';
	}
	if (elgg_is_active_plugin('pages')) {
		$defaults['page_top'] = '\ColdTrick\EntityTools\MigratePages';
	}
	
	$result = elgg_trigger_plugin_hook('supported_types', 'entity_tools', [], $defaults);
	
	return $result;
}

/**
 * Check if the current user is allowed to edit the page owner
 *
 * @return void
 */
function entity_tools_gatekeeper() {
	$result = false;
	
	$user = elgg_get_logged_in_user_entity();
	if (!empty($user)) {
		if ($user->isAdmin()) {
			// admins are always allowed
			$result = true;
		} else {
			// check plugin setting for normal user
			$plugin_setting = elgg_get_plugin_setting('edit_access', 'entity_tools', 'admin');
			$page_owner = elgg_get_page_owner_entity();
			
			if (($page_owner instanceof ElggUser) || ($page_owner instanceof ElggGroup)) {
				switch ($plugin_setting) {
					case 'group':
						if (!($page_owner instanceof ElggGroup)) {
							break;
						}
					case 'user':
						$result = $page_owner->canEdit();
						break;
				}
			}
		}
	}
	
	if ($result) {
		return;
	}
	
	register_error(elgg_echo('entity_tools:error:check_edit_access'));
	forward(REFERER);
}
