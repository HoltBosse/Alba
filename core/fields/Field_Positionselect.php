<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Positionselect extends Field {

	public $select_options;

	function __construct($id="") {
		$this->id = $id;
		$this->name = $id;
	}

	

	public function display() {
		$default_template = new Template(Template::get_default_template()->id);
		$required="";
		if ($this->required) {$required=" required ";}
		echo "<div class='select'>";
			echo "<select {$required} id='{$this->id}' name='{$this->name}'>";
				if ($this->required) {
					echo "<option value='' >{$this->label}: (required)</option>";
				}
				foreach ($default_template->positions as $select_option) {
					$selected = "";
					if (property_exists($this,'default')) {
						if ($select_option == $this->default) { $selected="selected";}
					}
					echo "<option {$selected} value='{$select_option}'>{$select_option}</option>";
				}
			echo "</select>";
			
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Positionselect = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Positionselect.designer_template = `
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
		$this->default = $config->default ?? '';
		$this->type = $config->type ?? 'error!!!';
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}