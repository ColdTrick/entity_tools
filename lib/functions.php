<?php

	function entity_tools_get_suported_entity_types(){
		$result = array(
			"object" => array(
				"blog",
				"page_top"
			)
		);
		
		return $result;
	}
	
	function entity_tools_get_allowed_attibutes(){
		$result = array(
			"time_created",
			"owner_guid",
			"container_guid"
		);
		
		return $result;
	}
	
	function entity_tools_get_edit_access_setting(){
		static $plugin_setting;
		
		if(!isset($plugin_setting)){
			$plugin_setting = "admin";
				
			if($setting = elgg_get_plugin_setting("edit_access", "entity_tools")){
				$plugin_setting = $setting;
			}
		}
		
		return $plugin_setting;
	}
	
	function entity_tools_check_edit_access($forward = true){
		$result = false;
		
		$plugin_setting = entity_tools_get_edit_access_setting();
		
		switch($plugin_setting){
			case "user":
				if(($page_owner = elgg_get_page_owner_entity()) && ($user = elgg_get_logged_in_user_entity())){
					if(($page_owner->getGUID() == $user->getGUID()) || $user->isAdmin()){
						$result = true;
					}
				}
				break;
			case "admin":
			default:
				if(($user = elgg_get_logged_in_user_entity()) && $user->isAdmin()){
					$result = true;
				}
				break;
		}
		
		if($forward){
			if(!$result){
				register_error(elgg_echo("entity_tools:error:check_edit_access"));
				forward(REFERER);
			}
		} else {
			return $result;
		}
	}

	function entity_tools_get_owner_options(ElggEntity $entity){
		$result = array();
		
		if(!empty($entity)){
			$owner = $entity->getOwnerEntity();
			$user = elgg_get_logged_in_user_entity();
			
			// add the current owner
			$result[$owner->getGUID()] = $owner->name;
			
			// add the current user (if not the owner)
			if($owner->getGUID() != $user->getGUID()){
				$result[$user->getGUID()] = $user->name;
			}
			
			// add the friends of the current user
			if($friends = $user->getFriends("", false)){
				foreach($friends as $friend){
					if(!array_key_exists($friend->getGUID(), $result)){
						$result[$friend->getGUID()] = $friend->name;
					}
				}
			}
		}
		
		return $result;
	}
	
	function entity_tools_get_container_options(ElggEntity $entity){
		$result = array();
		
		if(!empty($entity)){
			$owner = $entity->getOwnerEntity();
			$container = $entity->getContainerEntity();
			$user = elgg_get_logged_in_user_entity();
			
			// add the current container
			$result[$container->getGUID()] = $container->name;
			
			// add the owner (if not the current container)
			if($container->getGUID() != $owner->getGUID()){
				$result[$owner->getGUID()] = $owner->name;
			}
			
			// add the groups of the current owner
			if($owner_groups = $owner->getGroups("", false)){
				foreach($owner_groups as $group){
					if(!array_key_exists($group->getGUID(), $result)){
						$result[$group->getGUID()] = $group->name;
					}
				}
			}
			
			// add the groups of the current user (if not the owner)
			if($owner->getGUID() != $user->getGUID()){
				if($user_groups = $user->getGroups("", false)){
					foreach($user_groups as $group){
						if(!array_key_exists($group->getGUID(), $result)){
							$postfix = "";
							if(!$group->isMember($user)){
								$postfix = "*";
							}
							
							$result[$group->getGUID()] = $group->name . $postfix;
						}
					}
				}
			}
			
		}
		
		return $result;
	}
	
	function entity_tools_get_subpages(ElggObject $object, $owner_guid = 0){
		$result = array();
		
		if(!empty($object) && (elgg_instanceof($object, "object", "page") || elgg_instanceof($object, "object", "page_top"))){
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
			
			if($subpages = elgg_get_entities_from_metadata($options)){
				
				foreach($subpages as $subpage){
					// do we need to filter on owner_guid
					if(!empty($owner_guid)){
						if($subpage->getOwnerGUID() == $owner_guid){
							$result[] = $subpage;
						}
					} else {
						$result[] = $subpage;
					}
					
					// get children
					if($children = entity_tools_get_subpages($subpage, $owner_guid)){
						$result = array_merge($result, $children);
					}
				}
			}
			
			// restore access
			elgg_set_ignore_access($old_ia);
		}
		
		return $result;
	}
	
	function entity_tools_update_metadata_owner_guid(ElggEntity $entity){
		$dbprefix = elgg_get_config("dbprefix");
			
		// set all metadata to the new owner
		$query = "UPDATE " . $dbprefix . "metadata";
		$query .= " SET owner_guid = " . $entity->getOwnerGUID();
		$query .= " WHERE entity_guid = " . $entity->getGUID();
			
		update_data($query);
	}
	
	function entity_tools_move_blog_icon(ElggObject $object, $old_owner_guid){
		
		// do we have a blog
		if(!empty($object) && elgg_instanceof($object, "object", "blog")){
			// does the blog have an icon and do we have old/new owner
			if(!empty($object->icontime) && !empty($old_owner_guid)){
				// check if we have users
				if(($new_owner = get_user($object->getOwnerGUID())) && ($old_owner = get_user($old_owner_guid))){
					// get iconsizes
					if($iconsizes = elgg_get_config("icon_sizes")){
						// prepare transfer
						$prefix = "blogs/" . $object->getGUID();
						
						// new location
						$new_fh = new ElggFile();
						$new_fh->owner_guid = $new_owner->getGUID();
						
						// old location
						$old_fh = new ElggFile();
						$old_fh->owner_guid = $old_owner->getGUID();
						
						// loop through icon sizes
						foreach($iconsizes as $icon_name => $icon_info){
							$old_fh->setFilename($prefix . $icon_name . ".jpg");
							
							// icon exists?
							if($old_fh->exists()){
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
					}
				}
			}
		}
	}
	
	function entity_tools_update_subpages_owner_guid(ElggObject $object, $old_owner_guid){
		
		if(!empty($object) && elgg_instanceof($object, "object", "page_top") && !empty($old_owner_guid)){
			
			if($subpages = entity_tools_get_subpages($object, $old_owner_guid)){
				foreach($subpages as $subpage){
					// set new owner_guid
					$subpage->owner_guid = $object->getOwnerGUID();
					
					// transfer all metadata to new owner
					entity_tools_update_metadata_owner_guid($subpage);
					
					// save entity
					$subpage->save();
				}
			}
		}
	}
	
	function entity_tools_update_subpages_container_guid(ElggObject $object){
		
		if(!empty($object) && elgg_instanceof($object, "object", "page_top")){
			if($subpages = entity_tools_get_subpages($object)){
				foreach($subpages as $subpage){
					// change container
					$subpage->container_guid = $object->getContainerGUID();
					
					// save entity
					$subpage->save();
				}
			}
		}
	}