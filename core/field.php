<?php
defined('CMSPATH') or die; // prevent unauthorized access

// BASE CLASS FOR FIELDS
class Field {
	public $id;
	public $title;
	public $name; // unique id for form submit
	public $required;
	public $valid;
	public $default;
	public $filter;
	public $type;

	public function display() {
		echo "<label class='label'>Field Label</label>";
		echo "<p>Hello, I am a field!</p>";
	}

	public function designer_display() {
		echo "<label class='label'>Field Label</label>";
		echo "<p>Hello, I am a field!</p>";
	}

	public function validate() {
		return true;
	}

	public function is_missing() {
		$value = CMS::getvar($this->name, $this->filter);
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
		$value = CMS::getvar($this->name, $this->filter);
		if ($value||is_numeric($value)) {
			$this->default = $value;
		}
	}

	public function get_friendly_value() {
		// return friendly (text) version of data represented by default/current value
		// ostensibly used by 'list' item option in content listings for user driven columns
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