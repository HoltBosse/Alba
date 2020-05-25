<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
$view_id = CMS::Instance()->page->view;
CMS::Instance()->page->view_configuration_object = json_decode(CMS::Instance()->page->view_configuration);

if (Config::$debug) {
	echo "<hr>";
	echo "<h5>View Config:</h5>";
	CMS::pprint_r (CMS::Instance()->page->view_configuration_object);
	echo "<hr>";
}

$view = Content::get_view_location(CMS::Instance()->page->view);

$controller = new Controller(realpath(dirname(__FILE__)),$view);
$controller->load_view($view); 


