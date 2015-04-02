<?php
/**
 * All helper functions are bundled here
 */

/**
 * Get the currently supported type/subtypes
 *
 * @return array
 */
function entity_tools_get_suported_entity_types() {
	static $result;
	
	if (!isset($result)) {
		$result = array();
		
		if (elgg_is_active_plugin("blog")) {
			$result["object"][] = "blog";
		}
		
		if (elgg_is_active_plugin("pages")) {
			$result["object"][] = "page_top";
		}
		
		if (elgg_is_active_plugin("file")) {
			$result["object"][] = "file";
		}
		
		if (elgg_is_active_plugin("questions")) {
			$result["object"][] = "question";
		}
	}
	
	return $result;
}

/**
 * Get the attributes allowed to change
 *
 * @return array
 */
function entity_tools_get_allowed_attibutes() {
	return array(
		"time_created",
		"owner_guid",
		"container_guid"
	);
}

/**
 * Get the plugin setting for who is allowed to edit
 *
 * @return string
 */
function entity_tools_get_edit_access_setting() {
	static $plugin_setting;
	
	if (!isset($plugin_setting)) {
		$plugin_setting = "admin";
			
		if ($setting = elgg_get_plugin_setting("edit_access", "entity_tools")) {
			$plugin_setting = $setting;
		}
	}
	
	return $plugin_setting;
}

/**
 * Check if the current user is allowed to edit the page owner
 *
 * @param bool $forward (optional) forward on error (default: true)
 *
 * @return void|bool
 */
function entity_tools_check_edit_access($forward = true) {
	$result = false;
	
	$forward = (bool) $forward;
	
	$user = elgg_get_logged_in_user_entity();
	if (!empty($user)) {
		if ($user->isAdmin()) {
			// admins are always allowed
			$result = true;
		} else {
			// check plugin setting for normal user
			$plugin_setting = entity_tools_get_edit_access_setting();
			$page_owner = elgg_get_page_owner_entity();
			
			switch ($plugin_setting) {
				case "user":
					if (empty($page_owner) || !elgg_instanceof($page_owner, "user")) {
						break;
					}
					
					if ($page_owner->getGUID() == $user->getGUID()) {
						$result = true;
					}
					break;
				case "group":
					if (empty($page_owner) || !elgg_instanceof($page_owner, "group")) {
						break;
					}
					
					if ($page_owner->canEdit()) {
						$result = true;
					}
					break;
			}
		}
	}
	
	if ($forward && !$result) {
		register_error(elgg_echo("entity_tools:error:check_edit_access"));
		forward(REFERER);
	}
	
	return $result;
}

/**
 * Get the container transfer options
 *
 * @param ElggEntity $entity the entity to check container for
 *
 * @return array
 */
