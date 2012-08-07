<?php

	$type = get_input("type");
	$subtype = get_input("subtype");
	$owner_guid = (int) get_input("owner_guid");
	
	$params = get_input("params");
	
	if(!empty($type) && !empty($subtype) && !empty($owner_guid) && !empty($params) && is_array($params)){
		$success_count = 0;
		
		foreach($params as $guid => $options){
			// get the entity, and check if we can update the entity
			if(($entity = get_entity($guid)) && $entity->canEdit()){
				//validate entity
				if(($entity->getType() == $type) && ($entity->getSubtype() == $subtype) && ($entity->getOwnerGUID() == $owner_guid)){
					$update_needed = false;
					
					// get new values
					$new_time_created = elgg_extract("time_created", $options, $entity->time_created);
					$new_owner_guid = elgg_extract("owner_guid", $options, $entity->getOwnerGUID());
					$new_container_guid = elgg_extract("container_guid", $options, $entity->getContainerGUID());
					
					// get current values
					$old_time_created = $entity->time_created;
					$old_owner_guid = $entity->getOwnerGUID();
					$old_container_guid = $entity->getContainerGUID();
					
					// check for time_created
					if($new_time_created != $old_time_created){
						$entity->time_created = $new_time_created;
						
						$update_needed = true;
					}
					
					// check for owner_guid
					if($new_owner_guid != $old_owner_guid){
						$entity->owner_guid = $new_owner_guid;
						
						$update_needed = true;
						
						switch($subtype){
							case "blog":
								// with blogs also transfer icon (if needed)
								entity_tools_move_blog_icon($entity, $old_owner_guid);
								break;
							case "page_top":
								entity_tools_update_subpages_owner_guid($entity, $old_owner_guid);
								break;
							case "file":
								// move the physical file(s)
								entity_tools_move_file(get_entity($entity->getGUID()), $new_owner_guid); 
								break;
						}
						
						// notify the new owner
						$new_owner = get_user($new_owner_guid);
						$old_owner = get_user($old_owner_guid);
						
						$subject = elgg_echo("entity_tools:notify:transfer_owner:subject", array(elgg_echo("item:" . $type . ":" . $subtype)));
						$msg = elgg_echo("entity_tools:notify:transfer_owner:message", array(
									$new_owner->name, 
									$old_owner->name, 
									elgg_echo("item:" . $type . ":" . $subtype),
									$entity->title,
									$entity->getURL()
						));
						
						notify_user($new_owner_guid, $old_owner_guid, $subject, $msg);
						
						// change metadata to new owner
						entity_tools_update_metadata_owner_guid($entity);
					}
					
					// check for container_guid
					if($new_container_guid != $old_container_guid){
						$entity->container_guid = $new_container_guid;
						
						$update_needed = true;
						
						if($subtype == "page_top"){
							// move all the subpages to the new container
							entity_tools_update_subpages_container_guid($entity);
						}
						
						// check access_id for the new container
						entity_tools_update_access_id($entity, $old_container_guid);
					} elseif(($old_container_guid == $old_owner_guid) && ($new_owner_guid != $old_owner_guid)){
						// moved the entity to a different user, so also change container to this user
						$entity->container_guid = $new_owner_guid;
						
						$update_needed = true;
						
						if($subtype == "page_top"){
							// move all the subpages to the new container
							entity_tools_update_subpages_container_guid($entity);
						}
						
						// check access_id for the new container
						entity_tools_update_access_id($entity, $old_container_guid);
					}
					
					// update the entity?
					if($update_needed){
						// save the entity
						$entity->save();
					}
					
					// keep count
					$success_count++;
				}
			}
		}
		
		if(count($params) == $success_count){
			system_message(elgg_echo("entity_tools:action:update_entities:success", array($success_count)));
		} else {
			register_error(elgg_echo("entity_tools:action:update_entities:error:not_all", array($success_count, count($params))));
		}
	} else {
		register_error(elgg_echo("entity_tools:action:update_entities:error:input"));
	}
	
	forward(REFERER);