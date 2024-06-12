<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_UserGroupsMultiple extends Field_Select {

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->slimselect = $config->slimselect ?? true;
		$this->multiple = $config->multiple ?? true;

		$this->select_options = DB::fetchall("SELECT id AS value, display AS text FROM `groups` ORDER BY display ASC");
	}
}