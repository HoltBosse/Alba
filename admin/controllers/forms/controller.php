<?php
defined('CMSPATH') or die; // prevent unauthorized access

$view = CMS::Instance()->uri_segments[1] ?? "default";
if (is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$home_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$home_controller->load_view($view);
} else {
	CMS::raise_404();
}