<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_TagSingle extends Field_Select {

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->content_type = $config->content_type ?? false;

		if ($this->content_type) {
			$tags = Content::get_applicable_tags ($this->content_type);
		}
		else {
			$tags = Tag::get_all_tags ();
		}

		$this->select_options = [];
		foreach($tags as $tag) {
			$this->select_options[] = (object) [
				"text"=>$tag->title,
				"value"=>$tag->id,
			];
		}
	}
}