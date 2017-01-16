<?php

namespace ColdTrick\EntityTools;

class Migrate {
	
	protected $supported_options = [];
		
	protected $object;
	
	protected $original_attributes = [];
	
	/**
	 * Create a migration helper object
	 *
	 * @param \ElggObject $object the object to migrate
	 */
	public function __construct(\ElggObject $object) {
		
		$this->object = $object;
		$this->original_attributes = [
			'time_created' => $object->time_created,
			'owner_guid' => $object->owner_guid,
			'container_guid' => $object->container_guid,
		];
		
		$this->setSupportedOptions();
	}
	
	/**
	 * Set the supported options for this migration
	 *
	 * @return void
	 */
	protected function setSupportedOptions() {
		$this->supported_options = [
			'backdate' => false,
			'change_owner' => false,
			'change_container' => false,
		];
	}
	
	/**
	 * Return the object used in the migrate
	 *
	 * @return \ElggObject
	 */
	public function getObject() {
		return $this->object;
	}
	
	/**
	 * Can the entity be backdated
	 *
	 * @return bool
	 */
	public function canBackDate() {
		return (bool) elgg_extract('backdate', $this->supported_options, false);
	}
	
	/**
	 * Backdate the entity
	 *
	 * @param int $new_time the new time_created
	 *
	 * @retrun void
	 */
	public function backDate($new_time) {
		$this->object->time_created = $new_time;
	}
	
	/**
	 * Can the owner be changed
	 *
	 * @return bool
	 */
	public function canChangeOwner() {
		return (bool) elgg_extract('change_owner', $this->supported_options, false);
	}
	
	/**
	 * Changed the owner_guid of the entity
	 *
	 * @param int $new_owner_guid the new owner_guid
	 *
	 * @return void
	 */
	public function changeOwner($new_owner_guid) {
		
		if (!get_user($new_owner_guid)) {
			return;
		}
		
		$this->object->owner_guid = $new_owner_guid;
		
		// update the metadata
		$this->updateMetadataOwnerGUID();
		
		// check access_id for the new container
		$this->updateAccessID();
	}
	
	/**
	 * Can the container be changed
	 *
	 * @return bool
	 */
	public function canChangeContainer() {
		return (bool) elgg_extract('change_container', $this->supported_options, false);
	}
	
	/**
	 * Change the container_guid of the entity
	 *
	 * @param int $new_container_guid the new container_guid
	 *
	 * @return void
	 */
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
		
		$old_container_guid = (int) $this->original_attributes['container_guid'];
		$old_container = get_entity($old_container_guid);
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
