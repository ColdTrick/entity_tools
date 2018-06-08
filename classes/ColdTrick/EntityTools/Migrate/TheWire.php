<?php

namespace ColdTrick\EntityTools\Migrate;

use ColdTrick\EntityTools\Migrate;

class TheWire extends Migrate {
	
	/**
	 * @param \ElggWire $object the wire post to migrate
	 */
	public function __construct(\ElggWire $object) {
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
		return false;
	}
}
