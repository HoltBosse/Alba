<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \stdClass;
Use HoltBosse\Form\Form;
Use \PDO;

class Configuration {
	public ?string $id = null;
	public ?string $name = null; 
	public ?stdClass $configuration = null;
	public ?Form $form = null;
	public int $domain;

	static public function get_configuration_value (string $form_name, string $setting_name, ?PDO $pdo=null): mixed {
		// pdo param is a spot left for legacy reasons, but not actually used
		// TODO - fix json query
		// this is unsafe - not preparing $setting_name is BAD
		// mitigating with check for spaces
		// perhaps attempt CONCAT in json path section with ? preparation

		if (strpos($setting_name, ' ') !== false) {
			// setting names should not have spaces I'm guessing?
			return false;
		}

		// fallback - get complete json and get property in PHP
		$configuration = DB::fetch("SELECT configuration FROM configurations WHERE name=? AND domain=?", [$form_name, $_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST'])]);
		if ($configuration) {
			$config = json_decode($configuration->configuration);
			if (property_exists($config, $setting_name)) {
				return $config->{$setting_name};
			}
		}
		// not in db, get default from form
		$form = JSON::load_obj_from_file (__DIR__ . "/../admin/controllers/settings/views/general/general_options.json");
		//CMS::pprint_r ($form);
		$default = null; 
		if (property_exists($form,'fields')) { 
			foreach ($form->fields as $field) {
				if (property_exists($field,"name")) { 
					if ($field->name==$setting_name) {
						if (property_exists($field, "default")) { 
							if ($_ENV["debug"]) {
								CMS::log('Default value retrieved from form file for: ' . $setting_name . " from " . $form_name . " [{$field->default}]");
							}
							return $field->default;
						}
					}
				}
			}
		}
		CMS::log('No default value found for config field: ' . $setting_name);
		return false; // got here, got nuthin
	}

	function __construct(Form $form, ?int $domain=null) {
		$this->id = null;
		$this->name = $form->id;
		$this->form = $form;
		$this->configuration = new stdClass();

		if(is_null($domain)) {
			$domain = $_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']);
		}
		$this->domain = $domain;

		$this->load_from_form($this->form);
	}

	public function load_from_form(Form $form): void {
		foreach ($form->fields as $field) {
			// default is either actual default from json or updated value from model or similar
			$this->configuration->{$field->name} = $field->default; 
		}
	}

	public function load_from_db(): self|bool {
		// set config and form fields from configurations table entry
		$result = DB::fetch("SELECT * FROM configurations WHERE name=? AND domain=?", [$this->name, $this->domain]);
		if ($result) {
			// update object configuration
			$this->configuration = json_decode($result->configuration);
			// update config object form field values for display
			foreach ($this->form->fields as $field) {
				if ($this->configuration->{$field->name}===null||$this->configuration->{$field->name}===false) {
					// nothing stored in db for field (literally - 0 is fine :) 
					// do nothing - leave field default as default from form json
				}
				else {
					$field->default = $this->configuration->{$field->name};
				}
			}
			return $this;
		}
		else {
			return false;
		}
	}

	public function save(): self|bool {
		// update or insert new set of configuration options
		$json_config = json_encode($this->configuration);
		$configurationExists = DB::fetch("SELECT name FROM configurations WHERE name=? AND domain=?", [$this->name, $this->domain]);
		if ($configurationExists) {
			$ok = DB::exec("UPDATE configurations SET configuration=? WHERE name=? AND domain=?", [$json_config, $this->name, $this->domain]);
		} else {
			$ok = DB::exec("INSERT INTO configurations (name,configuration,domain) VALUES (?,?,?)", [$this->name, $json_config, $this->domain]);
		}

		if ($ok) {
			return $this;
		}
		else {
			return false;
		}
	}
}