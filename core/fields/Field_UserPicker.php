<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_UserPicker extends Field_Select {

	public function load_from_config($config) {
        parent::load_from_config($config);

        $this->slimselect = $config->slimselect ?? true;
        $this->placeholder = $config->placeholder ?? "User";

        $this->select_options=DB::fetchAll('SELECT id AS value, CONCAT(username, " (", email, ")") AS text FROM users WHERE state=1 ORDER BY username ASC');
	}

}