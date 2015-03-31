<?php

// only when logged in
elgg_gatekeeper();

// depending on plugin setting who can view this page
entity_tools_check_edit_access();

// make sure we can edit the page owner
$page_owner = elgg_get_page_owner_entity();
if (empty($page_owner) || !elgg_instanceof($page_owner, "user") || !$page_owner->canEdit()) {
	register_error(elgg_echo("pageownerunavailable", array(elgg_get_page_owner_guid())));
	forward(REFERER);
}

// get input
$valid_subtype = false;

$type = "object";
$subtype = get_input("subtype");

$offset = (int) get_input("offset", 0);
$offset = max(0, $offset);

// get supported types
$supported_types = entity_tools_get_suported_entity_types();

if (empty($supported_types) || !is_array($supported_types)) {
	register_error(elgg_echo("entity_tools:error:unsupported_subtype", array($subtype)));
	forward(REFERER);
}

foreach ($supported_types as $type => $subtypes) {
	if (empty($subtypes) || !is_array($subtypes)) {
		continue;
	}
	
	foreach ($subtypes as $allowed_subtype) {
		if (!empty($subtype)) {
			if ($subtype == $allowed_subtype) {
				$valid_subtype = true;
				break;
			}
		} else {
			$subtype = $allowed_subtype;
			$valid_subtype = true;
			break;
		}
	}
}

if (!$valid_subtype) {
	register_error(elgg_echo("entity_tools:error:unsupported_subtype", array($subtype)));
	forward(REFERER);
}
	
$title_text = elgg_echo("entity_tools:page:owner:title", array($page_owner->name, elgg_echo("item:" . $type .":" . $subtype)));

$options = array(
	"type" => $type,
	"subtype" => $subtype,
	"limit" => 50,
	"offset" => $offset,
	"owner_guid" => $page_owner->getGUID()
);

$entities = elgg_get_entities($options);
if (!empty($entities)) {
	$form_vars = array(
		"action" => "action/entity_tools/update_entities"
	);
	
	$body_vars = array(
		"owner" => $page_owner,
		"entities" => $entities,
		"type" => $type,
		"subtype" => $subtype
	);
	
	$content = elgg_view_form("entity_tools/owner_listing", $form_vars, $body_vars);
} else {
	$content = elgg_echo("notfound");
}

// build page
$page_data = elgg_view_layout("content", array(
	"title" => $title_text,
	"content" => $content
));

// show page
echo elgg_view_page($title_text, $page_data);
