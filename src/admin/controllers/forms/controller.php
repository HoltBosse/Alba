<?php

Use HoltBosse\Alba\Core\{CMS, Controller, File};

$view = CMS::Instance()->uri_segments[1] ?? "default";
if (is_dir(File::realpath(dirname(__FILE__) . "/views")) && is_dir(File::realpath(dirname(__FILE__) . "/views/$view"))) {
	$home_controller = new Controller(File::realpath(dirname(__FILE__)),$view);
	$home_controller->load_view($view);
} else {
	CMS::raise_404();
}