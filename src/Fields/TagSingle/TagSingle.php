<?php
namespace HoltBosse\Alba\Fields\TagSingle;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\Alba\Core\{Content, Tag, CMS};
Use HoltBosse\DB\DB;
Use \stdClass;

class TagSingle extends Select {

	public mixed $tag_cache = [];
	public mixed $content_type;
	public mixed $domain;

	private function get_parent_tag(mixed $input): stdClass {
		if ($input->parent != 0) {
			if(isset($this->tag_cache[$input->parent])) {
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

	private function make_tag_path(stdClass $input): string {
		$title = $input->title;
		while($input->parent != 0) {
			$parent_tag = $this->get_parent_tag($input);
			$title = $parent_tag->title . " > " . $title;
			$input = $parent_tag;
		}

		return $title;
	}

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$this->content_type = $config->content_type ?? null;
		$this->domain = $config->domain ?? $_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']);

		if ($this->content_type) {
			if(!is_numeric($this->content_type)) {
				$this->content_type = Content::get_content_type_id($this->content_type);
			}

			$tags = Content::get_applicable_tags((int) $this->content_type);
		} else {
			$tags = Tag::get_all_tags ();
		}

		$tags = array_values(array_filter($tags, function($tag) {
			return ($tag->domain === null || $tag->domain == $this->domain);
		}));

		$this->select_options = [];
		foreach($tags as $tag) {
			$this->select_options[] = (object) [
				"text"=>$this->make_tag_path($tag),
				"value"=>$tag->id,
				"disabled"=>$tag->state ? false : true
			];
		}

		return $this;
	}
}