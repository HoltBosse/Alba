<?php

Use HoltBosse\Alba\Core\{CMS, Controller};

// create views directory and a 'default' view directory with model 
// and view files if needed - otherwise homepage will be empty

$view = CMS::Instance()->uri_segments[1] ?? "default";
if (is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$home_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$home_controller->load_view($view);
} else {
	CMS::raise_404();
}


