<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_autoyear extends Plugin {

    public function init() {
        CMS::add_action("content_ready","insert_current_year"); // label, function, priority
        function insert_current_year($page_contents) {
            return str_replace('{YEAR}', date("Y"), $page_contents);
        }
    }

	public function render() {
		CMS::pprint_r ($this);
		echo "<hr>";
	}
}




