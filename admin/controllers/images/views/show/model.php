<?php
defined('CMSPATH') or die; // prevent unauthorized access

//add the image editor
Image::add_image_js_editor();

$valid_image_types = [];
foreach(File::$image_types as $type => $value) {
    array_push($valid_image_types, "'$type'");
}


$searchtext = Input::getvar('searchtext','TEXT',null);

$query = "select * from media where mimetype in (" . implode(",", $valid_image_types) . ") ";
if ($searchtext) {
    $query .= " and title like ? or alt like ? or filename like ?";
    $all_images = DB::fetchall($query, ["%".$searchtext."%", "%".$searchtext."%", "%".$searchtext."%"]);
}
else {
    $all_images = DB::fetchall($query);
}


$image_tags = Content::get_applicable_tags ("-1");

$filter = Input::getvar('filter','STRING');
$autoclose = Input::getvar('autoclose','STRING');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

