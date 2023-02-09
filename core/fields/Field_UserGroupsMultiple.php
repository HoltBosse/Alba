<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_UserGroupsMultiple extends Field {

	public $select_options;

	function __construct($tagid="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default = "";
		$this->content_type="";
		$this->type = "UserGroupsMultiple";
	}

	

	public function display() {
		$this->array_values = json_decode ($this->default);
		$required="";
		$groups = User::get_all_groups();
		
		if ($this->required) {$required=" required ";}
		// if id needs to be unique for scripting purposes, make sure replacement text inserted
		// this will be replaced during repeatable template literal js injection when adding new
		// repeatable form item
		if ($this->in_repeatable_form===null) {
			$repeatable_id_suffix='';
		}
		else {
			$repeatable_id_suffix='{{repeatable_id_suffix}}';
		}
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='select'>";
					echo "<select class='is-multiple' multiple {$required} id='{$this->id}{$repeatable_id_suffix}' {$this->get_rendered_name(true)}>";
						if ($this->required) {
							echo "<option value='' >{$this->label}</option>";
						}
						foreach ($groups as $group) {
							$selected = "";
							if (is_array($this->array_values)) {
								if (in_array($group->id, $this->array_values)) { $selected="selected";}
							}
							echo "<option {$selected} value='{$group->id}'>{$group->display}</option>";
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}
		// Slimselect Multiple library 
		if ($this->in_repeatable_form===null) {
			echo "<script>new SlimSelect({ select: '#{$this->id}' });</script>"; 
		}
		else {
			// also inject id_suffix to be replace at injection time
			echo "<script>new SlimSelect({ select: '#{$this->id}{$repeatable_id_suffix}' });</script>"; 
		}
	}



	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'ARRAYOFINT';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->select_options = $config->select_options ?? [];
		$this->default = json_decode($config->default) ?? "";
		$this->type = $config->type ?? 'error!!!';
		$this->content_type = $config->content_type ?? false;
		$this->logic = $config->logic ?? '';
	}

	public function get_friendly_value() {
		return DB::fetch('select title from tags where id=?', [$this->default])->title;
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}