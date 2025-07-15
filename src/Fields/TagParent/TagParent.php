<?php
namespace HoltBosse\Alba\Fields\TagParent;

Use HoltBosse\Alba\Fields\TagSingle\TagSingle;

class TagParent extends TagSingle {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		array_unshift(
			$this->select_options,
			(object) [
				"text"=>"None",
				"value"=>0,
			]
		);
	}
}