function entity_tools_get_container_options(ElggEntity $entity) {
	$result = array();
	
	if (empty($entity) || !elgg_instanceof($entity)) {
		return $result;
	}
	
	$page_owner = elgg_get_page_owner_entity();
	$owner = $entity->getOwnerEntity();
	$container = $entity->getContainerEntity();
	$user = elgg_get_logged_in_user_entity();
	
	// log unique guids
	$temp_array = array($container->getGUID());
	
	// add the current container
	$result[elgg_echo("entity_tools:dropdown:label:current_value")] = array($container->getGUID() => $container->name);
	
	// add the owner (if not the current container)
	if ($container->getGUID() != $owner->getGUID()) {
		$result[elgg_echo("entity_tools:dropdown:label:owner")] = array($owner->getGUID() => $owner->name);
		
		// add the guid to the filter
		$temp_array[] = $owner->getGUID();
	}
	
	// build default group options
	$dbprefix = elgg_get_config("dbprefix");
	$group_options = array(
		"limit" => false,
		"joins" => array("JOIN {$dbprefix}groups_entity ge ON e.guid = ge.guid"),
		"order_by" => "ge.name"
	);
	
	if (elgg_instanceof($page_owner, 'user')) {
		// add the groups of the current owner
		$owner_groups = $owner->getGroups($group_options);
		if (!empty($owner_groups)) {
			if ($owner->getGUID() == $user->getGUID()) {
				$label = elgg_echo("entity_tools:dropdown:label:my_groups");
			} else {
				$label = elgg_echo("entity_tools:dropdown:label:owner_groups");
			}
			
			// add label
			$result[$label] = array();
			
			foreach ($owner_groups as $group) {
				// check if group not already proccessed
				if (in_array($group->getGUID(), $temp_array)) {
					continue;
				}
				
				// add group
				$result[$label][$group->getGUID()] = $group->name;
				
				// add the guid to the filter
				$temp_array[] = $group->getGUID();
			}
			
			// check for empty label
			if (empty($result[$label])) {
				unset($result[$label]);
			}
		}
	}
	
	// add the groups of the current user (if not the owner)
	if ($page_owner->getGUID() != $user->getGUID()) {
		$user_groups = $user->getGroups($group_options);
		if (!empty($user_groups)) {
			// add label
			$result[elgg_echo("entity_tools:dropdown:label:my_groups")] = array();
			
			foreach ($user_groups as $group) {
				if (in_array($group->getGUID(), $temp_array)) {
					continue;
				}
				
				//
				$postfix = "";
				if (!$group->isMember($owner)) {
					$postfix = "*";
				}
				
				// add group
				$result[elgg_echo("entity_tools:dropdown:label:my_groups")][$group->getGUID()] = $group->name . $postfix;
				
				// add the guid to the filter
				$temp_array[] = $group->getGUID();
			}
			
			// check for empty label
			if (empty($result[elgg_echo("entity_tools:dropdown:label:my_groups")])) {
				unset($result[elgg_echo("entity_tools:dropdown:label:my_groups")]);
			}
		}
	}
	
	return $result;
}

/**
 * Get the subpages of a page_top
 *
 * @param ElggObject $object the page_top to check
 * @param int        $owner_guid (optional) limit to an owner
 *
 * @return array
 */
function entity_tools_get_subpages(ElggObject $object, $owner_guid = 0) {
	$result = array();
	
	if (empty($object) || (!elgg_instanceof($object, "object", "page") && !elgg_instanceof($object, "object", "page_top"))) {
		return $result;
	}
	
	$owner_guid = sanitize_int($owner_guid, false);
	
	// make sure we can get every entity
	$old_ia = elgg_set_ignore_access(true);
	
	// prepare options
	$options = array(
		"type" => "object",
		"subtype" => "page",
		"limit" => false,
		"metadata_name_value_pairs" => array(
			"name" => "parent_guid",
			"value" => $object->getGUID()
		)
	);
	
	$subpages = new ElggBatch("elgg_get_entities_from_metadata", $options);
	foreach ($subpages as $subpage) {
		// do we need to filter on owner_guid
		if (!empty($owner_guid)) {
			if ($subpage->getOwnerGUID() == $owner_guid) {
				$result[] = $subpage;
			}
		} else {
			$result[] = $subpage;
		}
		
		// get children
		$children = entity_tools_get_subpages($subpage, $owner_guid);
		if (!empty($children)) {
			$result = array_merge($result, $children);
		}
	}
	
	// restore access
	elgg_set_ignore_access($old_ia);
	
	return $result;
}

/**
 * Update metadata to new owner_guid
 *
 * @param ElggEntity $entity the entity to update metadata for
 *
 * @return bool
 */
function entity_tools_update_metadata_owner_guid(ElggEntity $entity) {
	
	if (empty($entity) || !elgg_instanceof($entity)) {
		return false;
	}
	
	$dbprefix = elgg_get_config("dbprefix");
		
	// set all metadata to the new owner
	$query = "UPDATE {$dbprefix}metadata";
	$query .= " SET owner_guid = {$entity->getOwnerGUID()}";
	$query .= " WHERE entity_guid = {$entity->getGUID()}";
		
	return update_data($query);
}

/**
 * Move the blog_tools provided blog icon
 *
 * @param ElggObject $object         the blog to move the icon for
 * @param int        $old_owner_guid the old owner_guid to find the blog icon
 *
 * @return void
 */
