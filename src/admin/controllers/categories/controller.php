<?php

Use HoltBosse\Alba\Core\{CMS, Controller};

// router

$segments = CMS::Instance()->uri_segments;
$content_type_filter=null;

if (sizeof($segments)==1) {
	header("Location: " . $_ENV["uripath"] . "/admin/categories/all");
    die;
}
$view = $segments[1];

if((sizeof($segments)>3 && ($view!="action" && $view!="edit")) || !file_exists(__DIR__ . "/views/{$segments[1]}/model.php")) {
	CMS::raise_404();
}

$content_type_controller = new Controller(realpath(dirname(__FILE__)),$view);
$content_type_controller->load_view($view);


