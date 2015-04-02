<?php

$plugin = elgg_extract("entity", $vars);

$edit_access_options = array(
	"admin" => elgg_echo("entity_tools:settings:edit_access:admin"),
	"group" => elgg_echo("entity_tools:settings:edit_access:group"),
	"user" => elgg_echo("entity_tools:settings:edit_access:user")
);

echo "<div>";
echo "<label>" . elgg_echo("entity_tools:settings:edit_access") . "</label>";
echo elgg_view("input/select", array(
	"name" => "params[edit_access]",
	"value" => $plugin->edit_access,
	"options_values" => $edit_access_options,
	"class" => "mls"
));
echo "</div>";