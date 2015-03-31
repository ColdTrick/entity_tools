<?php

$entities = elgg_extract("entities", $vars);
$owner = elgg_extract("owner", $vars);
$type = elgg_extract("type", $vars);
$subtype = elgg_extract("subtype", $vars);

// show some description
echo elgg_view("output/longtext", array("value" => elgg_echo("entity_tools:forms:owner_listing:description")));

// show entities
echo elgg_view("entity_tools/listing/wrapper", $vars);

// other form data
echo "<div>";
echo elgg_view("input/hidden", array("name" => "owner_guid", "value" => $owner->getGUID()));
echo elgg_view("input/hidden", array("name" => "type", "value" => $type));
echo elgg_view("input/hidden", array("name" => "subtype", "value" => $subtype));
echo elgg_view("input/submit", array("value" => elgg_echo("save"), "class" => "elgg-button-submit elgg-requires-confirmation"));
echo elgg_view("input/reset", array("value" => elgg_echo("reset")));
echo "</div>";

if (elgg_get_page_owner_guid() != elgg_get_logged_in_user_guid()) {
	echo "<div class='elgg-foot elgg-subtext'>";
	echo elgg_echo("entity_tools:forms:owner_listing:disclaimer");
	echo "</div>";
}