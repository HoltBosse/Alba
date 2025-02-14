<?php
defined('CMSPATH') or die; // prevent unauthorized access

// BASE CLASS FOR FIELDS
class Field {
	public $id;
	public $title;
	public $label;
	public $name; // unique id for form submit
	public $description;
	public $required;
	public $valid;
	public $default;
	public $filter;
	public $type;
	public $logic;
	public $missingconfig;
	public $content_type;
	public $in_repeatable_form;
	public $maxlength;
	public $minlength;
	public $save;
	public $placeholder;
	public $nowrap;
	public $index; // used to determine POST/GET array index in repeatables

	public function display() {
		echo "<label class='label'>Field Label</label>";
		echo "<p>Hello, I am a field!</p>";
	}

	public function get_rendered_name($multiple=false) {
		// output name as array if in repeatable form
		// multiple makes it an array of arrays :D -> [][]
		$rendered_name = ' name="' . $this->name;
		if ($this->in_repeatable_form!==null || $multiple) {
			if ($this->in_repeatable_form!==null && $multiple) {
				$rendered_name .= "[{{replace_with_index}}][]"; // replace string with index in js when repeatable form + is clicked
			}
			else {
				$rendered_name .= "[]";
			}
		}
		$rendered_name .=  '" ';
		return $rendered_name;
	}

	public function validate() {
		return true;
	}

	public function is_missing() {
		if ($this->in_repeatable_form ?? null) {
			// value will be in array
			$value = Input::filter(Input::getvar($this->name)[$this->index], $this->filter);
		}
		else {
			$value = Input::getvar($this->name, $this->filter);
		}
		if ($value===false && $this->required) {
			return true;
		}
		if ($value===null && $this->required) {
			return true;
		}
		if ($value==='' && $this->required) {
			return true;
		}
		return false;
	}

	public function set_from_submit() {
		$value = Input::getvar($this->name, $this->filter);
		if (is_array($value)) {
			$this->default = json_encode($value);
		} else {
			$this->default = $value;
		}
	}

	public function set_from_submit_repeatable($index=0) {
		// index = index of repeated form inside repeatable
		$raw_value_array = Input::getvar($this->name, "ARRAYRAW"); // get raw array
		$raw_value = $raw_value_array[$index]; // get nth entry in raw array
		$value = Input::filter($raw_value, $this->filter); // filter raw value appropriately according to field filter in json
		$this->index = $index; // set repeatable field index for validation

		$this->default = $value;
		if (is_array($value)) {
			$this->default = json_encode($value);
		}
	}

	public function get_friendly_value($helpful_info) {
		// return friendly (text) version of data represented by default/current value
		// ostensibly used by 'list' item option in content listings for user driven columns
		// helpful info can be anything, but something like the field config object
		// can be used to determine, for example, a content type for a contentselector etc
		return $this->default;
	}

	public function set_value($value) {
		return true;
	}

	public function get_form_editor_visibility() {
		return false;
	}

	public function get_form_editor_display() {
		return "<p>" . explode("_", get_class($this))[1] . "</p>";
	}

	public function get_form_editor_config() {
		$blackList = ["valid", "type", "missingconfig", "in_repeatable_form"];
		$varTypeToInputType = [
			"string"=>"text",
			"integer"=>"number",
			"double"=>"number",
		];

		$stockField = new Field();
		$stockField->load_from_config((object) []);
		$options = [];

		// @phpstan-ignore-next-line
		foreach($stockField as $key=>$value) {
			if(in_array($key, $blackList)) {
				continue;
			}

			if(gettype($value)=="boolean") {
				$options[] = (object) [
					"type"=>"select",
					"name"=>$key,
					"id"=>$key,
					"label"=>ucwords($key),
					"options"=>(object) [
						"true"=>"True",
						"false"=>"False",
					]
				];
			} else {
				$options[] = (object) [
					"type"=>"input",
					"input_type"=>$varTypeToInputType[gettype($value)] ?? "string",
					"name"=>$key,
					"id"=>$key,
					"label"=>ucwords($key),
				];
			}
		}

		return $options;
	}

	public function load_from_config($config) {
		// config is json field already converted to object by form class
		$this->type = $config->type ?? 'error!!!';
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->save = $config->save ?? true;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'RAW';
		$this->default = $config->default ?? $this->default;
		$this->maxlength = $config->maxlength ?? 99999;
		$this->minlength = $config->minlength ?? 0;
		$this->placeholder = $config->placeholder ?? "";
		$this->logic = $config->logic ?? '';
		$this->nowrap = $config->nowrap;
	}
}