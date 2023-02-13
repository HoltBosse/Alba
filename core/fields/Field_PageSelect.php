<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_PageSelect extends Field_Select {

	public function load_from_config($config) {
		parent::load_from_config($config);

		$page_array = [];
		foreach(Page::get_all_pages_by_depth() as $page) {
			$page_prefix="";
			for ($n=0; $n<$page->depth; $n++) {
				$page_prefix .= "&nbsp;-&nbsp;";
			}
			$page_array[] = (object) [
				"text" => $page_prefix . $page->title,
				"value" => $page->id,
			];
		}

		$this->select_options = $page_array;
	}

}
