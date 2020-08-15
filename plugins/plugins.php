<?php
defined('CMSPATH') or die; // prevent unauthorized access

// $this->page_contents;

// TODO: proper plugin interface / API

$this->page_contents = str_replace('{YEAR}', date("Y"), $this->page_contents);

CMS::Instance()->add_action('test_action',"foo_function");
function foo_function() {
    echo "<h1>TEST_ACTION TEST PLUGIN</h1>";
}