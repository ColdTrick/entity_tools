<?php

	elgg_load_js("jquery.timepicker");
	elgg_load_js("jquery.slider");
	elgg_load_css("jquery.timepicker");
	elgg_load_css("jquery.slider");
	
	if (isset($vars["class"])) {
		$vars["class"] = "elgg-input-datetime {$vars["class"]}";
	} else {
		$vars["class"] = "elgg-input-datetime";
	}
	
	$defaults = array(
		"value" => "",
		"disabled" => false,
		"timestamp" => false,
	);
	
	$vars = array_merge($defaults, $vars);
	
	$timestamp = elgg_extract("timestamp", $vars, false);
	unset($vars["timestamp"]);
	
	if ($timestamp) {
		echo elgg_view("input/hidden", array(
			"name" => $vars["name"],
			"value" => $vars["value"],
		));
	
		$vars["class"] = "{$vars["class"]} elgg-input-timestamp";
		$vars["id"] = $vars["name"];
		unset($vars["name"]);
		unset($vars["internalname"]);
	}
	
	// convert timestamps to text for display
	if (is_numeric($vars["value"])) {
		$vars["value"] = gmdate("Y-m-d H:i", $vars["value"]);
	}
	
	$attributes = elgg_format_attributes($vars);
	echo "<input type=\"text\" $attributes />";