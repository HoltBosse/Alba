<?php
defined('CMSPATH') or die; // prevent unauthorized access

$valid_image_types = [
    "'image/jpeg'", "'image/webp'", "'image/png'", "'image/svg+xml'", "'image/svg'"
];

$all_images = DB::fetchall("select * from media where mimetype in (" . implode(",", $valid_image_types) . ")");

$image_tags = Content::get_applicable_tags ("-1");

$filter = Input::getvar('filter','STRING');
$autoclose = Input::getvar('autoclose','STRING');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

