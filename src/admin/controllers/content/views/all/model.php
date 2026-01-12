<?php

Use HoltBosse\Alba\Core\{CMS, JSON, Controller, Configuration, Tag, Content, ContentSearch, Form, Hook, HookQueryResult};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search',v::StringVal(),null);
//$filters = Input::tuplesToAssoc( Input::getvar('filters',v::AlwaysValid(),null) );
// make sure coretags getvar returns empty array PHP 8+ in_array required haystack to be array
$coretags = Input::getvar('tagged',v::arrayType()->each(v::intVal()),[]);

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
$applicable_tags = Tag::get_tags_available_for_content_type ($content_type_filter);
$applicable_users = DB::fetchAll('SELECT id,username FROM users WHERE domain=? ORDER BY username ASC', [$_SESSION["current_domain"]]);
$applicable_categories = DB::fetchAll('SELECT * FROM categories WHERE content_type=? AND (domain=? OR domain IS NULL) ORDER BY title ASC', [$content_type_filter, $_SESSION["current_domain"]]);

$searchFormObject = json_decode(file_get_contents(__DIR__ . "/search_form.json"));

$searchFormObject->fields[] = (object) [
	"type"=>"Html",
	"html"=>"<div style='display: flex; gap: 1rem;'>
				<button class='button is-info' type='submit'>Submit</button>
				<button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
			</div>"
];

$searchFormObject = Hook::execute_hook_filters('admin_search_form_object', $searchFormObject);

$tagFieldOptions = array_map(Function($i) {
	return (object) [
		"text"=>$i->title,
		"value"=>$i->id,
	];
}, $applicable_tags);
$searchFormObject->fields[4]->select_options = $tagFieldOptions;

$userFieldsOptions = array_map(Function($i) {
	return (object) [
		"text"=>$i->username,
		"value"=>$i->id,
	];
}, $applicable_users);
$searchFormObject->fields[3]->select_options = $userFieldsOptions;

$categoryFieldOptions = array_map(Function($i) {
	return (object) [
		"text"=>$i->title,
		"value"=>$i->id,
	];
}, $applicable_categories);
$searchFormObject->fields[2]->select_options = $categoryFieldOptions;

$stateFieldOptions = array_map(Function($i) {
	return (object) [
		"text"=>$i->name,
		"value"=>$i->state,
	];
}, $custom_fields->states ?? []);
$searchFormObject->fields[1]->select_options = array_merge($searchFormObject->fields[1]->select_options, $stateFieldOptions);

$searchForm = new Form($searchFormObject);

if($searchForm->isSubmitted()) {
	$searchForm->setFromSubmit();
}

$cur_page = Input::getvar('page',v::IntVal(),'1');

$all_content_types = Content::get_all_content_types();
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');
$domain = $_SESSION["current_domain"];

$queryResult = Hook::execute_hook_filters('admin_search_form_results', (new HookQueryResult($searchForm, null, null, $cur_page)));

if($queryResult->results !== null && $queryResult->totalCount !== null) {
	$all_content = $queryResult->results;
	$content_count = $queryResult->totalCount;
} else {
	// start new content search class call
	$content_search = new ContentSearch();
	$content_search->searchtext = $search;
	$content_search->type_filter = $content_type_filter;
	$content_search->page = $cur_page;
	
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
	
	$state = Input::getVar("state", v::IntVal(), null);
	$category = Input::getVar("category", v::IntVal(), null);
	$creator = Input::getVar("creator", v::IntVal(), null);
	
	$filters = [];
	if($state !== null) {
		$filters["state"] = $state;
	}
	if($category !== null) {
		$filters["category"] = $category;
	}
	if($creator !== null) {
		$filters["created_by"] = $creator;
	}
	
	if (sizeof($filters) > 0) {
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
	
	// end new content search class
}

// get filter values for dropdowns etc

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

