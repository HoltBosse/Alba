<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router
$view = 'default';

// load model + view
$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)>1) {
	if (!is_numeric($segments[1])) {
		$view = $segments[1];
	}
}

$user_controller = new Controller(realpath(dirname(__FILE__)),$view);
$user_controller->load_view($view);


