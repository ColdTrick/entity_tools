<?php

namespace ColdTrick\EntityTools;

class MigrateTheWire extends Migrate {
	
	/**
	 * {@inheritDoc}
	 * @see \ColdTrick\EntityTools\Migrate::setSupportedOptions()
	 */
	protected function setSupportedOptions() {
		$this->supported_options = [
			'backdate' => true,
			'change_owner' => true,
			'change_container' => false,
		];
	}
}