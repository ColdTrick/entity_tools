<?php

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
