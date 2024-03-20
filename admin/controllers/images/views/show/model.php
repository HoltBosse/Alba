<?php
defined('CMSPATH') or die; // prevent unauthorized access

//add the image editor
Image::add_image_js_editor();

$valid_image_types = [];
foreach(File::$image_types as $type => $value) {
    array_push($valid_image_types, "'$type'");
}

$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');
$cur_page = Input::getvar('page','INT','1');

$searchtext = Input::getvar('searchtext','TEXT',null);

$query = "FROM media WHERE mimetype IN (" . implode(",", $valid_image_types) . ") ";
$params = [];
if ($searchtext) {
    $query .= " AND title LIKE ? OR alt LIKE ? OR filename LIKE ?";
    $params = ["%".$searchtext."%", "%".$searchtext."%", "%".$searchtext."%"];
}

$all_images = DB::fetchall("SELECT * " . $query . " LIMIT " . $pagination_size . " OFFSET " . ($cur_page-1)*$pagination_size, $params);
$images_count = DB::fetch("SELECT count(*) as count " . $query, $params)->count;

$image_tags = Content::get_applicable_tags ("-1");

$filter = Input::getvar('filter','STRING');
$autoclose = Input::getvar('autoclose','STRING');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

