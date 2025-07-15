<?php
namespace HoltBosse\Alba\Fields\Category;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;

class Category extends Select {

	public $self_id;
	public $content_type;

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->content_type = $config->content_type ?? 1;
	}

	public function display() {
		//we have to do this here, rather than the config method as the property is set o nthe field after the config is loaded
		$query = "SELECT id AS value, title as text FROM categories WHERE content_type=?";
		$params = [$this->content_type];

		if($this->self_id) {
			$query .= " AND id!=?";
			$params[] = $this->self_id;
		}

		$this->select_options = DB::fetchAll($query, $params);

		array_unshift(
			$this->select_options,
			(object) [
				"text"=>"None",
				"value"=>0,
			]
		);

		parent::display();
	}
}