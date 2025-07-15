<?php

Use HoltBosse\Alba\Core\{CMS, Controller};

// router

$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==1) {
	$view = 'default';
} else {
	$view = $segments[1];
}

// load model + view

if (is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$user_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$user_controller->load_view($view);
} else {
	CMS::raise_404();
}


