<?php

use Elgg\EntityNotFoundException;
use Elgg\GatekeeperException;

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner instanceof ElggGroup) {
	throw new EntityNotFoundException();
}

// get supported types
$supported_types = array_keys(entity_tools_get_supported_entity_types());

$subtype = elgg_extract('subtype', $vars);
if (empty($subtype)) {
	$subtype = $supported_types[0];
}

if (!in_array($subtype, $supported_types)) {
	throw new GatekeeperException(elgg_echo('entity_tools:error:unsupported_subtype', [$subtype]));
}
	
$title_text = elgg_echo('entity_tools:page:group:title', [
	elgg_echo("item:object:{$subtype}"),
	$page_owner->getDisplayName(),
]);

$content = elgg_view_form('entity_tools/update_entities', [], [
	'subtype' => $subtype,
	'container_guid' => $page_owner->guid,
]);

// build page
$page_data = elgg_view_layout('default', [
	'title' => $title_text,
	'content' => $content,
]);

// show page
echo elgg_view_page($title_text, $page_data);
