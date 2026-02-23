<?php
namespace HoltBosse\Alba\Fields\PageSelect;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\Alba\Core\Page;

class PageSelect extends Select {

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$page_array = [];
		foreach(Page::get_all_pages_by_depth() as $page) {
			$page_prefix="";
			for ($n=0; $n<$page->depth; $n++) {
				$page_prefix .= " - ";
			}
			$page_array[] = (object) [
				"text" => $page_prefix . $page->title,
				"value" => $page->id,
			];
		}

		$this->select_options = $page_array;

		return $this;
	}

}
