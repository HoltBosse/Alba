<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Content, File, Hook, Form};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
if(sizeof($segments)>2) {
    CMS::raise_404();
}

$searchFormObject = json_decode(File::getContents(__DIR__ . "/search_form.json"));
$searchFormObject->fields[] = (object) [
	"type"=>"Html",
	"html"=>"<div style='display: flex; gap: 1rem;'>
				<button class='button is-info' type='submit'>Submit</button>
				<button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
			</div>"
];

$searchFormObject = Hook::execute_hook_filters('admin_search_form_object', $searchFormObject);

$searchForm = new Form($searchFormObject);

if($searchForm->isSubmitted()) {
	$searchForm->setFromSubmit();
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

$image_tags = array_values(array_filter($image_tags, function($tag) {
    return ($tag->domain === null || $tag->domain == $_SESSION["current_domain"]) && $tag->state > 0;
}));

$filter = Input::getvar('filter',v::StringVal(),'');
$autoclose = Input::getvar('autoclose',v::StringVal(),'');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

