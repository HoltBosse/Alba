<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentPicker extends Field {

	public $select_options;

	function __construct($tagid="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default = "";
		$this->content_type="";
		$this->type = "ContentPicker";
	}

	

	public function display() {
		$this->array_values = json_decode ($this->default);
		$required="";
		if ($this->content_type) {
			if (!is_numeric($this->content_type)) {
				$this->content_type = DB::fetch('select id from content_types where title=?',[$this->content_type])->id ?? null;
			}
			if (!$this->content_type) {
				CMS::show_error('ContentPicker unable to determine content type');
			}
			// get_all_content($order_by="id", $type_filter=false, $id=null, $tag=null, $published_only=null, $list_fields=[], $ignore_fields=[], $filter_field=null, $filter_val=null, $page=0) 
			//$content = Content::get_all_content ("id", $this->content_type, null, null, true); // get all published only content

			$location = Content::get_content_location($this->content_type);
    		$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
    		$table_name = "controller_" . $custom_fields->id ;
			$query = "select id,title,state from {$table_name} where state=1";
			$content = DB::fetchAll($query);
			/* CMS::pprint_r ($query);
			CMS::pprint_r ($content); */
		}
		else {
			CMS::show_error('ContentPicker must have content type specified');
		}
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
				echo "<div class='select is-multiple'>";
					echo "<select class='is-multiple' multiple {$required} id='{$this->id}{$repeatable_id_suffix}' {$this->get_rendered_name(true)}>";
						if ($this->required) {
							echo "<option disabled value='' >{$this->label}</option>";
						}
						foreach ($content as $item) {
							if ($item->state==1) {
								$selected = "";
								if ($this->array_values && in_array($item->id, $this->array_values)) { $selected="selected";}
								echo "<option {$selected} value='{$item->id}'>{$item->title}</option>";
							}
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
		return DB::fetch('select title from content where id=?', [$this->default])->title;
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}
