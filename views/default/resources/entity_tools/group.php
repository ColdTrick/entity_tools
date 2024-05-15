<?php

use Elgg\Exceptions\Http\GatekeeperException;

/* @var $page_owner \ElggGroup */
$page_owner = elgg_get_page_owner_entity();

$supported_types = array_keys(entity_tools_get_supported_entity_types());

$subtype = elgg_extract('subtype', $vars, $supported_types[0], false);
if (!in_array($subtype, $supported_types)) {
	throw new GatekeeperException(elgg_echo('entity_tools:error:unsupported_subtype', [$subtype]));
}

elgg_push_entity_breadcrumbs($page_owner);

$title_text = elgg_echo('entity_tools:page:group:title', [
	elgg_echo("collection:object:{$subtype}"),
	$page_owner->getDisplayName(),
]);

$content = elgg_view_form('entity_tools/update_entities', [], [
	'subtype' => $subtype,
	'container_guid' => $page_owner->guid,
]);

echo elgg_view_page($title_text, [
	'content' => $content,
	'filter_id' => 'entity_tools',
	'filter_value' => $subtype,
	'sidebar' => false,
]);
