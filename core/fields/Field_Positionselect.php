<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Positionselect extends Field_Select {

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->placeholder = $config->placeholder ?? "Template Position: (required)";

		$this->select_options = [];
		$default_template = new Template(Template::get_default_template()->id);
		foreach($default_template->positions as $position) {
			$this->select_options[] = (object) [
				"value"=>$position,
				"text"=>$position
			];
		}
	}

}