<?php
defined('CMSPATH') or die; // prevent unauthorized access

// $this->page_contents;

// TODO: proper plugin interface / API

$this->page_contents = str_replace('{YEAR}', date("Y"), $this->page_contents);