<?php
namespace HoltBosse\Alba\Fields\TagMultiple;

Use HoltBosse\Alba\Fields\TagSingle\TagSingle;

class TagMultiple extends TagSingle {

	public function loadFromConfig(object $config): self {
		//set this beforehand due to selects loadfromconfig setting a filter dependant on the multiple property
		if (!property_exists($config, 'multiple')) {
			/** @phpstan-ignore-next-line */
			$config->multiple = true;
		}
		
		parent::loadFromConfig($config);

		$this->slimselect = $config->slimselect ?? true;

		return $this;
	}
}
