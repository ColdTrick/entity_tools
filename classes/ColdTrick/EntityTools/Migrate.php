<?php

namespace ColdTrick\EntityTools;

abstract class Migrate {
	
	/**
	 * @var \ElggObject the entity being migrated
	 */
	protected $object;
	
	/**
	 * @var array some of the original attributes of the entity before being changed
	 */
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
	abstract public function canBackDate();
	
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
	abstract public function canChangeOwner();
	
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
		
		// check access_id for the new container
		$this->updateAccessID();
	}
	
	/**
	 * Can the container be changed
	 *
	 * @return bool
	 */
	abstract public function canChangeContainer();
	
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
	 * Update access_id of the entity
	 *
	 * @return void
	 */
	public function updateAccessID() {
		
		$old_access_id = (int) $this->object->access_id;
		$new_access_id = ACCESS_PRIVATE;
		
		// ignore access restrictions
		$ia = elgg_set_ignore_access(true);
		
		$old_container_guid = (int) $this->original_attributes['container_guid'];
		$old_container = get_entity($old_container_guid);
		$new_container = $this->object->getContainerEntity();
		
		// check the old container to check access_id
		if ($old_container instanceof \ElggGroup) {
			// from a group
			$acl = $old_container->getOwnedAccessCollection('group_acl');
			if ($acl !== false && $old_access_id === (int) $acl->id) {
				// with group access
				if ($new_container instanceof \ElggGroup) {
					// to a new group
					// change access to the new group
					$new_acl = $new_container->getOwnedAccessCollection('group_acl');
					if ($new_acl !== false) {
						$new_access_id = (int) $new_acl->id;
					}
				}
			}
		} else {
			// from a user
			$acls = [];
			
			$user_access_collections = $old_container->getOwnedAccessCollections();
			if (!empty($user_access_collections)) {
				foreach ($user_access_collections as $acl) {
					$acls[] = (int) $acl->id;
				}
			}
			
			if (in_array($old_access_id, $acls)) {
				// access was a private access collection
				if ($new_container instanceof \ElggGroup) {
					// moved to a group
					// change access to the group
					$new_acl = $new_container->getOwnedAccessCollection('group_acl');
					if ($new_acl !== false) {
						$new_access_id = (int) $new_acl->id;
					}
				}
			}
		}
		
		// did the access_id change
		if ($old_access_id !== $new_access_id) {
			// store new value
			$this->object->access_id = $new_access_id;
		}
		
		// restore access restrictions
		elgg_set_ignore_access($ia);
	}
}
