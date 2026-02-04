<?php
namespace HoltBosse\Alba\Fields\UserGroupsMultiple;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;

class UserGroupsMultiple extends Select {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->slimselect = $config->slimselect ?? true;
		$this->multiple = $config->multiple ?? true;

		$this->select_options = DB::fetchAll("SELECT id AS value, display AS text FROM `groups` ORDER BY display ASC");

		return $this;
	}
}