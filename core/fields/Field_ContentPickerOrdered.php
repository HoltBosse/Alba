<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentPickerOrdered extends Field_PickerOrdered {

	public $select_options;
	public $list_unpublished;
	public $tags;
	public $searchable;
	public $empty_string;

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
					$options_all_articles = DB::fetchAll(
						"SELECT id AS value, title AS text
						FROM {$table_name}
						WHERE state>={$min_state}
						ORDER BY title ASC"
					);
				}
				else {
					$tags_csv = "'".implode("','", $this->tags)."'";
					$options_all_articles = DB::fetchAll(
						"SELECT c.id AS value, c.title AS text
						FROM {$table_name} c
						WHERE c.state=1
						AND c.id IN (
							SELECT tc.content_id
							FROM tagged tc
							WHERE tc.content_type_id={$this->content_type}
							AND tc.tag_id IN (
								SELECT t.id
								FROM tags t
								WHERE t.state>={$min_state}
								AND t.alias IN ($tags_csv)
							)
						)
						ORDER BY c.title ASC"
					);
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