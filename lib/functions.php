<?php
/**
 * All helper functions are bundled here
 */

use ColdTrick\EntityTools\Migrate;
use ColdTrick\EntityTools\Migrate\Blog;
use ColdTrick\EntityTools\Migrate\Discussion;
use ColdTrick\EntityTools\Migrate\TheWire;
use ColdTrick\EntityTools\Migrate\Pages;

/**
 * Get the currently supported type/subtypes
 *
 * @return array
 */
function entity_tools_get_supported_entity_types(): array {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$defaults = [];
	if (elgg_is_active_plugin('blog')) {
		$defaults['blog'] = Blog::class;
	}
	
	if (elgg_is_active_plugin('discussions')) {
		$defaults['discussion'] = Discussion::class;
	}
	
	if (elgg_is_active_plugin('thewire')) {
		$defaults['thewire'] = TheWire::class;
	}
	
	if (elgg_is_active_plugin('pages')) {
		$defaults['page'] = Pages::class;
	}
	
	$result = (array) elgg_trigger_event_results('supported_types', 'entity_tools', [], $defaults);
	
	// make sure we have valid classes
	foreach ($result as $subtype => $class) {
		if (is_subclass_of($class, Migrate::class)) {
			continue;
		}
		
		elgg_log("{$class} needs to implement " . Migrate::class, 'ERROR');
		unset($result[$subtype]);
	}
	
	return $result;
}
