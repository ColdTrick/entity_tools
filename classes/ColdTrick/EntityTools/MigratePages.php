<?php

namespace ColdTrick\EntityTools;

class MigratePages extends Migrate {
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::setSupportedOptions()
	 */
	protected function setSupportedOptions() {
		$this->supported_options = [
			'backdate' => true,
			'change_owner' => true,
			'change_container' => true,
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::changeOwner()
	 */
	public function changeOwner($new_owner_guid) {
		
		// do all the default stuff
		parent::changeOwner($new_owner_guid);
		
		// move last revision
		$this->moveLastRevision();
		
		// move subpages
		$this->updateSubpagesOwnerGUID($new_owner_guid);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::changeContainer()
	 */
	public function changeContainer($new_container_guid) {
		
		// do all the default stuff
		parent::changeContainer($new_container_guid);
		
		// move all subpages to the new container
		$this->moveSubpages($new_container_guid);
	}
	
	/**
	 * Move the last revision from the old owner to the new owner
	 *
	 * @return void
	 */
	protected function moveLastRevision() {
		
		$entity = $this->object;
		$old_owner_guid = (int) $this->original_attributes['owner_guid'];
		
		// get the last revision
		$annotations = $entity->getAnnotations([
			'annotation_name' => 'page',
			'limit' => 1,
			'reverse_order_by' => true
		]);
		if (empty($annotations)) {
			return;
		}
		
		$annotation = $annotations[0];
		
		// is the last revision owned by the old owner
		if ((int) $annotation->getOwnerGUID() !== $old_owner_guid) {
			return;
		}
		
		// update it to the new owner
		$annotation->owner_guid = $entity->getOwnerGUID();
		
		$annotation->save();
	}
	
	/**
	 * Update all subpages belonging to the same owner_guid to the new owner_guid
	 *
	 * @param int $new_owner_guid the new owner_guid
	 *
	 * @return void
	 */
	protected function updateSubpagesOwnerGUID($new_owner_guid) {
		
		$entity = $this->object;
		if (!elgg_instanceof($entity, 'object', 'page_top')) {
			return;
		}
		
		$old_owner_guid = (int) $this->original_attributes['owner_guid'];
		$new_owner_guid = (int) $new_owner_guid;
		if ($old_owner_guid === $new_owner_guid) {
			return;
		}
		
		$subpages = $this->getOwnedSubPages($entity);
		foreach ($subpages as $subpage) {
			
			$migrate = new MigratePages($subpage);
			$migrate->changeOwner($new_owner_guid);
			
			$subpage->save();
		}
	}
	
	/**
	 * Get all the subpages of a page owner by the original owner
	 *
	 * @param \ElggObject $entity the entity to get subpages for
	 *
	 * @return \ElggObject[]
	 */
	protected function getOwnedSubPages(\ElggObject $entity) {
		
		$result = [];
		
		$old_owner_guid = (int) $this->original_attributes['owner_guid'];
		
		// ignore access for this part
		$ia = elgg_set_ignore_access(true);
		
		$batch = new \ElggBatch('elgg_get_entities_from_metadata', [
			'type' => 'object',
			'subtype' => 'page',
			'limit' => false,
			'metadata_name_value_pairs' => [
				'name' => 'parent_guid',
				'value' => $entity->getGUID(),
			],
		]);
		/* @var $subpage \ElggObject */
		foreach ($batch as $subpage) {
			
			if ($subpage->owner_guid === (int) $old_owner_guid) {
				$result[] = $subpage;
			}
			
			// add children
			$children = $this->getOwnedSubPages($subpage);
			if (!empty($children)) {
				$result = array_merge($result, $children);
			}
		}
		
		// restore access
		elgg_set_ignore_access($ia);
		
		return $result;
	}
	
	/**
	 * Move all the children of the entity to the new container_guid
	 *
	 * @param int $new_container_guid the new container_guid
	 *
	 * @return void
	 */
	protected function moveSubpages($new_container_guid) {
		
		// ignore access for this part
		$ia = elgg_set_ignore_access(true);
		
		$batch = new \ElggBatch('elgg_get_entities_from_metadata', [
			'type' => 'object',
			'subtype' => 'page',
			'limit' => false,
			'metadata_name_value_pairs' => [
				'name' => 'parent_guid',
				'value' => $this->object->getGUID(),
			],
		]);
		/* @var $subpage \ElggObject */
		foreach ($batch as $subpage) {
			
			$migrate = new MigratePages($subpage);
			$migrate->changeContainer($new_container_guid);
			
			$subpage->save();
		}
		
		// restore access
		elgg_set_ignore_access($ia);
	}
}
