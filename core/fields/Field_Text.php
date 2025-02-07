<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Text extends Field {

	public $select_options;
	public $pattern;
	public $input_type;
	public $min;
	public $max;
	public $attribute_list;
	public $step;
	public $icon_status;
	public $icon_parent_class;
	public $icon_markup;

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
			echo "<div class='control " . ($this->icon_status ? $this->icon_parent_class : false) . "'>";
				
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
				//explictly using htmlspecialchars here instead of Input::stringHtmlSafe because this is for attribute handling while the latter method is for in elements
				$value = htmlspecialchars($this->default);
				echo "<input type='{$this->input_type}' value='{$value}' placeholder='{$placeholder}' {$minmax} {$pattern} {$step} {$this->get_rendered_name()} maxlength={$this->maxlength} minlength={$this->minlength} class='filter_{$this->filter} input' {$required} type='text' id='{$this->id}' >";
				echo $this->icon_status ? $this->icon_markup : false;
			echo "</div>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}

	public function get_friendly_value($helpful_info) {
		if($this->filter=="RAW" && $helpful_info && $helpful_info->return_in_text_form!=true) {
			return Input::stringHtmlSafe($this->default);
		} else {
			return $this->default;
		}
	}

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->input_type = $config->input_type ?? 'text';
		$this->pattern = $config->pattern ?? '';
		if ($this->input_type=='range') {
			$this->min = $config->min ?? "0";
			$this->max = $config->max ?? "100";
		} elseif ($this->input_type=='number') {
			$this->min = $config->min ?? "";
			$this->max = $config->max ?? "";
		}
		$this->attribute_list = $config->attribute_list ?? "";
		$this->step = $config->step;
		$this->icon_status = $config->icon_status ?? false;
		$this->icon_parent_class = $config->icon_parent_class ?? "";
		$this->icon_markup = $config->icon_markup ?? "";
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing() || mb_strlen($this->default)>$this->maxlength) {
			return false;
		}
		return true;
	}
}
