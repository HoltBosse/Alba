<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_TagMultiple extends Field_Select {

	public $tag_cache = [];

	private function get_parent_tag($input) {
		if ($input->parent != 0) {
			if($this->tag_cache[$input->parent]) {
				return $this->tag_cache[$input->parent];
			} else {
				$parent_tag = DB::fetch("SELECT * FROM tags WHERE id=?", $input->parent);
				$this->tag_cache[$parent_tag->id] = $parent_tag;
				return $parent_tag;
			}
		} else {
			return (object) ["parent"=>0];
		}
	}

	private function make_tag_path($input) {
		$title = $input->title;
		while($input->parent != 0) {
			$parent_tag = $this->get_parent_tag($input);
			$title = $parent_tag->title . " > " . $title;
			$input = $parent_tag;
		}

		return $title;
	}

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->multiple = $config->multiple ?? true;
		$this->slimselect = $config->slimselect ?? true;
		$this->content_type = $config->content_type;

		$this->select_options = [];
		if ($this->content_type) {
			$tags = Content::get_applicable_tags ($this->content_type);
		}
		else {
			$tags = Tag::get_all_tags ();
		}
		$this->select_options = [];
		foreach($tags as $tag) {
			$this->select_options[] = (object) [
				"text"=>$this->make_tag_path($tag),
				"value"=>$tag->id,
				"disabled"=>$tag->state ? false : true
			];
		}
	}
}
