<?php
defined('CMSPATH') or die; // prevent unauthorized access

// $this->page_contents;

// TODO: proper plugin interface / API

// Example plugin functions a la wordpress

// hook into test_hook and add action 'test_action'
$this->add_action("test_hook","test_action",1,1); // label, function, priority, arg count
function test_action($arg1) {
    echo "<h1>Test Hook {YEAR} - got var {$arg1}</h1>";
}

// hook into content_ready hook and add action to replace {YEAR} in page contents with current year string
// replaces old method of just accessing the public variable page contents in the CMS class...
//$this->page_contents = str_replace('{YEAR}', date("Y"), $this->page_contents);
$this->add_action("content_ready","insert_current_year",1,1); // label, function, priority, arg count
function insert_current_year($page_contents) {
    return str_replace('{YEAR}', date("Y"), $page_contents);
}