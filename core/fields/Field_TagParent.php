<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_TagParent extends Field_TagSingle {

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