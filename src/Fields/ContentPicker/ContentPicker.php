<?php
namespace HoltBosse\Alba\Fields\ContentPicker;

Use HoltBosse\Alba\Fields\ContentSelector\ContentSelector;

//shim for legacy reasons.
class ContentPicker extends ContentSelector {

	public $content_type;
	
	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->content_type = $config->content_type ?? false;
		$this->slimselect = $config->slimselect ?? true;
		$this->multiple = $config->multiple ?? true;

		return $this;
	}

}
