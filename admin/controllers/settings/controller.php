<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
$view = $segments[1];

if(sizeof($segments)==1) {
    header("Location: " . Config::uripath() . "/admin/settings/general");
    die;
}

if(sizeof($segments)>2 || !file_exists(CMSPATH . "/admin/controllers/settings/views/{$segments[1]}/model.php")) {
    CMS::raise_404();
}

// load model + view

$user_controller = new Controller(realpath(dirname(__FILE__)),$view);
$user_controller->load_view($view);


