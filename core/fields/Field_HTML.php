<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_HTML extends Field {

	public $html;
	public $save;
	public $maxlength;
	public $select_options;

	public function display() {
		echo $this->html;
	}

	public function get_form_editor_visibility() {
		return true;
	}

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->html = $config->html ?? "";
	}

	public function validate() {
		// not a real field, just displays stuff :)
		return true;
	}
}