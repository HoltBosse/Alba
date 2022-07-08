<?php
defined('CMSPATH') or die; // prevent unauthorized access

// create views directory and a 'default' view directory with model 
// and view files if needed - otherwise homepage will be empty

$view = "default";
if (is_dir(realpath(dirname(__FILE__) . "/views"))) {
	$home_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$home_controller->load_view($view);
}


