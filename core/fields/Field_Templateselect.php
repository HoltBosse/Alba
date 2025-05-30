
<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Templateselect extends Field_Select {

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->select_options = DB::fetchAll("SELECT id AS value, title AS text FROM templates");
	}
}