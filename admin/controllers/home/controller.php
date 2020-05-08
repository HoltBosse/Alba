<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==1) {
	$view = 'default';
}
else {
	$view = 'default';
}

// load model + view

//CMS::queue_message('Test','success');

$home_controller = new Controller(realpath(dirname(__FILE__)),$view);
$home_controller->load_view($view);


