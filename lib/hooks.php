<?php

	function entity_tools_filter_menu_hook($hook, $type, $return_value, $params){
		$result = $return_value;
		
		if(elgg_in_context("entities")){
			$result = array();
			
			$page_owner = elgg_get_page_owner_entity();
			$priority = 10;
			$href_prefix = "entities/owner/" . $page_owner->username . "/";
			
			if($types = entity_tools_get_suported_entity_types()){
				foreach($types as $type => $subtypes){
					if(!empty($subtypes)){
						if(is_array($subtypes)){
							foreach($subtypes as $subtype){
								$result[] = ElggMenuItem::factory(array(
									"name" => $type . ":" . $subtype,
									"text" => elgg_echo("item:" . $type . ":" . $subtype),
									"href" => $href_prefix . $subtype,
									"priority" => $priority
								));
									
								$priority += 10;
							}
						} else {
							$result[] = ElggMenuItem::factory(array(
								"name" => $type . ":" . $subtypes,
								"text" => elgg_echo("item:" . $type . ":" . $subtypes),
								"href" => $href_prefix . $subtypes,
								"priority" => $priority
							));
							
							$priority += 10;
						}
					} else {
						$result[] = ElggMenuItem::factory(array(
							"name" => $type,
							"text" => elgg_echo("item:" . $type),
							"href" => $href_prefix . $type,
							"priority" => $priority
						));
						
						$priority += 10;
					}
				}
			}
		}
		
		return $result;
	}
	
	function entity_tools_filter_menu_prepare_hook($hook, $type, $return_value, $params){
		$result = $return_value;
	
		if(elgg_in_context("entities")){
			if(!empty($params) && is_array($params)){
				if(empty($params["selected_item"])){
					foreach($result as $section => $items){
						foreach($items as $index => $item){
							$item->setSelected(true);
							break(2);
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	function entity_tools_user_hover_menu_hook($hook, $type, $return_value, $params){
		$result = $return_value;
		
		if($loggedin_user = elgg_get_logged_in_user_entity()){
			if(!empty($params) && is_array($params)){
				if($user = elgg_extract("entity", $params)){
					// add the admin menu
					if($loggedin_user->isAdmin()){
						$result[] = ElggMenuItem::factory(array(
							"name" => "entity_tools:admin",
							"text" => elgg_echo("entity_tools:menu:user_hover"),
							"href" => "entities/owner/" . $user->username,
							"section" => "admin",
							"priority" => 500
						));
					}
				}
			}
		}
		
		return $result;
	}
	
	function entity_tools_owner_block_menu_hook($hook, $type, $return_value, $params){
		$result = $return_value;
		
		if($loggedin_user = elgg_get_logged_in_user_entity()){
			if(!empty($params) && is_array($params)){
				if($user = elgg_extract("entity", $params)){
					// depending on the plugin setting a user can go to the edit page
					if($loggedin_user->getGUID() == $user->getGUID()){
						if((entity_tools_get_edit_access_setting() == "user") || $user->isAdmin()){
							$result[] = ElggMenuItem::factory(array(
								"name" => "entity_tools:user",
								"text" => elgg_echo("entity_tools:menu:owner_block"),
								"href" => "entities/owner/" . $user->username,
								"context" => "profile",
								"priority" => 500
							));
						}
					}
				}
			}
		}
		
		return $result;
	}
	