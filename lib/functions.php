<?php
/**
 * All helper functions are bundled here
 */

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
