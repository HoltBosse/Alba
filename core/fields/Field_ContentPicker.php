<?php
defined('CMSPATH') or die; // prevent unauthorized access

//shim for legacy reasons.
class Field_ContentPicker extends Field_Contentselector {
	
	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->content_type = $config->content_type ?? false;
		$this->slimselect = $config->slimselect ?? true;
		$this->multiple = $config->multiple ?? true;
	}

}