function entity_tools_move_blog_icon(ElggObject $object, $old_owner_guid) {
	
	// do we have a blog
	if (empty($object) || !elgg_instanceof($object, "object", "blog")) {
		return;
	}
	
	$old_owner_guid = sanitize_int($old_owner_guid, false);
	
	// does the blog have an icon and do we have old/new owner
	if (empty($object->icontime) || empty($old_owner_guid)) {
		return;
	}
	
	// check if we have users
	$new_owner = get_user($object->getOwnerGUID());
	$old_owner = get_user($old_owner_guid);
	if (empty($new_owner) || empty($old_owner)) {
		return;
	}
	// get iconsizes
	$iconsizes = elgg_get_config("icon_sizes");
	if (empty($iconsizes)) {
		return;
	}
	
	// prepare transfer
	$prefix = "blogs/" . $object->getGUID();
	
	// new location
	$new_fh = new ElggFile();
	$new_fh->owner_guid = $new_owner->getGUID();
	
	// old location
	$old_fh = new ElggFile();
	$old_fh->owner_guid = $old_owner->getGUID();
	
	// loop through icon sizes
	foreach ($iconsizes as $icon_name => $icon_info) {
		$old_fh->setFilename($prefix . $icon_name . ".jpg");
		
		// icon exists?
		if (!$old_fh->exists()) {
			continue;
		}
		
		$new_fh->setFileName($prefix . $icon_name . ".jpg");
		
		// open handlers
		$old_fh->open("read");
		$new_fh->open("write");
		
		// transfer
		$new_fh->write($old_fh->grabFile());
		
		// close handlers
		$old_fh->close();
		$new_fh->close();
		
		// remove old icon
		$old_fh->delete();
	}
}

/**
 * Update the owner of all sub pages
 *
 * @param ElggObject $object         the page_top to update all subpages for
 * @param int        $old_owner_guid the old owner_guid
 *
 * @return void
 */
function entity_tools_update_subpages_owner_guid(ElggObject $object, $old_owner_guid) {
	
	if (empty($object) || !elgg_instanceof($object, "object", "page_top")) {
		return;
	}
	
	$old_owner_guid = sanitize_int($old_owner_guid, false);
	if (empty($old_owner_guid)) {
		return;
	}
		
	$subpages = entity_tools_get_subpages($object, $old_owner_guid);
	if (empty($subpages)) {
		return;
	}
	
	foreach ($subpages as $subpage) {
		// set new owner_guid
		$subpage->owner_guid = $object->getOwnerGUID();
		
		// transfer all metadata to new owner
		entity_tools_update_metadata_owner_guid($subpage);
		
		// check revisions
		entity_tools_check_page_revision($subpage, $old_owner_guid);
		
		// save entity
		$subpage->save();
	}
}

/**
 * Update the container of all sub pages
 *
 * @param ElggObject $object         the page_top to update all subpages for
 *
 * @return void
 */
function entity_tools_update_subpages_container_guid(ElggObject $object) {
	
	if (empty($object) || !elgg_instanceof($object, "object", "page_top")) {
		return;
	}
	
	$subpages = entity_tools_get_subpages($object);
	if (empty($subpages)) {
		return;
	}
	
	foreach ($subpages as $subpage) {
		$old_container_guid = $subpage->getContainerGUID();
		
		// change container
		$subpage->container_guid = $object->getContainerGUID();
		
		// check if the access need to be updated
		entity_tools_update_access_id($subpage, $old_container_guid);
		
		// save entity
		$subpage->save();
	}
}

/**
 * Update access_id of the entity
 *
 * @param ElggEntity $entity             the entity to update access_id for
 * @param int        $old_container_guid the old container_guid
 *
 * @return void
 */
