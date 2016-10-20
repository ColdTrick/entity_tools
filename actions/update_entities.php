<?php

$type = get_input('type');
$subtype = get_input('subtype');
$owner_guid = (int) get_input('owner_guid');
$container_guid = (int) get_input('container_guid');

$params = get_input('params');

if (empty($type) || empty($subtype) || (empty($owner_guid) && empty($container_guid)) || empty($params) || !is_array($params)) {
	register_error(elgg_echo('entity_tools:action:update_entities:error:input'));
	forward(REFERER);
}

$update_count = 0;
$error_count = 0;

foreach ($params as $guid => $options) {
	// get the entity, and check if we can update the entity
	$entity = get_entity($guid);
	if (empty($entity) || !$entity->canEdit()) {
		$error_count++;
		continue;
	}
	
	//validate entity
	if (($entity->getType() != $type) || ($entity->getSubtype() != $subtype) || (($entity->getOwnerGUID() != $owner_guid) && ($entity->getContainerGUID() != $container_guid))) {
		$error_count++;
		continue;
	}
	
	$update_needed = false;
	
	// get new values
	$new_time_created = elgg_extract('time_created', $options, $entity->time_created);
	$new_owner_guid = elgg_extract('owner_guid', $options, $entity->getOwnerGUID());
	$new_container_guid = elgg_extract('container_guid', $options, $entity->getContainerGUID());
	
	if (is_array($new_owner_guid)) {
		$new_owner_guid = (int) $new_owner_guid[0];
	}
	
	// get current values
	$old_time_created = $entity->time_created;
	$old_owner_guid = $entity->getOwnerGUID();
	$old_container_guid = $entity->getContainerGUID();
	
	// check for time_created
	if ($new_time_created != $old_time_created) {
		$entity->time_created = $new_time_created;
		
		$update_needed = true;
	}
	
	// check for owner_guid
	if ($new_owner_guid != $old_owner_guid) {
		$entity->owner_guid = $new_owner_guid;
		
		$update_needed = true;
		
		switch ($subtype) {
			case 'blog':
				// with blogs also transfer icon (if needed)
				entity_tools_move_blog_icon($entity, $old_owner_guid);
				break;
			case 'page_top':
				
				// make sure the rlast revision is correct
				entity_tools_check_page_revision($entity, $old_owner_guid);
				
				// move all subpages by the same user
				entity_tools_update_subpages_owner_guid($entity, $old_owner_guid);
				break;
			case 'file':
				// move the physical file(s)
				entity_tools_move_file(get_entity($entity->getGUID()), $new_owner_guid);
				break;
		}
		
		// notify the new owner
		$new_owner = get_user($new_owner_guid);
		$old_owner = get_user($old_owner_guid);
		
		$subject = elgg_echo('entity_tools:notify:transfer_owner:subject', array(elgg_echo('item:' . $type . ':' . $subtype)));
		$msg = elgg_echo('entity_tools:notify:transfer_owner:message', array(
					$new_owner->name,
					$old_owner->name,
					elgg_echo('item:' . $type . ':' . $subtype),
					$entity->title,
					$entity->getURL()
		));
		
		notify_user($new_owner_guid, $old_owner_guid, $subject, $msg);
		
		// change metadata to new owner
		entity_tools_update_metadata_owner_guid($entity);
	}
	
	// check for container_guid
	if (($new_container_guid != $old_container_guid) && (!get_user($new_container_guid) || (get_user($new_container_guid) && ($new_container_guid == $entity->getOwnerGUID())))) {
		// the new container is not a user or the owner
		$entity->container_guid = $new_container_guid;
		
		$update_needed = true;
		
		if ($subtype == 'page_top') {
			// move all the subpages to the new container
			entity_tools_update_subpages_container_guid($entity);
		} elseif ($subtype == 'question') {
			entity_tools_update_answers_access($entity);
		}
		
		// check access_id for the new container
		entity_tools_update_access_id($entity, $old_container_guid);
	} elseif (($new_owner_guid != $old_owner_guid) && (get_user($old_container_guid) || get_user($new_container_guid))) {
		// moved the entity to a different user, so also change container to this user
		$entity->container_guid = $new_owner_guid;
		
		$update_needed = true;
		
		if ($subtype == 'page_top') {
			// move all the subpages to the new container
			entity_tools_update_subpages_container_guid($entity);
		}
		
		// check access_id for the new container
		entity_tools_update_access_id($entity, $old_container_guid);
	}
	
	// update the entity?
	if ($update_needed) {
		// save the entity
		$entity->save();
		
		// keep count
		$update_count++;
	}
}

if (empty($error_count)) {
	system_message(elgg_echo('entity_tools:action:update_entities:success', array($update_count)));
} else {
	register_error(elgg_echo('entity_tools:action:update_entities:error:not_all', array($update_count, $error_count)));
}

forward(REFERER);
