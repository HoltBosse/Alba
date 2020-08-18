<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_autoyear extends Plugin {
    public function init() {
        CMS::add_action("content_ready_frontend",$this,'replace_year_placeholder'); // label, function, priority  
    }

    public function replace_year_placeholder($page_contents, ...$args) {
        $override_year = $this->get_option('testoption');
        if ($override_year) {
            return str_replace($override_year, date("Y"), $page_contents);
        }
        else {
            return str_replace('{YEAR}', date("Y"), $page_contents);
        }
    }
}




