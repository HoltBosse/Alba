<?php
namespace HoltBosse\Alba\Fields\UserPicker;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Core\CMS;

class UserPicker extends Select {
	public $domain;

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->domain = $config->domain ?? $_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']);
		$this->slimselect = $config->slimselect ?? true;
		$this->placeholder = $config->placeholder ?? "User";
		$this->select_options=DB::fetchAll('SELECT id AS value, CONCAT(username, " (", email, ")") AS text FROM users WHERE state=1 AND domain=? ORDER BY username ASC', [$this->domain]);
	}

}