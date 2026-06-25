<?php

Use HoltBosse\Alba\Core\{CMS, File};

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments) == 2) {
    // This is a special controller for JS files, not to be confused with the API controller
    $jsBase = realpath(__DIR__ . "/js");
    $jsFile = realpath(__DIR__ . "/js/" . $segments[1]);
    if($jsBase !== false && $jsFile !== false && str_starts_with($jsFile, $jsBase . DIRECTORY_SEPARATOR) && File::getContents($jsFile)) {
        header('Content-Type: application/javascript; charset=utf-8');
        echo File::getContents($jsFile);
        exit();
    }
} elseif(sizeof($segments) > 2 && $segments[1]=="dist") {
    $newSegments = $segments;
    unset($newSegments[0]);
    unset($newSegments[1]);
    $newSegments = array_values($newSegments);

    $distBase = realpath(__DIR__ . "/dist");
    $reqFile = realpath(__DIR__ . "/dist/" . implode("/", $newSegments));
    if($distBase !== false && $reqFile !== false && str_starts_with($reqFile, $distBase . DIRECTORY_SEPARATOR)) {
        header("Content-Type: application/javascript; charset=utf-8");
        echo File::getContents($reqFile);
        exit();
    } else {
        CMS::raise_404();
    }
}

CMS::raise_404();
