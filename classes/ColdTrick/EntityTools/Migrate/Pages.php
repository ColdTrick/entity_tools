<?php

namespace ColdTrick\EntityTools\Migrate;

use ColdTrick\EntityTools\Migrate;

/**
 * Migrate configuration for page entities
 */
class Pages extends Migrate {
	
	/**
	 * @param \ElggPage $object the page to migrate
	 */
	public function __construct(\ElggPage $object) {
		parent::__construct($object);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canBackDate() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canChangeOwner() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function canChangeContainer() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
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
		
		/* @var $entity \ElggPage */
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
		
		/* @var $annotation \ElggAnnotation */
		$annotation = $annotations[0];
		
		// is the last revision owned by the old owner
		if ((int) $annotation->owner_guid !== $old_owner_guid) {
			return;
		}
		
		// update it to the new owner
		$annotation->owner_guid = $entity->owner_guid;
		
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
		
		/* @var $entity \ElggPage */
		$entity = $this->object;
		
		$old_owner_guid = (int) $this->original_attributes['owner_guid'];
		$new_owner_guid = (int) $new_owner_guid;
		if ($old_owner_guid === $new_owner_guid) {
			return;
		}
		
		$subpages = $this->getOwnedSubPages($entity);
		foreach ($subpages as $subpage) {
			$migrate = new static($subpage);
			$migrate->changeOwner($new_owner_guid);
			
			$subpage->save();
		}
	}
	
	/**
	 * Get all the subpages of a page owner by the original owner
	 *
	 * @param \ElggPage $entity the entity to get subpages for
	 *
	 * @return \ElggPage[]
	 */
	protected function getOwnedSubPages(\ElggPage $entity) {
		
		$old_owner_guid = (int) $this->original_attributes['owner_guid'];
		
		return elgg_call(ELGG_IGNORE_ACCESS, function() use ($entity, $old_owner_guid) {
			$result = [];
			
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => 'page',
				'limit' => false,
				'metadata_name_value_pairs' => [
					'name' => 'parent_guid',
					'value' => $entity->guid,
				],
				'batch' => true,
			]);
			
			/* @var $subpage \ElggPage */
			foreach ($batch as $subpage) {
				if ($subpage->owner_guid === $old_owner_guid) {
					$result[] = $subpage;
				}
				
				// add children
				$children = $this->getOwnedSubPages($subpage);
				if (!empty($children)) {
					$result = array_merge($result, $children);
				}
			}
			
			return $result;
		});
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
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($new_container_guid) {
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => 'page',
				'limit' => false,
				'metadata_name_value_pairs' => [
					'name' => 'parent_guid',
					'value' => $this->object->guid,
				],
				'batch' => true,
			]);
			
			/* @var $subpage \ElggPage */
			foreach ($batch as $subpage) {
				$migrate = new static($subpage);
				$migrate->changeContainer($new_container_guid);
				
				$subpage->save();
			}
		});
	}
}
