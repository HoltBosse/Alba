<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Contentselector extends Field {

	public $select_options;
	public $list_unpublished;
	public $tags;
	public $empty_string;

	function __construct($content_type=false) {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default=false; // content id
		$this->list_unpublished=false;
		$this->tags=[];
		$this->content_type = false; // content type - REQUIRED in flat
	}

	public function display() {
		$required="";
		if (!$this->content_type) {
			CMS::show_error('Content type required for Contentselector field in v3+');
		}

		if (!is_numeric($this->content_type)) {
			// content type denoted by content controller location - this is a unique safe folder name
			// e.g. basic_article
			$this->content_type = Content::get_content_type_id($this->content_type);
		}
		if ($this->content_type && is_numeric($this->content_type)) {

			$location = Content::get_content_location($this->content_type);
			$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
			$table_name = "controller_" . $custom_fields->id ;
			
			if ($this->list_unpublished) {
				$min_state = 0;
			}
			else {
				$min_state = 1;
			}
			if (!$this->tags) {
				// default order is alphabetical
				$query = "select * from " . $table_name . " where state >={$min_state} order by title ASC";
				$options_all_articles = CMS::Instance()->pdo->query($query)->fetchAll();
				/* CMS::pprint_r ($query);
				CMS::pprint_r ($options_all_articles); */
			}
			else {
				$tags_csv = "'".implode("','", $this->tags)."'";
				$query = "select c.* from {$table_name} c where c.state=1 ";
				$query .= " and c.id in (";
					$query .= " select tc.content_id from tagged tc where tc.content_type_id={$this->content_type} and tc.tag_id in (";
						$query .= "select t.id from tags t where t.state>={$min_state} and t.alias in ($tags_csv)";
					$query .= ")";
				$query .= ") order by c.title ASC";
				$options_all_articles = DB::fetchAll($query);
			}
		}
		
		if (!$options_all_articles) {
			// content type was not able to be established
			if (Config::debug()) {
				echo "<h5>Error determining content type</h5>";
				return false;
			}
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
						elseif ($this->empty_string) {
							// not required, but we need a 0 value top option to signify nothing
							echo "<option value='0' >{$this->empty_string}</option>";
						}
						foreach ($options_all_articles as $tag) {
							$selected = "";
							if ($tag->id == $this->default) { $selected="selected";}
							echo "<option {$selected} value='{$tag->id}'>{$tag->title}</option>";
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
		$this->select_options = $config->select_options ?? [];
		$this->default = $config->default ?? '';
		$this->type = $config->type ?? 'error!!!';
		$this->content_type = $config->content_type ?? false;
		$this->empty_string = $config->empty_string ?? '';
		$this->tags = $config->tags ?? [];
		$this->list_unpublished = $config->list_unpublished ?? false;
		$this->logic = $config->logic ?? '';
	}

	public function get_friendly_value($custom_field_config) {
		// custom field config should contain content_type from config
		// this combined with the content id set in 'default' will help us get the specific content item from our tables
		$location = Content::get_content_location($custom_field_config->content_type);
		$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
		$table_name = "controller_" . $custom_fields->id ; 
		echo DB::fetch("select title from {$table_name} where id=?",$this->default)->title ?? "";
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}