<?php

namespace ColdTrick\EntityTools;

use Elgg\Exceptions\HttpException;
use Elgg\Exceptions\Http\EntityNotFoundException;
use Elgg\Exceptions\Http\GatekeeperException;
use Elgg\Exceptions\Http\EntityPermissionsException;
use Elgg\Request;
use Elgg\Router\Middleware\Gatekeeper as ElggGatekeeper;

class Gatekeeper extends ElggGatekeeper {
	
	/**
	 * Entity tools gatekeeper
	 *
	 * @param Request $request Request
	 *
	 * @return void
	 * @throws HttpException
	 */
	public function __invoke(Request $request) {
		
		parent::__invoke($request);
		
		if ($request->elgg()->session->isAdminLoggedIn()) {
			// admins are always allowed
			return;
		}
		
		$plugin_setting = elgg_get_plugin_setting('edit_access', 'entity_tools');
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggUser && !$page_owner instanceof \ElggGroup && !$page_owner instanceof \ElggSite) {
			// can only handler user/group pages
			throw new EntityNotFoundException();
		}
		
		switch ($plugin_setting) {
			case 'group':
				// only group admins (so need to be in a group)
				if (!$page_owner instanceof \ElggGroup) {
					throw new GatekeeperException();
				}
			case 'user':
				if (!$page_owner->canEdit()) {
					throw new EntityPermissionsException();
				}
				break;
			default:
				if (!elgg_is_admin_logged_in()) {
					throw new EntityPermissionsException();
				}
				break;
		}
	}
}
