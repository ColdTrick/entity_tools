<?php

	function entity_tools_page_handler($page){
		$result = false;
		$include_file = false;
		
		switch($page[0]){
			case "owner":
				if(isset($page[2])){
					set_input("subtype", $page[2]);
				}
				$include_file = dirname(dirname(__FILE__)) . "/pages/owner.php";
				break;
		}
		
		
		if(!empty($include_file)){
			$result = true;
			include($include_file);
		}
		
		return $result;
	}