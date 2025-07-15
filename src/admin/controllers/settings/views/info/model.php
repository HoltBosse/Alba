<?php

Use HoltBosse\Alba\Core\{CMS, Mail};

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;
$native_zip = class_exists('ZipArchive',false);
$allow_fopen = ini_get('allow_url_fopen');
$gd_available = function_exists ('imagecreatefromjpeg');
$virtual_available = function_exists ('virtual');
$mail_available = Mail::is_available();
$mysqldump_available = `which mysqldump`;
$curl_available = function_exists('curl_version');

