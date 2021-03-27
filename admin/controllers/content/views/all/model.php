<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;
$order_by = Input::getvar('order_by','STRING');
$search = Input::getvar('search','TEXT',null);

$content_type_filter = null;
if (sizeof($segments)==3) {
	$content_type_filter = $segments[2];
}

if (!$order_by) {
	$cur_page = Input::getvar('page','INT','1');
}
else {
	// ordering view, DO NOT LIMIT OR PAGINATE
	$cur_page = null;
}

$all_content = Content::get_all_content($order_by, $content_type_filter, null, null, null, [], [], null, null, $cur_page, $search);
$all_content_types = Content::get_all_content_types();
$content_count = Content::get_content_count($content_type_filter, $search);
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');

// handle custom optional listing on content specific 'all' view

$content_list_fields = [];
$custom_fields = false;

if ($content_type_filter) {
	// get listing fields for content type based on custom_fields_json list field
	$location = Content::get_content_location($content_type_filter);
	//$custom_fields = json_decode(file_get_contents (CMSPATH . '/controllers/' . $location . '/custom_fields.json'));
	$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
	
	if (property_exists($custom_fields,'list')) {
		// create content_list_fields
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
}
