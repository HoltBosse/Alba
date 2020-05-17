<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Checkbox extends Field {

	public function display() {
		echo "<div class='field'>";
			echo "<label for='{$this->id}' class='checkbox'>";
				echo "<input type='hidden' value='0' name='{$this->name}'>"; // ensure submitted value
				$required="";
				if ($this->required) {$required=" required ";}
				if ($this->default) {$checked=" checked=checked ";} // 0 value stored for unchecked
				echo "<input $checked value='1' type='checkbox' id='{$this->id}' name='{$this->name}'>";
				echo "&nbsp;" . $this->label;
			echo "</label>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Checkbox = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Text.designer_template = `
			<div class="field">
				<h2 class='heading title'>Text Field</h2>	

				<label class="label">Label</label>
				<div class="control has-icons-left has-icons-right">
					<input required name="label" class="input iss-success" type="label" placeholder="Label" value="">
				</div>

				<label class="label">Required</label>
				<div class="control has-icons-left has-icons-right">
					<input name="required" class="checkbox iss-success" type="checkbox"  value="">
				</div>
			</div>`;
		</script>
		<?php 
	}

	public function designer_display() {

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
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}