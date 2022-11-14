<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;
$order_by = Input::getvar('order_by','STRING');
$search = Input::getvar('search','TEXT',null);

$content_type_filter = 0;
if (sizeof($segments)==3) {
	$content_type_filter = $segments[2];
}


$all_categories = Category::get_all_categories_by_depth($content_type_filter);
$all_content_types = Content::get_all_content_types();
$category_count = Category::get_category_count($content_type_filter, $search);
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');