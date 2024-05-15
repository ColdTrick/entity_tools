<?php

namespace ColdTrick\EntityTools\Migrate;

use ColdTrick\EntityTools\Migrate;

/**
 * Migrate configuration for thewire entities
 */
class TheWire extends Migrate {
	
	/**
	 * @param \ElggWire $object the wire post to migrate
	 */
	public function __construct(\ElggWire $object) {
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
		return false;
	}
}
