<?php

namespace ColdTrick\EntityTools\Migrate;

use ColdTrick\EntityTools\Migrate;

/**
 * Migrate configuration for discussion entities
 */
class Discussion extends Migrate {
	
	/**
	 * @param \ElggDiscussion $object the discussion to migrate
	 */
	public function __construct(\ElggDiscussion $object) {
		parent::__construct($object);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canBackDate(): bool {
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canChangeOwner(): bool {
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function canChangeContainer(): bool {
		return true;
	}
}
