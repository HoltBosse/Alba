<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
$content_type_filter=null;

if (sizeof($segments)==1) {
	$view = 'all';
}
else {
	$view = $segments[1];
}


$content_type_controller = new Controller(realpath(dirname(__FILE__)),$view);
$content_type_controller->load_view($view);


