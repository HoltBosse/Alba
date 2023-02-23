<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;


if (sizeof($segments)==0) {
	CMS::Instance()->queue_message('Unknown widget view','danger',Config::uripath().'/admin/');
}
$view="show";

$controller = new Controller(realpath(dirname(__FILE__)),$view);
$controller->load_view($view);

