<?php

Use HoltBosse\Alba\Core\CMS;

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments) == 2 && file_get_contents(__DIR__ . "/js/" . $segments[1])) {
    // This is a special controller for JS files, not to be confused with the API controller
    header('Content-Type: application/javascript; charset=utf-8');
    echo file_get_contents(__DIR__ . "/js/" . $segments[1]);
    exit();
}

CMS::raise_404();
