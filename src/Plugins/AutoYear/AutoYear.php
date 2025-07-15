<?php
namespace HoltBosse\Alba\Plugins\AutoYear;

use HoltBosse\Alba\Core\{CMS, Plugin};

class AutoYear extends Plugin {
    public function init() {
        CMS::add_action("content_ready_frontend",$this,'replace_year_placeholder'); // label, function, priority  
    }

    public function replace_year_placeholder($page_contents, ...$args) {
        // FILTER
        $override_year = $this->get_option('testoption');
        if ($override_year) {
            return str_replace($override_year, date("Y"), $page_contents);
        }
        else {
            return str_replace('{YEAR}', date("Y"), $page_contents);
        }
    }
}




