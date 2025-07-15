<?php

Use HoltBosse\Alba\Core\{CMS, Category, Content};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search','TEXT',null);

$content_type_filter = 0;
if (sizeof($segments)==3) {
	$content_type_filter = $segments[2];
}

$max_content_id = DB::fetch("SELECT MAX(id) AS id FROM content_types")->id;
if($content_type_filter < -3 || $content_type_filter > $max_content_id) {
	CMS::show_error("Invalid content type", 404);
}

$all_categories = Category::get_all_categories_by_depth($content_type_filter);
$all_content_types = Content::get_all_content_types();