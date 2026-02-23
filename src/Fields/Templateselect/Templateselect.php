<?php
namespace HoltBosse\Alba\Fields\Templateselect;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;

class Templateselect extends Select {

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$this->select_options = DB::fetchAll("SELECT id AS value, title AS text FROM templates");

		return $this;
	}
}