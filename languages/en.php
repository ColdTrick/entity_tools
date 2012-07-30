<?php

	$english = array(
	
		// general stuff
		'entity_tools:created' => "Created",
		'entity_tools:owner' => "Owner",
		'entity_tools:container' => "Group / User",
		
		'entity_tools:error:check_edit_access' => "You're not allowed to edit entities",
		'entity_tools:error:unsupported_subtype' => "You're trying to edit a unsupported entity subtype (%s)",
		
		// menu
		'entity_tools:menu:user_hover' => "Manage content",
		'entity_tools:menu:owner_block' => "Manage content",
		
		// page 
		'entity_tools:page:owner:title' => "Change %s's: %s",
		
		'entity_tools:forms:owner_listing:description' => "Here you can edit some of the properties of you content. The column Created allows you to edit when an item was created. The column Owner allows you to transfer an item to one of your friends. The last column (Group / User) shows where the content is located, eighter in a group or in your personal listing and it allows you to change this.",
		'entity_tools:forms:owner_listing:disclaimer' => "Please note that when assigning content to a group with a *, the current owner is not a member of that group and therefor can't access the content.",
		
		'entity_tools:listing:wrapper:page_top:header' => "Subpages*",
		'entity_tools:listing:wrapper:page_top:description' => "*: when transfering the page to a new owner, should all the subpages be transfered as well?",
		
		// settings
		'entity_tools:settings:edit_access' => "Who can manage content",
		'entity_tools:settings:edit_access:admin' => "Only site administrators",
		'entity_tools:settings:edit_access:user' => "All users",
		
		// actions
		// update entities
		'entity_tools:action:update_entities:error:input' => "Not all the required inputs were supplied, please try again",
		'entity_tools:action:update_entities:error:not_all' => "Not all entities could be updated (%s of %s succeeded)",
		'entity_tools:action:update_entities:success' => "Successfully edited %s entities",
		'' => "",
	
	);
	
	add_translation("en", $english);