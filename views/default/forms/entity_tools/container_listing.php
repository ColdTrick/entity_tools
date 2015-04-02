<?php

$entities = elgg_extract("entities", $vars);
$container = elgg_extract("container", $vars);
$type = elgg_extract("type", $vars);
$subtype = elgg_extract("subtype", $vars);

// show some description
echo elgg_view("output/longtext", array("value" => elgg_echo("entity_tools:forms:container_listing:description")));

// show entities
echo elgg_view("entity_tools/listing/wrapper", $vars);

// other form data
echo "<div>";
echo elgg_view("input/hidden", array("name" => "container_guid", "value" => $container->getGUID()));
echo elgg_view("input/hidden", array("name" => "type", "value" => $type));
echo elgg_view("input/hidden", array("name" => "subtype", "value" => $subtype));
echo elgg_view("input/submit", array("value" => elgg_echo("save"), "class" => "elgg-button-submit elgg-requires-confirmation"));
echo elgg_view("input/reset", array("value" => elgg_echo("reset")));
echo "</div>";
