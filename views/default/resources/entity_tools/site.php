<?php

use Elgg\GatekeeperException;

// get supported types
$supported_types = array_keys(entity_tools_get_supported_entity_types());

$subtype = elgg_extract('subtype', $vars);
if (empty($subtype)) {
	$subtype = $supported_types[0];
}

if (!in_array($subtype, $supported_types)) {
	throw new GatekeeperException(elgg_echo('entity_tools:error:unsupported_subtype', [$subtype]));
}

elgg_push_breadcrumb(elgg_echo("item:object:{$subtype}"), false);
	
$title_text = elgg_echo('entity_tools:page:owner:title', [
	elgg_get_site_entity()->getDisplayName(),
	elgg_echo("item:object:{$subtype}"),
]);

$content = elgg_view_form('entity_tools/update_entities', [], [
	'subtype' => $subtype,
	'owner_guid' => elgg_get_site_entity()->guid,
]);

// build page
$page_data = elgg_view_layout('default', [
	'title' => $title_text,
	'content' => $content,
	'filter_id' => 'entity_tools',
	'filter_value' => $subtype,
	'sidebar' => false,
]);

// show page
echo elgg_view_page($title_text, $page_data);
