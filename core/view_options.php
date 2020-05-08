<?php

defined('CMSPATH') or die; // prevent unauthorized access

class View_Options {
	public $component_path;
	public $component_view;

	public function __construct($component_path, $component_view, $options) {
		$this->component_path = $component_path;
		$this->component_view = $component_view;
		$this->options = json_decode($options);
	}

	public function get_content_info() {
		if ($this->component_path=='basic_article') {
			if ($this->component_view=='single') {
				// first and only option is article ID
				$content_title = CMS::Instance()->pdo->query('select title from content where id=' . $this->options[0])->fetch();
				return $content_title->title;
			}
			if ($this->component_view=='blog') {
				// option 1 is tag, option 2 is num per page
				$content_title = CMS::Instance()->pdo->query('select title from content where id=' . $this->options[0])->fetch();
				return $content_title->title;
			}
		}
		return "";
	}

}