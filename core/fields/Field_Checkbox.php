<?php
defined('CMSPATH') or die; // prevent unauthorized access

// by using a select input to handle the actual submitted values
// the checkbox field can be natively used to handle the UX accessibly
// this will force EVERY checked field to submit a value 
// - this is needed for correct evaluation of checkbox form data in a repeatable

class Field_Checkbox extends Field {

	public function display() {
		if ($this->in_repeatable_form===null) {
			$repeatable_id_suffix='';
		}
		else {
			$repeatable_id_suffix='{{repeatable_id_suffix}}'; // injected via JS at repeatable addition time
			$this->id = $this->id . $repeatable_id_suffix;
		}
		echo "<div class='field'>";
			$required="";
			if ($this->required) {$required=" required ";}
			// hidden select
			$off_selected = $this->default ? "" : " selected ";
			$on_selected = $this->default ? " selected " : "";
			echo "<select style='display:none;' id='{$this->id}' {$this->get_rendered_name()}><option $off_selected value='0'>Off</option><option $on_selected value='1'>On</option></select>";
			// visible checkbox + label
			echo "<label for='checkbox_{$this->id}' class='checkbox'>";
				if ($this->default) {$checked=" checked=checked ";} 
				$onchange_js = 'this.closest(".field").querySelector("select").value=(this.checked ? 1 : 0);'; // set option to match checkbox
				echo "<input onchange='$onchange_js' $checked value='1' type='checkbox' id='checkbox_{$this->id}' >"; // no name given, not submitted
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
