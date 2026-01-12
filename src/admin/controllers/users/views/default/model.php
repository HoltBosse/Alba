<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, User, UserSearch, Tag, Hook, Form, HookQueryResult};
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
$all_groups = User::get_all_groups();

$applicable_tags = Tag::get_tags_available_for_content_type (-2); // -2 = user content type
$applicable_tags = array_values(array_filter($applicable_tags, function($tag) {
	return ($tag->domain === null || $tag->domain === $_SESSION["current_domain"]);
}));

$searchFormObject = json_decode(file_get_contents(__DIR__ . "/search_form.json"));

$states = NULL;
if(isset($_ENV["custom_user_fields_file_path"])) {
	$formObject = json_decode(file_get_contents($_ENV["custom_user_fields_file_path"]));
	if($formObject->states) {
		$states = $formObject->states;
	}
}
if(!is_null($states)) {
	$stateOptions = array_map(Function($i) {
		return (object) [
			"text"=>$i->name,
			"value"=>$i->state,
		];
	}, $states);
	$searchFormObject->fields[1]->select_options = array_merge($searchFormObject->fields[1]->select_options, $stateOptions);
}

$groupFieldOptions = array_map(Function($i) {
	return (object) [
		"text"=>$i->display,
		"value"=>$i->id,
	];
}, $all_groups);
$searchFormObject->fields[2]->select_options = $groupFieldOptions;

$tagFieldOptions = array_map(Function($i) {
	return (object) [
		"text"=>$i->title,
		"value"=>$i->id,
	];
}, $applicable_tags);
$searchFormObject->fields[3]->select_options = $tagFieldOptions;

$searchFormObject->fields[] = (object) [
	"type"=>"Html",
	"html"=>"<div style='display: flex; gap: 1rem;'>
				<button class='button is-info' type='submit'>Submit</button>
				<button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
			</div>"
];

$searchFormObject = Hook::execute_hook_filters('admin_search_form_object', $searchFormObject);

$searchForm = new Form($searchFormObject);

if($searchForm->isSubmitted()) {
	$searchForm->setFromSubmit();
}

$cur_page = Input::getvar('page',v::IntVal(),'1');
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size');
$domain = $_SESSION["current_domain"];

$queryResult = Hook::execute_hook_filters('admin_search_form_results', (new HookQueryResult($searchForm, null, null, $cur_page)));

if($queryResult->results !== null && $queryResult->totalCount !== null) {
	$all_users = $queryResult->results;
	$user_count = $queryResult->totalCount;
} else {
	// new user search - based on improved content search
	$search = Input::getvar('search',v::StringVal(),null);
	$state = Input::getvar('state',v::IntVal(),null);
	$coretags = Input::getvar('tagged',v::arrayType()->each(v::intVal()),[]);
	$groups = Input::getvar('grouped',v::arrayType()->each(v::intVal()),[]); 
	
	// start new content search class call - experimental
	$user_search = new UserSearch();
	$user_search->searchtext = $search;
	$user_search->page = $cur_page;
	
	//even if in shared mode, specific domain view as the search looks for null (all domains) or specific domain
	$user_search->domain = $domain;
	
	if ($groups) {
		$user_search->groups = $groups;
	}
	if (!is_null($state)) {
		$user_search->filters = [ "state" => $state ];
	
		$user_search->disable_builtin_state_check = true;
	
	}
	if ($coretags) {
		$user_search->tags = $coretags;
	}
	
	$all_users = $user_search->exec();
	$user_count = $user_search->get_count();
}


$content_list_fields = [];
$customUserFieldsLookup = [];
if(isset($_ENV["custom_user_fields_file_path"])) {
	$formObject = json_decode(file_get_contents($_ENV["custom_user_fields_file_path"]));

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