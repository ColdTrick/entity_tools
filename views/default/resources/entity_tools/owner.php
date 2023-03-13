<?php

use Elgg\Exceptions\Http\EntityNotFoundException;
use Elgg\Exceptions\Http\GatekeeperException;

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner instanceof ElggUser) {
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

// breadcrumb
elgg_push_breadcrumb($page_owner->getDisplayName(), $page_owner->getURL());
elgg_push_breadcrumb(elgg_echo('entity_tools:menu:owner_block'), elgg_generate_url('entity_tools:owner', [
	'username' => $page_owner->username
]));
elgg_push_breadcrumb(elgg_echo("collection:object:{$subtype}"), false);

// page components
$title_text = elgg_echo('entity_tools:page:owner:title', [
	$page_owner->getDisplayName(),
	elgg_echo("collection:object:{$subtype}"),
]);

$content = elgg_view_form('entity_tools/update_entities', [], [
	'subtype' => $subtype,
	'owner_guid' => $page_owner->guid,
]);

// show page
echo elgg_view_page($title_text, [
	'content' => $content,
	'filter_id' => 'entity_tools',
	'filter_value' => $subtype,
	'sidebar' => false,
]);
