<?php
namespace HoltBosse\Alba\Fields\ContentPicker;

Use HoltBosse\Alba\Fields\ContentSelector\ContentSelector;

//shim for legacy reasons.
class ContentPicker extends ContentSelector {

	public mixed $content_type = null;
	
	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$this->content_type = $config->content_type ?? false;
		$this->slimselect = $config->slimselect ?? true;
		$this->multiple = $config->multiple ?? true;

		return $this;
	}

}
