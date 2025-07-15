<?php
namespace HoltBosse\Alba\Fields\TagMultiple;

Use HoltBosse\Alba\Fields\TagSingle\TagSingle;

class TagMultiple extends TagSingle {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->multiple = $config->multiple ?? true;
		$this->slimselect = $config->slimselect ?? true;
	}
}
