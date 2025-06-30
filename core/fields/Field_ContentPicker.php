<?php
defined('CMSPATH') or die; // prevent unauthorized access

//shim for legacy reasons.
class Field_ContentPicker extends Field_Contentselector {
	
	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->content_type = $config->content_type ?? false;
		$this->slimselect = $config->slimselect ?? true;
		$this->multiple = $config->multiple ?? true;
	}

}
