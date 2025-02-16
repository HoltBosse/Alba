<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

if (sizeof($segments)!==3) {
    CMS::show_error('Cannot determine content type to order');
}

$content_type = $segments[2];
if (!is_numeric($content_type)) {
    CMS::show_error('Invalid content type');
}

function clean_ordering($table_name) {
	// fix table that hasn't been manually ordered at all previously
	// guarantees unique sensible ordering
	// 1 based, not 0 based
	$sql = "
	WITH ordered_items AS (
		SELECT id, ordering,
		ROW_NUMBER() OVER (ORDER BY ordering, id) AS new_ordering
		FROM $table_name
	)
	UPDATE $table_name
	JOIN ordered_items ON $table_name.id = ordered_items.id
	SET $table_name.ordering = ordered_items.new_ordering;
	";
	DB::exec($sql);
}

$location = Content::get_content_location($content_type);
$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
$content_list_fields = [];
// create easy to access field array based on 'name' property
// useful, for example, in field get_friendly_value function call
$named_custom_fields = array_column($custom_fields->fields, null, 'name');

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

// TODO: limit to $content_list_fields + needed for ordering view (id,ordering,title)
$content_table = Content::get_table_name_for_content_type($content_type);
clean_ordering($content_table); // ensure correct ordering pre-manipulation
$all_content = DB::fetchAll("SELECT * FROM $content_table WHERE state<>-1 ORDER BY ordering ASC, id ASC");
