<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Textarea extends Field {

	public $maxlength;
	public $minlength;
	public $select_options;
	public $input_type;

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default = $default_content;
		$this->content_type="";
	}

	public function display() {
		if (property_exists($this,'attribute_list')) {
			$attributes = implode(' ',$this->attribute_list);
			if (in_array('hidden',$this->attribute_list)) {
				$hidden = "hidden";
			}
		}
		$required="";
		if ($this->required) {$required=" required ";}
		echo "<div class='field {$required} {$hidden}'>";
			echo "<label for='{$this->id}' class='label'>{$this->label}</label>";
			echo "<div class='control' data-value='{$this->default}'>";
				$this->default = str_replace("[NEWLINE]","\n",$this->default);
				echo "<textarea oninput='this.parentNode.dataset.value = this.value;' type='{$this->input_type}' maxlength={$this->maxlength} placeholder='{$this->placeholder}' minlength={$this->minlength} class='filter_{$this->filter} input autogrowingtextarea' {$required} type='text' id='{$this->id}' {$this->get_rendered_name()}>";
				echo $this->default;
				echo "</textarea>";
			echo "</div>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}


	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->filter = $config->filter ?? 'TEXTAREA';
		$this->input_type = $config->input_type ?? 'text';
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}