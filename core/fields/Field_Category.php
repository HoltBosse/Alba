<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Category extends Field {

	public $select_options;

	function __construct($tagid="") {
		$this->id = "";
		$this->name = "";
		$this->default = 0;
		$this->content_type="1";
		$this->self_id; // prevent showing self as option for parent
	}

	

	public function display() {
		$required="";
		if ($this->content_type) {
			$cats = Content::get_applicable_categories ($this->content_type);
		}
		if ($this->required) {$required=" required ";}
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='select'>";
					echo "<select {$required} id='{$this->id}' {$this->get_rendered_name()}>";
						if ($this->required) {
							echo "<option value='' >{$this->label}</option>";
						}
						else {
							echo "<option value='0'>None</option>";
						}
						foreach ($cats as $cat) {
							if ($this->self_id==$cat->id) {
								// don't show self if self_id is set
								continue;
							}
							if ($cat->state==1) {
								$selected = "";
								if ($cat->id == $this->default) { $selected="selected";}
								echo "<option {$selected} value='{$cat->id}'>{$cat->title}</option>";
							}
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}
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
		$this->content_type = $config->content_type ?? 1;
		$this->logic = $config->logic ?? '';
	}

	public function get_friendly_value() {
		return DB::fetch('select title from categories where id=?', [$this->default])->title;
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}