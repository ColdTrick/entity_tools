<?php

$entities = elgg_extract("entities", $vars);
if (empty($entities)) {
	return;
}

echo "<table id='entity-tools-listing-table' class='elgg-table mbm'>";

echo "<tr>";
echo "<th>" . elgg_echo("title") . "</th>";
echo "<th>" . elgg_echo("entity_tools:created") . "</th>";
echo "<th>" . elgg_echo("entity_tools:owner") . "</th>";
echo "<th>" . elgg_echo("entity_tools:container") . "</th>";
echo "</tr>";

foreach ($entities as $entity) {
	echo elgg_view("entity_tools/listing/entity", array("entity" => $entity));
}

echo "</table>";
