<?php

Use HoltBosse\Alba\Core\CMS;

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments) == 2 && file_get_contents(__DIR__ . "/js/" . $segments[1])) {
    // This is a special controller for JS files, not to be confused with the API controller
    header('Content-Type: application/javascript; charset=utf-8');
    echo file_get_contents(__DIR__ . "/js/" . $segments[1]);
    exit();
} elseif(sizeof($segments) > 2 && $segments[1]=="dist") {
    $newSegments = $segments;
    unset($newSegments[0]);
    unset($newSegments[1]);
    $newSegments = array_values($newSegments);

    $reqFile = __DIR__ . "/dist/" . implode("/", $newSegments);
    if(file_exists($reqFile)) {
        $mimetype = mime_content_type($reqFile);
        header("Content-Type: $mimetype");
        echo file_get_contents($reqFile);
        exit();
    } else {
        CMS::raise_404();
    }
}

CMS::raise_404();
