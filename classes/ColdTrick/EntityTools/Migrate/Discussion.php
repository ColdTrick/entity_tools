<?php

namespace ColdTrick\EntityTools\Migrate;

use ColdTrick\EntityTools\Migrate;

class Discussion extends Migrate {
	
	/**
	 * @param \ElggDiscussion $object the discussion to migrate
	 */
	public function __construct(\ElggDiscussion $object) {
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
}
