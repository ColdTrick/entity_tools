<?php

/**
 * Update the anwsers to a question
 *
 * @param ElggQuestion $entity the question to check
 *
 * @return bool
 */
function entity_tools_update_answers_access(ElggQuestion $entity) {

	if (empty($entity) || !elgg_instanceof($entity, "object", "question")) {
		return false;
	}

	$options = array(
		"type" => "object",
		"subtype" => "answer",
		"limit" => false,
		"container_guid" => $entity->getGUID(),
		"wheres" => array("e.access_id <> " . $entity->access_id)
	);
	
	$ia = elgg_set_ignore_access(true);
	
	$entities = new ElggBatch("elgg_get_entities", $options);
	foreach ($entities as $answer) {
		$answer->access_id = $entity->access_id;
		$answer->save();
	}

	elgg_set_ignore_access($ia);
	return true;
}
