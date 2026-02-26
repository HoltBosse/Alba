<?php
namespace HoltBosse\Alba\Fields\FormInstances;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\Alba\Core\{CMS};
Use HoltBosse\Alba\Fields\SqlSelector\SqlSelector;
Use \stdClass;

class FormInstances extends SqlSelector {
    public ?int $domain = null;

	public function loadFromConfig(object $config): self {
        $domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);

        $this->domain = $config->domain ?? $domain;
        
		//@phpstan-ignore-next-line
		$config->query = "SELECT id as value, title as text FROM `form_instances` WHERE state >= 0 AND (domain = ? OR domain IS NULL) ORDER BY title ASC";
		//@phpstan-ignore-next-line
		$config->params = [$this->domain];

        parent::loadFromConfig($config);

		return $this;
	}

}