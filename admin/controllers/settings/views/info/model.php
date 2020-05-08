<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;
$native_zip = class_exists('ZipArchive',false);
$allow_fopen = ini_get('allow_url_fopen');
