<?php

	$plugin = elgg_extract("entity", $vars);
	
	$edit_access_options = array(
		"admin" => elgg_echo("entity_tools:settings:edit_access:admin"),
		"user" => elgg_echo("entity_tools:settings:edit_access:user")
	);
	
	echo "<div>";
	echo "<label>" . elgg_echo("entity_tools:settings:edit_access") . "</label>";
	echo "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[edit_access]", "value" => $plugin->edit_access, "options_values" => $edit_access_options));
	echo "</div>";