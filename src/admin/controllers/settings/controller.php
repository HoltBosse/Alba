<?php

Use HoltBosse\Alba\Core\{CMS, Controller, File};

// router

$segments = CMS::Instance()->uri_segments;
$view = $segments[1];

if(sizeof($segments)==1) {
    header("Location: " . $_ENV["uripath"] . "/admin/settings/general");
    die;
}

if(sizeof($segments)>2 || !file_exists(__DIR__ . "/views/{$segments[1]}/model.php")) {
    CMS::raise_404();
}

// load model + view

$user_controller = new Controller(File::realpath(dirname(__FILE__)),$view);
$user_controller->load_view($view);


