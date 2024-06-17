<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentPicker extends Field_Select {
	
	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->content_type = $config->content_type ?? false;
		$this->slimselect = $config->content_type ?? true;
		$this->multiple = $config->multiple ?? true;

		if ($this->content_type) {
			if (!is_numeric($this->content_type)) {
				$this->content_type = DB::fetch('SELECT id from content_types WHERE title=?',[$this->content_type])->id ?? null;
			}
			if (!$this->content_type) {
				CMS::show_error('ContentPicker unable to determine content type');
			}

			$location = Content::get_content_location($this->content_type);
    		$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
    		$table_name = "controller_" . $custom_fields->id ;
			$this->select_options = DB::fetchAll("SELECT id as value, title as text FROM `{$table_name}` WHERE state=1");
		}
		else {
			CMS::show_error('ContentPicker must have content type specified');
		}
	}

}
