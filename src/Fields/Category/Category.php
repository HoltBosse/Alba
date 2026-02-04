<?php
namespace HoltBosse\Alba\Fields\Category;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Core\CMS;

class Category extends Select {

	public $self_id;
	public $content_type;
	public $domain;

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->content_type = $config->content_type ?? 1;
		$this->domain = $config->domain ?? $_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']);

		return $this;
	}

	public function display() {
		//we have to do this here, rather than the config method as the property is set o nthe field after the config is loaded
		$query = "SELECT id AS value, title as text FROM categories WHERE content_type=? AND (domain=? OR domain IS NULL)";
		$params = [$this->content_type, $this->domain];

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