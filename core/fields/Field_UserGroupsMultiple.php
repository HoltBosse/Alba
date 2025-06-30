<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_UserGroupsMultiple extends Field_Select {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->slimselect = $config->slimselect ?? true;
		$this->multiple = $config->multiple ?? true;

		$this->select_options = DB::fetchAll("SELECT id AS value, display AS text FROM `groups` ORDER BY display ASC");
	}
}