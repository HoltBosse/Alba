<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_TagParent extends Field_Select {

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->content_type = $config->content_type ?? false;

		if ($this->content_type) {
			$tags = Content::get_applicable_tags ($this->content_type);
		}
		else {
			//$tags = Tag::get_all_tags ();
			$tags = Tag::get_all_tags_by_depth();
		}

		$this->select_options = [];
		foreach($tags as $tag) {
			$tag_title_prefix = "";
			for ($n=0; $n<$tag->depth; $n++) {
				$tag_title_prefix .= "&nbsp-&nbsp";
			}

			$this->select_options[] = (object) [
				"text"=>"$tag_title_prefix $tag->title",
				"value"=>$tag->id,
			];
		}
		array_unshift(
			$this->select_options,
			(object) [
				"text"=>"None",
				"value"=>0,
			]
		);
	}
}