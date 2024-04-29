<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Text extends Field {

	public $select_options;
	public $placeholder;
	public $pattern;
	public $input_type;
	public $min;
	public $max;
	public $attribute_list;
	public $step;

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default = $default_content;
		$this->content_type="";
		$this->placeholder="";
		$this->pattern = "";
	}

	public function display() {
		$hidden = "";
		$required="";
		$pattern="";
		if ($this->pattern) {$pattern="pattern='{$this->pattern}'"; };
		if ($this->required) {$required=" required ";}
		if (property_exists($this,'attribute_list')) {
			$attributes = explode(' ',$this->attribute_list);
			if (in_array('hidden',$attributes)) {
				$hidden = "hidden";
			}
		}
		echo "<div class='field {$required} {$hidden}'>";
			echo "<label for='{$this->id}' class='label'>{$this->label}</label>";
			echo "<div class='control'>";
				
				if ($this->input_type=='date') {
					if ($this->default>0) {
						$this->default = date("Y-m-d", strtotime($this->default));
					}
					else {
						$this->default = "";
					}
				}
				$minmax="";
				if (property_exists($this,'min')) {
					$minmax=" min='{$this->min}' max='{$this->max}' ";
				}
				$step="";
				if (property_exists($this,'step')) {
					$step=" step='{$this->step}' ";
				}
				$placeholder = $this->placeholder ?? "";

				echo "<input type='{$this->input_type}' value='{$this->default}' placeholder='{$placeholder}' {$minmax} {$pattern} {$step} {$this->get_rendered_name()} maxlength={$this->maxlength} minlength={$this->minlength} class='filter_{$this->filter} input' {$required} type='text' id='{$this->id}' >";
			echo "</div>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Text = {};
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
		$this->maxlength = $config->maxlength ?? 999;
		$this->filter = $config->filter ?? 'RAW';
		$this->minlength = $config->minlength ?? 0;
		$this->missingconfig = $config->missingconfig ?? false;
		$this->type = $config->type ?? 'error!!!';
		$this->input_type = $config->input_type ?? 'text';
		$this->pattern = $config->pattern ?? '';
		if ($this->input_type=='range') {
			$this->min = $config->min ?? "0";
			$this->max = $config->max ?? "100";
		}
		elseif ($this->input_type=='number') {
			$this->min = $config->min ?? "";
			$this->max = $config->max ?? "";
		}
		$this->default = $config->default ?? $this->default;
		$this->attribute_list = $config->attribute_list ?? "";
		$this->placeholder = $config->placeholder ?? "";
		$this->logic = $config->logic ?? '';
		$this->step = $config->step;
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing() || mb_strlen($this->default)>$this->maxlength) {
			return false;
		}
		return true;
	}
}
