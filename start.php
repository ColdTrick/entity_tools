<?php

	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/hooks.php");
	require_once(dirname(__FILE__) . "/lib/page_handlers.php");
	
	function entity_tools_init(){
		// register page handler
		elgg_register_page_handler("entities", "entity_tools_page_handler");
		
		// register JS / CSS
		$base_url = elgg_get_site_url() . "mod/entity_tools/vendors/jquery/";
		elgg_register_js("jquery.timepicker", $base_url . "jquery-ui-timepicker-addon.js");
		elgg_register_js("jquery.slider", $base_url . "jquery-ui-slider.js");
		elgg_register_css("jquery.timepicker", $base_url . "jquery-ui-timepicker-addon.css");
		elgg_register_css("jquery.slider", $base_url . "jquery-ui-slider.css");
		
		// extend js
		elgg_extend_view("js/elgg", "entity_tools/js/site");
		elgg_extend_view("css/elgg", "entity_tools/css/site");
		
		// register plugin hooks
		elgg_register_plugin_hook_handler("register", "menu:filter", "entity_tools_filter_menu_hook");
		elgg_register_plugin_hook_handler("register", "menu:user_hover", "entity_tools_user_hover_menu_hook");
		elgg_register_plugin_hook_handler("register", "menu:owner_block", "entity_tools_owner_block_menu_hook");
		elgg_register_plugin_hook_handler("prepare", "menu:filter", "entity_tools_filter_menu_prepare_hook");
		
		// register actions
		elgg_register_action("entity_tools/update_entities", dirname(__FILE__) .  "/actions/update_entities.php");
	}
	
	// register default Elgg events
	elgg_register_event_handler("init", "system", "entity_tools_init");
	