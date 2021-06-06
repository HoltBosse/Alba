<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Form {
	public $location; // relative to CMS path for json config
	public $fields;
	public $repeatable;

	function __construct($path = CMSPATH . "/testform.json", $repeatable=false) {
		$this->fields = array();
		$this->location = "";
		$this->repeatable = $repeatable;
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
				if ($this->repeatable) {
					$thisfield->in_repeatable_form = true;
				}
				if (property_exists($field_config,'name')) {
					// not all form fields require name property - only saveable items
					// HTML field / Tab field etc are for rendering only
					$this->fields[$field_config->name] = $thisfield;
				}
				else {
					$this->fields[] = $thisfield;
				}
			}
		}
	}

	public function set_from_submit() {
		foreach ($this->fields as $field) {
			$field->set_from_submit();
		}
	}

	public function get_field_by_name($field_name) {
		if (isset($this->fields[$field_name])) {
			return $this->fields[$field_name];
		}
		else {
			if (Config::$debug) {
				CMS::log('Unable to load form field ' . $field_name);
			}
		}
		return false;
	}

	public function is_submitted() {
		if ($this->id) {
			$form_name = Input::getvar("form_" . $this->id, "TEXT");
			if ($form_name) {
				return true;
			}
		}
		return false;
	}

	public function validate() {
		foreach ($this->fields as $field) {
			if (!$field->validate()) {
				$field_info = print_r ($field,true);
				CMS::Instance()->log('Invalid field: ' . $field_info);
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
			if ($field->type=="Repeatable") {
				// loop through each repeatable form and each field inside each form
				// creating tuples for each
				$sub_form_value_array=[];
				foreach ($field->forms as $sub_form) {
					$sub_pair = new stdClass();
					$sub_pair->name = $sub_form->id;
					$sub_values = [];
					foreach ($sub_form->fields as $sub_form_field) {
						$sub_field_pair = new stdClass();
						$sub_field_pair->name = $sub_form_field->name;
						$sub_field_pair->value = $sub_form_field->default;
						$sub_values[] = $sub_field_pair;
					}
					$sub_pair->value = $sub_values;
					$sub_form_value_array[] = $sub_pair;
				}
				$pair->value = $sub_form_value_array;
			}
			else {
				$pair->value = $field->default;
			}
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
		
		// first make sure array added to name if required
		$aftername='';
		if ($this->repeatable) {
			$aftername="[]";
		}

		// loop through fields and call display();
		foreach ($this->fields as $field) {
			if (!property_exists($field,'nowrap')) {
				echo "<div class='form_field field'>";
			}
			$field->display();
			if (!property_exists($field,'nowrap')) {
				echo "</div><!-- end field -->";
			}
		}
		echo "<input type='hidden' value='1' name='form_" . $this->id . "{$aftername}'>";
	}
}