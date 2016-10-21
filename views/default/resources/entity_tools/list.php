<?php

// only when logged in
elgg_gatekeeper();

// depending on plugin setting who can view this page
entity_tools_gatekeeper();

// make sure we can edit the page owner
$page_owner = elgg_get_page_owner_entity();
if (empty($page_owner) || !$page_owner->canEdit()) {
	register_error(elgg_echo('pageownerunavailable', [$page_owner->guid]));
	forward(REFERER);
}

$subtype = elgg_extract('subtype', $vars);

// get supported types
$supported_types = array_keys(entity_tools_get_supported_entity_types());
if (!in_array($subtype, $supported_types)) {
	register_error(elgg_echo('entity_tools:error:unsupported_subtype', [$subtype]));
	forward(REFERER);
}
	
$title_text = elgg_echo('entity_tools:page:owner:title', [$page_owner->name, elgg_echo('item:object:' . $subtype)]);

$content = elgg_view_form('entity_tools/update_entities', [], [
	'subtype' => $subtype,
	'owner' => $page_owner,
]);

// build page
$page_data = elgg_view_layout('content', array(
	'title' => $title_text,
	'content' => $content
));

// show page
echo elgg_view_page($title_text, $page_data);
