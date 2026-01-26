<?php

Use HoltBosse\Alba\Core\{CMS, Template, Content, Widget, Hook, HookQueryResult};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Form;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

if (sizeof(CMS::Instance()->uri_segments)==3) {
	$widget_type_id = Input::filter(CMS::Instance()->uri_segments[2], v::NumericVal(), false);
} else {
	$widget_type_id = false;
}

if($widget_type_id && !Widget::isAccessibleOnDomain($widget_type_id, $_SESSION["current_domain"])) {
	CMS::raise_404();
}

$searchFormObject = json_decode(file_get_contents(__DIR__ . "/search_form.json"));
if(!is_numeric($widget_type_id)) {
	$searchFormObject->fields[] = (object) [
		"type"=>"Select",
        "label"=>"Widget Type",
        "name"=>"widget_type",
        "id"=>"widget_type",
        "placeholder"=>"widget",
        //"select_options"=>DB::fetchAll("SELECT title AS text, location AS value FROM widget_types"),
		"select_options"=>array_filter(
			array_map(
				function($input) {
					if(Widget::isAccessibleOnDomain($input->id)) {
						//CMS::pprint_r($input);
						
						return (object) [
							"text"=>$input->title,
							"value"=>$input->id
						];
					} else {
						return null;
					}
				},
				DB::fetchAll("SELECT id, title, location FROM widget_types")
			),
			function($input) {
				return !is_null($input);
			}
		),
		"filter"=>[
			"IntVal"=>[]
		]
	];
}
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

//it is expected that this hook will return * from the respective table in queryResult->results
$queryResult = Hook::execute_hook_filters('admin_search_form_results', (new HookQueryResult($searchForm)));

if($queryResult->results !== null && $queryResult->totalCount !== null) {
	$all_widgets = $queryResult->results;
} else {
	$widget_type_title="";
	if ($widget_type_id && is_numeric($widget_type_id)) {
		$widget_type_title = DB::fetch('SELECT title FROM widget_types WHERE id=?', [$widget_type_id])->title;
	}
	
	$query = 'SELECT w.* FROM widgets w LEFT JOIN widget_types wt ON w.type=wt.id WHERE w.state>=0 AND (domain IS NULL OR domain=?)';
	$params = [$_SESSION["current_domain"]];
	
	if(is_numeric($widget_type_id)) {
		$query .= " AND w.type=?";
		$params[] = $widget_type_id;
	}
	
	$searchState = Input::getVar("state", v::numericVal(), null);
	if(!is_null($searchState)) {
		$query .= " AND w.state=?";
		$params[] = $searchState;
	}
	
	$searchTitle = Input::getVar("title", v::stringType()->length(1, null), null);
	if($searchTitle) {
		$query .= " AND w.title like ?";
		$params[] = "%{$searchTitle}%";
	}
	
	$searchType = Input::getVar("widget_type", v::stringType()->length(1, null), null);
	if($searchType) {
		$query .= " AND wt.id=?";
		$params[] = $searchType;
	}
	
	$searchPage = Input::getVar("page", v::numericVal(), null);
	if($searchPage) {
		$query .= " AND ((FIND_IN_SET(?, w.page_list) AND w.position_control=0) OR (NOT FIND_IN_SET(?, w.page_list) AND w.position_control=1))";
		$params[] = $searchPage;
		$params[] = $searchPage;
	}
	
	$query .= ' ORDER BY id DESC';
	
	$all_widgets = DB::fetchAll($query, $params);
}


$contentTypeDomainCache = [];
$all_widgets = array_filter($all_widgets, function($widget) use (&$contentTypeDomainCache) {
	if(!isset($contentTypeDomainCache[$widget->type])) {
		$contentTypeDomainCache[$widget->type] = Widget::isAccessibleOnDomain($widget->type);
	}

	return $contentTypeDomainCache[$widget->type] != false;
});

$all_widget_types = Widget::get_all_widget_types();

