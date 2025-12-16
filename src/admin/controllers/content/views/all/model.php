<?php

Use HoltBosse\Alba\Core\{CMS, JSON, Controller, Configuration, Tag, Content, ContentSearch};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search',v::StringVal(),null);
$filters = Input::tuplesToAssoc( Input::getvar('filters',v::AlwaysValid(),null) );
// make sure coretags getvar returns empty array PHP 8+ in_array required haystack to be array
$coretags = Input::getvar('coretags',v::arrayType()->each(v::intVal()),[]);

$content_type_filter = null;
if (sizeof($segments)>3) {
	CMS::raise_404();
} elseif (sizeof($segments)==3) {
	$content_type_filter = $segments[2];
} else {
	CMS::show_error('Cannot determine content type to show');
}

if(!Content::isAccessibleOnDomain($content_type_filter, $_SESSION["current_domain"])) {
	CMS::raise_404();
}

$contentTypeTableRecord = DB::fetch("SELECT * FROM content_types WHERE id=?", $content_type_filter);
if(!$contentTypeTableRecord) {
	CMS::raise_404();
}

$custom_fields = false; //i have no idea why this exists, moved from below
$location = Content::get_content_location($content_type_filter);
$custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($location) . '/custom_fields.json');

$cur_page = Input::getvar('page',v::IntVal(),'1');

$all_content_types = Content::get_all_content_types();
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');

// start new content search class call - experimental
$content_search = new ContentSearch();
$content_search->searchtext = $search;
$content_search->type_filter = $content_type_filter;
$content_search->page = $cur_page;

$domain = $_SESSION["current_domain"];
/* if(isset($custom_fields->multi_domain_shared_instances) && $custom_fields->multi_domain_shared_instances===true) {
	$domain = null;
} */

//even if in shared mode, specific domain view as the search looks for null (all domains) or specific domain
$content_search->domain = $domain;

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

$applicable_users = DB::fetchAll('SELECT id,username FROM users WHERE domain=? ORDER BY username ASC', [$_SESSION["current_domain"]]);
$applicable_categories = DB::fetchAll('SELECT * FROM categories WHERE content_type=? AND (domain=? OR domain IS NULL) ORDER BY title ASC', [$content_type_filter, $_SESSION["current_domain"]]);
$applicable_tags = Tag::get_tags_available_for_content_type ($content_type_filter);

// handle custom optional listing on content specific 'all' view

$content_list_fields = [];


// get listing fields for content type based on custom_fields_json list field
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

$hidden_list_fields = isset($custom_fields->hide) ? $custom_fields->hide : [];

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

