<?php
namespace HoltBosse\Alba\Fields\SqlSelector;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\Alba\Core\{Content, JSON, CMS};
use HoltBosse\DB\DB;
use \Exception;

class SqlSelector extends Select {
	public ?string $query = null;
	public array $params = [];

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		if(!isset($config->query) || $config->query=="" || $config->query==null) {
			throw new Exception("SqlSelector: invalid query passed in!");
		}

		$this->query = $config->query;
		$this->params = $config->params ?? [];

		$this->select_options = DB::fetchall($this->query, $this->params);

		return $this;
	}

}