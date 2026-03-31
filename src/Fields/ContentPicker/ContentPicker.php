<?php
namespace HoltBosse\Alba\Fields\ContentPicker;

Use HoltBosse\Alba\Fields\ContentSelector\ContentSelector;

//shim for legacy reasons.
class ContentPicker extends ContentSelector {

	public mixed $content_type = null;
	
	public function loadFromConfig(object $config): self {
		//set this beforehand due to selects loadfromconfig setting a filter dependant on the multiple property
		if (!property_exists($config, 'multiple')) {
			/** @phpstan-ignore-next-line */
			$config->multiple = true;
		}

		parent::loadFromConfig($config);

		$this->content_type = $config->content_type ?? false;
		$this->slimselect = $config->slimselect ?? true;

		return $this;
	}

}
