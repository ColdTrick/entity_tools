<?php

namespace ColdTrick\EntityTools\Migrate;

use ColdTrick\EntityTools\Migrate;

class Blog extends Migrate {
	
	/**
	 * @param \ElggBlog $object the blog to migrate
	 */
	public function __construct(\ElggBlog $object) {
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
