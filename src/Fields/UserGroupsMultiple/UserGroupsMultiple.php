<?php
namespace HoltBosse\Alba\Fields\UserGroupsMultiple;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;

class UserGroupsMultiple extends Select {

	public function loadFromConfig(object $config): self {
		//set this beforehand due to selects loadfromconfig setting a filter dependant on the multiple property
		if (!property_exists($config, 'multiple')) {
			/** @phpstan-ignore-next-line */
			$config->multiple = true;
		}

		parent::loadFromConfig($config);

		$this->slimselect = $config->slimselect ?? true;

		$this->select_options = DB::fetchAll("SELECT id AS value, display AS text FROM `groups` ORDER BY display ASC");

		return $this;
	}
}