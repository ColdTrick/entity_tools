<?php

	$entity = elgg_extract("entity", $vars);
	
	$owner_options = entity_tools_get_owner_options($entity);
	$container_options = entity_tools_get_container_options($entity);
	
	echo "<tr>";
	echo "<td>" . elgg_view("output/url", array("text" => $entity->title, "href" => $entity->getURL())) . "</td>";
	echo "<td>" . elgg_view("input/datetimepicker", array("name" => "params[" . $entity->getGUID() . "][time_created]", "value" => $entity->time_created, "timestamp" => true)) . "</td>";
	echo "<td>" . elgg_view("input/dropdown", array("name" => "params[" . $entity->getGUID() . "][owner_guid]", "value" => $entity->getOwnerGUID(), "options_values" => $owner_options)) . "</td>";
	echo "<td>" . elgg_view("input/dropdown", array("name" => "params[" . $entity->getGUID() . "][container_guid]", "value" => $entity->getContainerGUID(), "options_values" => $container_options)) . "</td>";
	echo "</tr>";