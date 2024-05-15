<?php

namespace ColdTrick\EntityTools\Migrate;

use ColdTrick\EntityTools\Migrate;

/**
 * Migrate configuration for blog entities
 */
class Blog extends Migrate {
	
	/**
	 * @param \ElggBlog $object the blog to migrate
	 */
	public function __construct(\ElggBlog $object) {
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
