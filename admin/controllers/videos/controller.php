<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;


if (sizeof($segments)==0) {
	CMS::Instance()->queue_message('Unknown widget view','danger',Config::uripath().'/admin/');
}
if (sizeof($segments)==1) {
	$view = 'show';
}
else {
	if ($segments[1]=='show') {
		$view = 'show';
	}
	/* elseif ($segments[1]=='edit') {
		$view = 'edit';
	}
	elseif ($segments[1]=='action') {
		$view = 'action';
	}
	elseif ($segments[1]=='discover') {
		$view = 'discover';
	}
	elseif ($segments[1]=='api') {
		$view = 'api';
	}
	elseif ($segments[1]=='upload') {
		$view = 'upload';
	}
	elseif ($segments[1]=='uploadv2') {
		$view = 'uploadv2';
	} */
}

$controller = new Controller(realpath(dirname(__FILE__)),$view);
$controller->load_view($view);

