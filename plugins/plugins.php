<?php
defined('CMSPATH') or die; // prevent unauthorized access

// $this->page_contents;

// TODO: proper plugin interface / API

$this->page_contents = str_replace('{YEAR}', date("Y"), $this->page_contents);

CMS::Instance()->add_action("test_hook","test_action",1,0); // label, function, priorty, arg count
function test_action($args) {
    echo "<h1>Test Hook - got var {$args[0]}</h1>";
}