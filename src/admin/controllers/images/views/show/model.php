<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Content, File};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
if(sizeof($segments)>2) {
    CMS::raise_404();
}

$valid_image_types = [];
foreach(File::$image_types as $type => $value) {
    array_push($valid_image_types, "'$type'");
}

$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');
$cur_page = Input::getvar('page',v::IntVal(),'1');

$searchtext = Input::getvar('searchtext',v::StringVal(),null);

$query = "FROM media WHERE mimetype IN (" . implode(",", $valid_image_types) . ") ";
$params = [];
if ($searchtext) {
    $query .= " AND title LIKE ? OR alt LIKE ? OR filename LIKE ?";
    $params = ["%".$searchtext."%", "%".$searchtext."%", "%".$searchtext."%"];
}

$query .= " AND (domain=? OR domain IS NULL) ";
$params[] = ($_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']));

$all_images = DB::fetchAll("SELECT * " . $query . " LIMIT " . $pagination_size . " OFFSET " . ($cur_page-1)*$pagination_size, $params);
$images_count = DB::fetch("SELECT count(*) as count " . $query, $params)->count;

$image_tags = Content::get_applicable_tags(-1);

$filter = Input::getvar('filter',v::StringVal(),'');
$autoclose = Input::getvar('autoclose',v::StringVal(),'');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

