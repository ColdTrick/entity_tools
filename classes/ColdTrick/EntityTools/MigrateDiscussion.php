<?php

namespace ColdTrick\EntityTools;

class MigrateDiscussion extends Migrate {
	
	protected function setSupportedOptions() {
		$this->supported_options = [
			'backdate' => true,
			'change_owner' => true,
			'change_container' => true,
		];
	}
}