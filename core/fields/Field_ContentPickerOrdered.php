<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentPickerOrdered extends Field_PickerOrdered {

	public $select_options;
	public $list_unpublished;
	public $tags;
	public $searchable;
	public $empty_string;

	function __construct($content_type=1) {
		$this->id = "";
		$this->name = "";
		$this->default=$content_type;
		$this->list_unpublished=false;
		$this->tags=[];
	}

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->content_type = $config->content_type ?? 1; // default to articles if not provided
		$this->tags = $config->tags ?? [];
		$this->list_unpublished = $config->list_unpublished ?? false;

		if ($this->content_type) {
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
					$query = "select * from {$table_name} where state>={$min_state} order by title ASC";
					$options_all_articles = DB::fetchall("select id as value, title as text from {$table_name} where state>={$min_state} order by title ASC");
				}
				else {
					$tags_csv = "'".implode("','", $this->tags)."'";
					$query = "select c.id as value, c.title as text from {$table_name} c where c.state=1 ";
					$query .= " and c.id in (";
						$query .= " select tc.content_id from tagged tc where tc.content_type_id={$this->content_type} and tc.tag_id in (";
							$query .= "select t.id from tags t where t.state>={$min_state} and t.alias in ($tags_csv)";
						$query .= ")";
					$query .= ") order by c.title ASC";
					$options_all_articles = DB::fetchAll($query);
				}
			}
		}
		if (!$options_all_articles) {
			// content type was not able to be established
			if (Config::debug()) {
				echo "<h5>Error determining content type</h5>";
				return false;
			}
		}

		$this->select_options = $options_all_articles;
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}