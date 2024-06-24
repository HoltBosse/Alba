<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Contentselector extends Field_Select {

	public $list_unpublished;
	public $tags;

	public function get_friendly_value($helpful_info) {
		// content_type already checked for being numeric in load_from_config function
		$content_type = $this->content_type ?? $helpful_info->content_type;
		$table_name = Content::get_table_name_for_content_type($content_type);
		$field_value = json_decode($this->default);
		if (is_numeric($this->default)) {
			$query = 'SELECT `title` AS val FROM `' . $table_name . '` WHERE id=?';
			$val = DB::fetch($query, $this->default)->val ?? false;
			if ($val) {
				return $val;
			}
		}
		elseif (is_array($this->default)) {
			$title_arr = [];
			foreach ($this->default as $content_id) {
				$query = 'SELECT `title` AS val FROM `' . $table_name . '` WHERE id=?';
				$val = DB::fetch($query, $content_id)->val ?? false;
				if ($val) {
					$title_arr[] = $val;
				}
			}
			return explode(", ", $title_arr);
		}
		else {
			return $this->default;
		}
	}

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->content_type = $config->content_type ?? false;
		$this->list_unpublished = $config->list_unpublished ?? false;
		$this->tags = $config->tags ?? false;

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

			$min_state = $this->list_unpublished ? 0 : 1;

			if (!$this->tags) {
				// default order is alphabetical
				$options_all_articles = DB::fetchall("SELECT id AS value, title AS text FROM `$table_name` WHERE state >={$min_state} ORDER BY title ASC");
				//CMS::pprint_r ($options_all_articles);
			}
			else {
				$tags_csv = "'".implode("','", $this->tags)."'";
				$options_all_articles = DB::fetchAll(
					"SELECT id AS value, title AS text
					FROM {$table_name} c
					WHERE c.state=1
					AND c.id in (
						SELECT tc.content_id
						FROM tagged tc
						WHERE tc.content_type_id={$this->content_type}
						AND tc.tag_id IN (
							SELECT t.id
							FROM tags t
							WHERE t.state>={$min_state}
							AND t.alias IN ($tags_csv)
						)
					) ORDER BY c.title ASC"
				);
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

}