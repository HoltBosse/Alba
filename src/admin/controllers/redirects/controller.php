<?php

Use HoltBosse\Alba\Core\{CMS, Controller, File};

// router

$segments = CMS::Instance()->uri_segments;
$content_type_filter=null;

if (sizeof($segments)==0) {
	CMS::Instance()->queue_message('Unknown redirect operation','danger', $_ENV["uripath"] . '/admin');
} elseif (sizeof($segments)==1) {
	$view = 'all';
} else {
	$view = $segments[1];
}

if (is_dir(File::realpath(dirname(__FILE__) . "/views")) && is_dir(File::realpath(dirname(__FILE__) . "/views/$view"))) {
	$content_type_controller = new Controller(File::realpath(dirname(__FILE__)),$view);
	$content_type_controller->load_view($view);
} else {
	CMS::raise_404();
}


