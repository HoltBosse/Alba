<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
$content_type_filter=null;

if (sizeof($segments)==0) {
	CMS::Instance()->queue_message('Unknown redirect operation','danger', Config::uripath() . '/admin');
} elseif (sizeof($segments)==1) {
	$view = 'all';
} else {
	$view = $segments[1];
}

if (is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$content_type_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$content_type_controller->load_view($view);
} else {
	CMS::raise_404();
}


