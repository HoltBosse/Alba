<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Checkbox extends Field {

	public function display() {
		echo "<div class='field'>";
			echo "<label for='{$this->id}' class='checkbox'>";
				echo "<input type='hidden' data-logicignore value='0' {$this->get_rendered_name()} {$this->get_rendered_form()}>"; // ensure submitted value
				$required="";
				if ($this->required) {$required=" required ";}
				if ($this->default) {$checked=" checked=checked ";} // 0 value stored for unchecked
				echo "<input $checked value='1' type='checkbox' id='{$this->id}' {$this->get_rendered_name()} {$this->get_rendered_form()}>";
				echo "&nbsp;" . $this->label;
			echo "</label>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}

	public function get_friendly_value($helpful_info) {
		$checked="";
		if ($this->default==1) {
			$checked=" checked ";
		}
		return "<input type='checkbox' disabled {$checked}>";
	}

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->filter = $config->filter ?? 'NUMBER';
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}
