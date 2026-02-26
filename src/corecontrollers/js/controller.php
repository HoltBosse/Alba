<?php

Use HoltBosse\Alba\Core\{CMS, File};

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments) == 2 && File::getContents(__DIR__ . "/js/" . $segments[1])) {
    // This is a special controller for JS files, not to be confused with the API controller
    header('Content-Type: application/javascript; charset=utf-8');
    echo File::getContents(__DIR__ . "/js/" . $segments[1]);
    exit();
} elseif(sizeof($segments) > 2 && $segments[1]=="dist") {
    $newSegments = $segments;
    unset($newSegments[0]);
    unset($newSegments[1]);
    $newSegments = array_values($newSegments);

    $reqFile = __DIR__ . "/dist/" . implode("/", $newSegments);
    if(file_exists($reqFile)) {
        header("Content-Type: application/javascript; charset=utf-8");
        echo File::getContents($reqFile);
        exit();
    } else {
        CMS::raise_404();
    }
}

CMS::raise_404();
