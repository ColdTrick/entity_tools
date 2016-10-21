<?php

namespace ColdTrick\EntityTools;

class Migrate {
	
	protected $supported_options = [];
		
	protected $object;
	
	protected $original_attributes = [];
	
	public function __construct(\ElggObject $object) {
		
		$this->object = $object;
		$this->original_attributes = [
			'time_created' => $object->time_created,
			'owner_guid' => $object->owner_guid,
			'container_guid' => $object->container_guid,
		];
		
		$this->setSupportedOptions();
	}
	
	protected function setSupportedOptions() {
		$this->supported_options = [
			'backdate' => false,
			'change_owner' => false,
			'change_container' => false,
		];
	}
	
	public function canBackDate() {
		return $this->supported_options['backdate'];
	}
	
	public function backDate($new_time) {
		$this->object->time_created = $new_time;
	}
	
	public function canChangeOwner() {
		return $this->supported_options['change_owner'];
	}
	
	public function changeOwner($new_owner_guid) {
		if (!get_user($new_owner_guid)) {
			return;
		}
		$this->object->owner_guid = $new_owner_guid;
		$this->updateMetadataOwnerGUID();
		
		// check access_id for the new container
		$this->updateAccessID();
	}
	
	public function canChangeContainer() {
		return $this->supported_options['change_container'];
	}
	
	public function changeContainer($new_container_guid) {
		$this->object->container_guid = $new_container_guid;
		
		// check access_id for the new container
		$this->updateAccessID();
	}
	
	/**
	 * Update metadata to owner guid of entity
	 *
	 * @return bool
	 */
	public function updateMetadataOwnerGUID() {
		
		$dbprefix = elgg_get_config('dbprefix');
			
		// set all metadata to the new owner
		$query = "UPDATE {$dbprefix}metadata";
		$query .= " SET owner_guid = {$this->object->owner_guid}";
		$query .= " WHERE entity_guid = {$this->object->guid}";
			
		return update_data($query);
	}
	
	/**
	 * Update access_id of the entity
	 *
	 * @return void
	 */
	public function updateAccessID() {
		
		$access_id = (int) $this->object->access_id;
		
		// ignore access restrictions
		$ia = elgg_set_ignore_access(true);
		
		$old_container = get_entity($this->original_attributes['container_guid']);
		$new_container = $this->object->getContainerEntity();
		
		// check the old container to check access_id
		if ($old_container instanceof \ElggGroup) {
			// from a group
			if ($access_id === (int) $old_container->group_acl) {
				// with group access
				if ($new_container instanceof \ElggGroup) {
					// to a new group
					// change access to the new group
					$this->object->access_id = (int) $new_container->group_acl;
				} else {
					// new container is a user, so make the entity private
					$this->object->access_id = ACCESS_PRIVATE;
				}
			}
		} else {
			// from a user
			$acls = [];
			
			$user_access_collections = get_user_access_collections($old_container_guid);
			if (!empty($user_access_collections)) {
				foreach ($user_access_collections as $acl) {
					$acls[] = (int) $acl->id;
				}
			}
			
			if (in_array($access_id, $acls)) {
				// access was a private access collection
				if ($new_container instanceof \ElggGroup) {
					// moved to a group
					// change access to the group
					$this->object->access_id = (int) $new_container->group_acl;
				} else {
					// moved to different user
					// change access to private
					$this->object->access_id = ACCESS_PRIVATE;
				}
			}
		}
		
		// restore access restrictions
		elgg_set_ignore_access($ia);
	}
		
}