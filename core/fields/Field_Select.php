<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Select extends Field {

	public $select_options;

	function __construct($id="") {
		$this->id = $id;
		$this->name = $id;
		$this->select_options=[];
		$this->config = false;
	}

	public function display() {
		$required="";
		if ($this->required) {$required=" required ";}
		if (property_exists($this,'attribute_list')) {
			$attributes = implode(' ',$this->attribute_list);
			if (in_array('hidden',$this->attribute_list)) {
				$hidden = "hidden";
			}
		}
		echo "<div class='field {$hidden}'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='select'>";
					echo "<select {$required} id='{$this->id}' {$this->get_rendered_name()}>";
						if ($this->required) {
							echo "<option value='' >{$this->label}</option>";
						}
						foreach ($this->select_options as $select_option) {
							$selected = "";
							if ($select_option->value == $this->default) { $selected="selected";}
							echo "<option {$selected} value='{$select_option->value}'>{$select_option->text}</option>";
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help {$hidden}'>" . $this->description . "</p>";
		}
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Select = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Select.designer_template = `
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

	public function get_friendly_value() {
		// TODO: select fields need to store config somewhere to retrieve friendly values
		// for now return id
		return $this->id;
	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'NUMBER';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->select_options = $config->select_options ?? [];
		$this->default = $config->default ?? '';
		$this->type = $config->type ?? 'error!!!';
		$this->config = $config;
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}