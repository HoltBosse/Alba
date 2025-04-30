<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;


if (sizeof($segments)==0) {
	CMS::Instance()->queue_message('Unknown widget view','danger',Config::uripath().'/admin/');
}

if (sizeof($segments)==1) {
	header("Location: " . Config::uripath() . "/admin/images/show");
	die;
} else {
	if ($segments[1]=='show') {
		$view = 'show';
	}
	elseif ($segments[1]=='edit') {
		$view = 'edit';
	}
	elseif ($segments[1]=='action') {
		$view = 'action';
	}
	elseif ($segments[1]=='api') {
		$view = 'api';
	}
	elseif ($segments[1]=='uploadv2') {
		$view = 'uploadv2';
	}
}

// load model + view

//CMS::queue_message('Test','success');

if ($view && is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$images_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$images_controller->load_view($view);
} else {
	CMS::raise_404();
}

