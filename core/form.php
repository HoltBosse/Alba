<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Form {
	public $location; // relative to CMS path for json config
	public $fields;

	function __construct($path=CMSPATH . "/testform.json") {
		$this->fields = array();
		$this->location = "";
		$this->load_json($path);
	}

	public function load_json($path = CMSPATH . "/testform.json") {
		if (!is_file($path)) {
			echo "<h5>File {$path} not found</h5>";
			if (Config::$debug) {
				echo "<p class='help'>Called from /core/form.php load_json function</p>";
			}
		}
		else {

			$json = file_get_contents($path);
			$obj = json_decode($json);
			if (!$obj) {
				echo "<h5>Invalid json in file {$path}</h5>";
				//CMS::Instance()->queue_message('Invalid JSON found in: ' . $path,'danger',Config::$uripath.'/admin');
			}
			$tempfields = $obj->fields;
			$this->id = $obj->id;
			//CMS::pprint_r ($tempfields);
			foreach ($tempfields as $field_config) {
				$class = "Field_" . $field_config->type;
				$thisfield = new $class();
				$thisfield->load_from_config($field_config);
				$this->fields[] = $thisfield;
			}
		}
	}

	public function set_from_submit() {
		foreach ($this->fields as $field) {
			$field->set_from_submit();
		}
	}

	public function get_field_by_name($field_name) {
		foreach ($this->fields as $field) {
			if ($field->name == $field_name) {
				return $field;
			}
		}
		if (Config::$debug) {
			echo "<p class='unimportant'>Field {$field_name} not found.</p>";
		}
		return false;
	}

	public function is_submitted() {
		if ($this->id) {
			$form_name = CMS::getvar("form_" . $this->id, "TEXT");
			if ($form_name) {
				return true;
			}
		}
		return false;
	}

	public function validate() {
		foreach ($this->fields as $field) {
			if (!$field->validate()) {
				return false;
			}
		}
		return true;
	}

	public function serialize_json() {
		$name_value_pairs = array();
		foreach ($this->fields as $field) {
			$pair = new stdClass();
			$pair->name = $field->name;
			$pair->value = $field->default;
			$name_value_pairs[] = $pair;
		}
		return json_encode ($name_value_pairs);
	}

	public function deserialize_json($json) {
		$json_obj = json_decode($json);
		//CMS::pprint_r ($json_obj);
		if ($json_obj) {
			foreach ($json_obj as $option) {
				$field = $this->get_field_by_name($option->name);
				$field->default = $option->value;
			}
		}
	}

	public function display_front_end() {
		// loop through fields and call display();
		//CMS::pprint_r ($this);
		
		foreach ($this->fields as $field) {
			echo "<div class='form_field field'>";
			$field->display();
			echo "</div>";
		}
		echo "<input type='hidden' value='1' name='form_" . $this->id . "'>";
	}
}