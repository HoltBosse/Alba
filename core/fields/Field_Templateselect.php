
<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Templateselect extends Field_Select {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->select_options = DB::fetchAll("SELECT id AS value, title AS text FROM templates");
	}
}