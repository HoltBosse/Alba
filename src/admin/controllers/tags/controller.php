<?php

Use HoltBosse\Alba\Core\{CMS, Controller, File};
// router

$segments = CMS::Instance()->uri_segments;


if (sizeof($segments)==0) {
	CMS::Instance()->queue_message('Unknown widget view','danger',$_ENV["uripath"].'/admin/');
}

if (sizeof($segments)==1) {
	$view = 'show';
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
}

// load model + view

//CMS::queue_message('Test','success');
if ($view && is_dir(File::realpath(dirname(__FILE__) . "/views")) && is_dir(File::realpath(dirname(__FILE__) . "/views/$view"))) {
	$tags_controller = new Controller(File::realpath(dirname(__FILE__)),$view);
	$tags_controller->load_view($view);
} else {
	CMS::raise_404();
}

