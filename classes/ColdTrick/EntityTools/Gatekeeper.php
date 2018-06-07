<?php

namespace ColdTrick\EntityTools;

use Elgg\HttpException;
use Elgg\Request;
use Elgg\Router\Middleware\Gatekeeper as ElggGatekeeper;
use Elgg\EntityNotFoundException;
use Elgg\GatekeeperException;
use Elgg\EntityPermissionsException;

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
		if (!$page_owner instanceof \ElggUser && !$page_owner instanceof \ElggGroup) {
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
		}
	}
}
