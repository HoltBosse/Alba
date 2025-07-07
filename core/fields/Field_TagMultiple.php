<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_TagMultiple extends Field_TagSingle {

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->multiple = $config->multiple ?? true;
		$this->slimselect = $config->slimselect ?? true;
	}
}
