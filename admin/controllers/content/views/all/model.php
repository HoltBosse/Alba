<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;
$order_by = Input::getvar('order_by','STRING');
$search = Input::getvar('search','TEXT',null);
$filters = Input::tuples_to_assoc( Input::getvar('filters','RAW',null) );
// make sure coretags getvar returns empty array PHP 8+ in_array required haystack to be array
$coretags = Input::getvar('coretags','ARRAYOFINT',[]);

$content_type_filter = null;
if (sizeof($segments)==3) {
	$content_type_filter = $segments[2];
}
else {
	CMS::show_error('Cannot determine content type to show');
}

if (!$order_by) {
	$cur_page = Input::getvar('page','INT','1');
}
else {
	// ordering view, DO NOT LIMIT OR PAGINATE
	$cur_page = null;
}

$table_name = Content::get_table_name_for_content_type($content_type_filter); 

// og all content call - comment out experimental section below to restore
// and uncomment next 3 lines
//$all_content = Content::get_all_content($order_by, $content_type_filter, null, null, null, [], [], null, null, $cur_page, $search);
// og content count
//$content_count = Content::get_content_count($content_type_filter, $search, -1);

$all_content_types = Content::get_all_content_types();
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');

// start new content search class call - experimental
$content_search = new Content_Search();
$content_search->searchtext = $search;
$content_search->type_filter = $content_type_filter;
$content_search->page = $cur_page;

if ($filters) {
	$content_search->filters = $filters;
}
if ($coretags) {
	$content_search->tags = $coretags;
}
if ($order_by) {
	$content_search->order_by = "ordering";
	$content_search->order_direction = "ASC";
	$content_search->page_size = 99999999; // silly large number
	$content_search->page = 1; // always one for ordering view
}
$all_content = $content_search->exec();
$content_count = $content_search->get_count();

// end new conten search class - experimental

// get filter values for dropdowns etc

$applicable_users = DB::fetchAll('select id,username from users order by username asc');
if ($content_type_filter) {
	$applicable_categories = DB::fetchAll('select * from categories where content_type=? order by title asc',$content_type_filter);
	$applicable_tags = Tag::get_tags_available_for_content_type ($content_type_filter);
}
else {
	$applicable_categories = DB::fetchAll('select * from categories order by title asc');
	$applicable_tags = DB::fetchAll('select * from tags order by title asc');
}


// handle custom optional listing on content specific 'all' view

$content_list_fields = [];
$custom_fields = false;


// get listing fields for content type based on custom_fields_json list field
$location = Content::get_content_location($content_type_filter);
//$custom_fields = json_decode(file_get_contents (CMSPATH . '/controllers/' . $location . '/custom_fields.json'));
$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
// create easy to access field array based on 'name' property
// useful, for example, in field get_friendly_value function call
$named_custom_fields = array_column($custom_fields->fields, null, 'name');

if (property_exists($custom_fields,'list')) {
	// create content_list_fields
	// TODO: for performance, pre-calculate the content type table names for 
	// fast lookups in any get_friendly_value calls in fields
	foreach ($custom_fields->list as $custom_field_name) {
		//$content_list_fields = $custom_fields->list;
		$custom_fields_list_item = new stdClass();
		$custom_fields_list_item->name = $custom_field_name;
		// get label and type
		foreach ($custom_fields->fields as $field) {
			if ($field->name==$custom_field_name) {
				$custom_fields_list_item->label = $field->label;
				$custom_fields_list_item->type = $field->type;
			}
		}
		$content_list_fields[] = $custom_fields_list_item;
	}
	
}

