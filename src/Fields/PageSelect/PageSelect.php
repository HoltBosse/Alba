<?php
namespace HoltBosse\Alba\Fields\PageSelect;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\Alba\Core\Page;
Use HoltBosse\DB\DB;
Use \stdClass;

class PageSelect extends Select {
	private mixed $page_cache = [];

	private function get_parent_page(mixed $input): stdClass {
		if ($input->parent != -1 && $input->parent !== null) {
			if (isset($this->page_cache[$input->parent])) {
				return $this->page_cache[$input->parent];
			} else {
				$parent_page = DB::fetch("SELECT * FROM pages WHERE id=?", [$input->parent]);
				$this->page_cache[$parent_page->id] = $parent_page;
				return $parent_page;
			}
		} else {
			return (object) ["parent"=>-1];
		}
	}

	private function make_page_path(stdClass $input): string {
		$title = $input->title;
		while ($input->parent != -1 && $input->parent !== null) {
			$parent = $this->get_parent_page($input);
			$title = $parent->title . " > " . $title;
			$input = $parent;
		}

		return $title;
	}

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$page_array = [];
		foreach (Page::get_all_pages_by_depth() as $page) {
			$page_array[] = (object) [
				"text" => $this->make_page_path($page),
				"value" => $page->id,
			];
		}

		$this->select_options = $page_array;

		return $this;
	}

}