function entity_tools_update_access_id(ElggEntity &$entity, $old_container_guid) {
	
	if (empty($entity) || !elgg_instanceof($entity)) {
		return;
	}
	
	$old_container_guid = sanitize_int($old_container_guid, false);
	if (empty($old_container_guid)) {
		return;
	}
	
	$access_id = (int) $entity->access_id;
	
	// ignore access restrictions
	$ia = elgg_set_ignore_access(true);
	
	$old_container = get_entity($old_container_guid);
	$new_container = $entity->getContainerEntity();
	
	// check the old container to check access_id
	if (elgg_instanceof($old_container, "group")) {
		// from a group
		if ($access_id === (int) $old_container->group_acl) {
			// with group access
			if (elgg_instanceof($new_container, "group")) {
				// to a new group
				// change access to the new group
				$entity->access_id = (int) $new_container->group_acl;
			} else {
				// new container is a user, so make the entity private
				$entity->access_id = ACCESS_PRIVATE;
			}
		}
	} else {
		// from a user
		$acls = array();
		
		$user_access_collections = get_user_access_collections($old_container_guid);
		if (!empty($user_access_collections)) {
			foreach ($user_access_collections as $acl) {
				$acls[] = (int) $acl->id;
			}
		}
		
		if (in_array($access_id, $acls)) {
			// access was a private access collection
			if (elgg_instanceof($new_container, "group")) {
				// moved to a group
				// change access to the group
				$entity->access_id = (int) $new_container->group_acl;
			} else {
				// moved to different user
				// change access to private
				$entity->access_id = ACCESS_PRIVATE;
			}
		}
	}
	
	// save new access
	$entity->save();
	
	// restore access restrictions
	elgg_set_ignore_access($ia);
}

/**
 * Move a file to a new owner
 *
 * @param ElggFile $old_entity     the file to move
 * @param int      $new_owner_guid the new owner_guid
 *
 * @return void
 */
function entity_tools_move_file(ElggFile $old_entity, $new_owner_guid) {
	
	if (empty($old_entity) || !elgg_instanceof($old_entity, "object", "file")) {
		return;
	}
	
	$new_owner_guid = sanitize_int($new_owner_guid, false);
	if (empty($new_owner_guid)) {
		return;
	}
	
	// make temp file handlers
	$tmp_fh = new ElggFile();
	$tmp_fh->owner_guid = $new_owner_guid;
	
	$tmp_fh_old = new ElggFile();
	$tmp_fh_old->owner_guid = $old_entity->getOwnerGUID();
	
	// check main file
	$tmp_fh_old->setFilename($old_entity->getFilename());
	
	// move the main file
	if ($tmp_fh_old->exists()) {
		$tmp_fh->setFilename($tmp_fh_old->getFilename());
		
		// copy the main file to the new location
		$tmp_fh->open("write");
		$tmp_fh->write($tmp_fh_old->grabFile());
		$tmp_fh->close();
		
		// remove old file
		$tmp_fh_old->delete();
	}
	
	// check for thumbs and move
	$thumbs = array("thumbnail", "smallthumb", "largethumb");
	foreach ($thumbs as $thumb) {
		$filename = $old_entity->$thumb;
		if (empty($filename)) {
			continue;
		}
		
		$tmp_fh_old->setFilename($filename);
		if (!$tmp_fh_old->exists()) {
			continue;
		}
		
		$tmp_fh->setFilename($filename);
		
		// copy the thumb to the new location
		$tmp_fh->open("write");
		$tmp_fh->write($tmp_fh_old->grabFile());
		$tmp_fh->close();
		
		// remove old thumb
		$tmp_fh_old->delete();
	}
}

/**
 * Check the last page revision for the correct owner
 *
 * @param ElggObject $entity         the page to check
 * @param int        $old_owner_guid the old owner_guid
 *
 * @return void
 */
function entity_tools_check_page_revision(ElggObject $entity, $old_owner_guid) {
	
	if (empty($entity) || (!elgg_instanceof($entity, "object", "page_top") && !elgg_instanceof($entity, "object", "page"))) {
		return;
	}
	
	$old_owner_guid = sanitize_int($old_owner_guid, false);
	if (empty($old_owner_guid)) {
		return;
	}
	
	$annotations = $entity->getAnnotations(array(
		"annotation_name" => "page",
		"limit" => 1,
		"reverse_order_by" => true
	));
	if (empty($annotations)) {
		return;
	}
	
	$annotation = $annotations[0];
	
	// is the last revision owned by the old owner
	if ($annotation->getOwnerGUID() != $old_owner_guid) {
		return;
	}
	
	// update it to the new owner
	$annotation->owner_guid = $entity->getOwnerGUID();
	
	$annotation->save();
}

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
