<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Checkbox extends Field {

	public function display() {
		echo "<div class='field'>";
			echo "<label for='{$this->id}' class='checkbox'>";
				echo "<input type='hidden' value='0' {$this->get_rendered_name()}>"; // ensure submitted value
				$required="";
				if ($this->required) {$required=" required ";}
				if ($this->default) {$checked=" checked=checked ";} // 0 value stored for unchecked
				echo "<input $checked value='1' type='checkbox' id='{$this->id}' {$this->get_rendered_name()}>";
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
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'NUMBER';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->default = $config->default ?? 0;
		$this->type = 'Checkbox';
		$this->logic = $config->logic ?? '';
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}
