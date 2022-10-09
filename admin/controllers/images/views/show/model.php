<?php
defined('CMSPATH') or die; // prevent unauthorized access

$valid_image_types = [];
foreach(File::$image_types as $type => $value) {
    array_push($valid_image_types, "'$type'");
}

$all_images = DB::fetchall("select * from media where mimetype in (" . implode(",", $valid_image_types) . ") ORDER BY id DESC");

$image_tags = Content::get_applicable_tags ("-1");

$filter = Input::getvar('filter','STRING');
$autoclose = Input::getvar('autoclose','STRING');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

