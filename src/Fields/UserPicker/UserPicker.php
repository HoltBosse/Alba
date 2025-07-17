<?php
namespace HoltBosse\Alba\Fields\UserPicker;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;

class UserPicker extends Select {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->slimselect = $config->slimselect ?? true;
		$this->placeholder = $config->placeholder ?? "User";
		$this->select_options=DB::fetchAll('SELECT id AS value, CONCAT(username, " (", email, ")") AS text FROM users WHERE state=1 ORDER BY username ASC');
	}

}