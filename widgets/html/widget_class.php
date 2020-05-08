<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Widget_html extends Widget {
	public function render() {
		//CMS::pprint_r ($this);
		echo $this->options[0]->value;
	}
}