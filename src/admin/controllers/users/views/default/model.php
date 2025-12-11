<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, User, UserSearch, Tag};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

// any variables created here will be available to the view


$group_id = NULL;
$group_name = "All";
$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==2) {
	if (is_numeric($segments[1])) {
        $group_id = $segments[1];
        $group_name = User::get_group_name ($group_id);
    }
}

$user = new User();

/* if ($group_id) {
    $all_users = $user->get_all_users_in_group($group_id);
}
else {
    $all_users = $user->get_all_users();
} */

// new user search - based on improved content search

$search = Input::getvar('search',v::StringVal(),null);
$filters = Input::tuplesToAssoc( Input::getvar('filters',v::AlwaysValid(),null) );
$coretags = Input::getvar('coretags',v::arrayType()->each(v::intVal()),[]);
$groups = Input::getvar('groups',v::arrayType()->each(v::intVal()),[]); 
$cur_page = Input::getvar('page',v::IntVal(),'1');

$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');

// start new content search class call - experimental
$user_search = new UserSearch();
$user_search->searchtext = $search;
$user_search->page = $cur_page;

$domain = $_SESSION["current_domain"];

//even if in shared mode, specific domain view as the search looks for null (all domains) or specific domain
$user_search->domain = $domain;

$applicable_tags = Tag::get_tags_available_for_content_type (-2); // -2 = user content type
$all_groups = User::get_all_groups();
if ($groups) {
	$user_search->groups = $groups;
}
if ($filters) {
	$user_search->filters = $filters;
	if($filters["state"]) {
		$user_search->disable_builtin_state_check = true;
	}
}
if ($coretags) {
	$user_search->tags = $coretags;
}

$all_users = $user_search->exec();
$user_count = $user_search->get_count();

$states = NULL;
$content_list_fields = [];
$customUserFieldsLookup = [];
if(isset($_ENV["custom_user_fields_file_path"])) {
	$formObject = json_decode(file_get_contents($_ENV["custom_user_fields_file_path"]));
	if($formObject->states) {
		$states = $formObject->states;
	}

	if (property_exists($formObject,'list')) {
		foreach ($formObject->list as $fieldName) {
			$custom_fields_list_item = new stdClass();
			$custom_fields_list_item->name = $fieldName;

			foreach ($formObject->fields as $field) {
				if ($field->name==$fieldName) {
					$custom_fields_list_item->label = $field->label;
					$custom_fields_list_item->type = $field->type;
				}
			}
			$content_list_fields[] = $custom_fields_list_item;
		}
		
	}

	$allUserIds = array_column($all_users, 'id');
	if(!empty($allUserIds)) {
		$cufResults = DB::fetchAll('SELECT * FROM custom_user_fields WHERE user_id IN (' . implode(",", array_map(function($input) {return "?";}, $allUserIds)) . ')', $allUserIds);
	} else {
		$cufResults = [];
	}
	foreach($cufResults as $cufRow) {
		$customUserFieldsLookup[$cufRow->user_id] = $cufRow;
	}
}