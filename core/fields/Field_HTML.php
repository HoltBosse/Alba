<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_HTML extends Field {

	public $html;
	public $save;
	public $maxlength;
	public $select_options;

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default = $default_content;
		$this->content_type="";
		$this->save=false;
	}

	public function display() {
		echo $this->html;
	}



	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->html = $config->html ?? "";
	}

	public function validate() {
		// not a real field, just displays stuff :)
		return true;
	}
}