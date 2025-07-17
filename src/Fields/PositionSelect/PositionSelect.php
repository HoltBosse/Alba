<?php
namespace HoltBosse\Alba\Fields\PositionSelect;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\Alba\Core\Template;

class PositionSelect extends Select {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

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