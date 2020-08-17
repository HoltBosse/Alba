<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_autoyear extends Plugin {
    public function init() {
        CMS::add_action("content_ready",$this); // label, function, priority  
    }

    public function execute_filter($page_contents, ...$args) {
        $override_year = $this->get_option('testoption');
        if ($override_year) {
            return str_replace('{YEAR}', date($override_year), $page_contents);
        }
        else {
            return str_replace('{YEAR}', date("Y"), $page_contents);
        }
    }
}




