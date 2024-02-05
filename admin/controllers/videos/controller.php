<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;


if (sizeof($segments)==0) {
	CMS::Instance()->queue_message('Unknown widget view','danger',Config::uripath().'/admin/');
}
$view="show";
if ($segments[1]=='api') {
	$view = 'api';
}

$controller = new Controller(realpath(dirname(__FILE__)),$view);
$controller->load_view($view);

