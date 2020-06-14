<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Textarea extends Field {

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
		echo "<div class='field {$hidden}'>";
			echo "<label class='label'>{$this->label}</label>";
			echo "<div class='control'>";
				$required="";
				if ($this->required) {$required=" required ";}
				if ($this->input_type=='date') {
					if ($this->default>0) {
						$this->default = date("Y-m-d", strtotime($this->default));
					}
					else {
						$this->default = "";
					}
				}
				echo "<textarea type='{$this->input_type}' value='{$this->default}' maxlength={$this->maxlength} minlength={$this->minlength} class='filter_{$this->filter} input' {$required} type='text' id='{$this->id}' name='{$this->name}'>";
				echo $this->default;
				echo "</textarea>";
			echo "</div>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}


	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->maxlength = $config->maxlength ?? 999;
		$this->filter = $config->filter ?? 'RAW';
		$this->minlength = $config->minlength ?? 0;
		$this->missingconfig = $config->missingconfig ?? false;
		$this->type = $config->type ?? 'error!!!';
		$this->input_type = $config->input_type ?? 'text';
		$this->default = $config->default ?? $this->default;
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}