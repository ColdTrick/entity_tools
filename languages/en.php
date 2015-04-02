<?php

return array(

	// general stuff
	'entity_tools:created' => "Created",
	'entity_tools:owner' => "Owner",
	'entity_tools:container' => "Group / User",
	
	'entity_tools:error:check_edit_access' => "You're not allowed to edit entities",
	'entity_tools:error:unsupported_subtype' => "You're trying to edit a unsupported entity subtype (%s)",
	
	// menu
	'entity_tools:menu:user_hover' => "Manage content",
	'entity_tools:menu:owner_block' => "Manage content",
	'entity_tools:menu:owner_block:group' => "Manage group content",
	
	// page
	'entity_tools:page:owner:title' => "Change %s's: %s",
	'entity_tools:page:container:title' => "Change %s's: %s",
	
	'entity_tools:forms:owner_listing:description' => "Here you can edit some of the properties of your content. The column Created allows you to edit when an item was created. The column Owner allows you to transfer an item to another user. The last column (Group / User) shows where the content is located, eighter in a group or in your personal listing and it allows you to change this.",
	'entity_tools:forms:owner_listing:disclaimer' => "Please note that when assigning content to a group with a *, the current owner is not a member of that group and therefor can't access the content.",
	
	'entity_tools:forms:container_listing:description' => "Here you can edit some of the properties of the group content. The column Created allows you to edit when an item was created. The column Owner allows you to transfer an item to another user. The last column (Group / User) shows where the content is located, eighter in a group or in your personal listing and it allows you to change this.",
	
	// settings
	'entity_tools:settings:edit_access' => "Who can manage content",
	'entity_tools:settings:edit_access:admin' => "Only site administrators",
	'entity_tools:settings:edit_access:group' => "Site administrators & group owners",
	'entity_tools:settings:edit_access:user' => "All users",
	
	// notifications
	'entity_tools:notify:transfer_owner:subject' => "A %s has been transfered to you",
	'entity_tools:notify:transfer_owner:message' => "Dear %s

%s transfered a %s with the title '%s' to you.

You can view it here: %s",
	
	// fancy dropdown labels
	'entity_tools:dropdown:label:current_value' => "Current value",
	'entity_tools:dropdown:label:owner' => "Current owner",
	'entity_tools:dropdown:label:myself' => "Myself",
	'entity_tools:dropdown:label:friends' => "My friends",
	'entity_tools:dropdown:label:owner_groups' => "Owner groups",
	'entity_tools:dropdown:label:my_groups' => "My groups",
	 
	// actions
	// update entities
	'entity_tools:action:update_entities:error:input' => "Not all the required inputs were supplied, please try again",
	'entity_tools:action:update_entities:error:not_all' => "Not all entities could be updated (%s succeeded, %s failed)",
	'entity_tools:action:update_entities:success' => "Successfully updated %s entities",
	
);