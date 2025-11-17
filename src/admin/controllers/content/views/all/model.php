<?php

Use HoltBosse\Alba\Core\{CMS, JSON, Controller, Configuration, Tag, Content, ContentSearch};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search','RAW',null);
$filters = Input::tuplesToAssoc( Input::getvar('filters','RAW',null) );
// make sure coretags getvar returns empty array PHP 8+ in_array required haystack to be array
$coretags = Input::getvar('coretags','ARRAYOFINT',[]);

$content_type_filter = null;
if (sizeof($segments)>3) {
	CMS::raise_404();
} elseif (sizeof($segments)==3) {
	$content_type_filter = $segments[2];
} else {
	CMS::show_error('Cannot determine content type to show');
}

$contentTypeTableRecord = DB::fetch("SELECT * FROM content_types WHERE id=?", $content_type_filter);
if(!$contentTypeTableRecord) {
	CMS::raise_404();
}

$cur_page = Input::getvar('page','INT','1');

$all_content_types = Content::get_all_content_types();
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');

// start new content search class call - experimental
$content_search = new ContentSearch();
$content_search->searchtext = $search;
$content_search->type_filter = $content_type_filter;
$content_search->page = $cur_page;

foreach($_GET as $key=>$value) {
	if(str_contains($key, "_order") && ($value=="asc" || $value=="desc")) {
		$content_search->order_by = str_replace("_order", "", $key);
		if($value=="asc") {
			$content_search->order_direction = "ASC";
		}
		//is desc by default
	}
}

if ($filters) {
	$content_search->filters = $filters;
	if($filters["state"]) {
		$content_search->disable_builtin_state_check = true;
	}
}
if ($coretags) {
	$content_search->tags = $coretags;
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
$custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($location) . '/custom_fields.json');
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

function make_sortable_header($title) {
	?>
		<th>
			<label class="orderablerow">
				<span><?php echo ucwords($title); ?></span>
				<i class="tableorder fas fa-sort"></i>
				<i class="tableorder fas fa-sort-up"></i>
				<i class="tableorder fas fa-sort-down"></i>
			</label>
			<?php $selectedTitle = Input::getVar(str_replace(" ", "_", $title) . "_order", v::StringVal()->in(["asc", "desc", "regular"]), "regular");?>
			<input type="radio" name="<?php echo str_replace(" ", "_", $title); ?>_order" form="orderform" value="regular" <?php echo $selectedTitle=="regular" ? "checked" : ""; ?> />
			<input type="radio" name="<?php echo str_replace(" ", "_", $title); ?>_order" form="orderform" value="asc" <?php echo $selectedTitle=="asc" ? "checked" : ""; ?> />
			<input type="radio" name="<?php echo str_replace(" ", "_", $title); ?>_order" form="orderform" value="desc" <?php echo $selectedTitle=="desc" ? "checked" : ""; ?> />
		</th>
	<?php
}

