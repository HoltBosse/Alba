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


	public function designer_display() {
		echo "<label class='label'>Field Label</label>";
		echo "<p>Hello, I am a field!</p>";
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
		if (is_string($value)||is_numeric($value)) {
			$this->default = $value;
		}
		if (is_array($value)) {
			$this->default = json_encode($value);
		}
	}

	public function set_from_submit_repeatable($index=0) {
		// index = index of repeated form inside repeatable
		$raw_value_array = Input::getvar($this->name, "ARRAYRAW"); // get raw array
		$raw_value = $raw_value_array[$index]; // get nth entry in raw array
		$value = Input::filter($raw_value, $this->filter); // filter raw value appropriately according to field filter in json
		$this->index = $index; // set repeatable field index for validation
		if (is_string($value)||is_numeric($value)) {
			$this->default = $value;
		}
		elseif (is_array($value)) {
			$this->default = json_encode($value);
		}
		elseif (is_bool($value)) {
			$this->default = $value;
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

	public function load_from_config($config) {
		// config is json field already converted to object by form class
		return true;
	}
}