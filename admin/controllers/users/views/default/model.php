<?php
defined('CMSPATH') or die; // prevent unauthorized access

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

$search = Input::getvar('search','TEXT',null);
$filters = Input::tuples_to_assoc( Input::getvar('filters','RAW',null) );
$coretags = Input::getvar('coretags','ARRAYOFINT',[]);
$groups = Input::getvar('groups','ARRAYOFINT',[]); 
$cur_page = Input::getvar('page','INT','1');

$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');

// start new content search class call - experimental
$user_search = new Users_Search();
$user_search->searchtext = $search;
$user_search->page = $cur_page;

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
if(file_exists(CMSPATH . "/custom_user_fields.json")) {
	$formObject = json_decode(file_get_contents(CMSPATH . "/custom_user_fields.json"));
	if($formObject->states) {
		$states = $formObject->states;
	}
}