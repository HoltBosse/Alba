<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
$content_type_filter=null;

if (sizeof($segments)==1) {
	header("Location: " . Config::uripath() . "/admin/categories/all");
    die;
}
$view = $segments[1];

if((sizeof($segments)>3 && $view!="action") || !file_exists(CMSPATH . "/admin/controllers/categories/views/{$segments[1]}/model.php")) {
	CMS::raise_404();
}

$content_type_controller = new Controller(realpath(dirname(__FILE__)),$view);
$content_type_controller->load_view($view);


