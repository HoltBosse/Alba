<?php

Use HoltBosse\Alba\Core\{CMS, Category, Content, Hook, Form, HookQueryResult};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search',v::StringVal(),null);

$content_type_filter = 0;
if (sizeof($segments)==3) {
	$content_type_filter = $segments[2];
}

$max_content_id = DB::fetch("SELECT MAX(id) AS id FROM content_types")->id;
if($content_type_filter < -3 || $content_type_filter > $max_content_id) {
	CMS::show_error("Invalid content type", 404);
}

$searchFormObject = json_decode(file_get_contents(__DIR__ . "/search_form.json"));
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

$queryResult = Hook::execute_hook_filters('admin_search_form_results', (new HookQueryResult($searchForm)));

if($queryResult->results !== null && $queryResult->totalCount !== null) {
	$all_categories = $queryResult->results;
} else {
	$all_categories = Category::get_all_categories_by_depth($content_type_filter);

	$all_categories = array_values(array_filter($all_categories, function($category) use ($search) {
		if($search === null) {
			return true;
		}
		return (str_contains($category->title,$search));
	})); //filter categories by search term if provided
}

$all_content_types = Content::get_all_content_types();

$contentTypeDomainCache = [];
$all_categories = array_filter($all_categories, function($category) use (&$contentTypeDomainCache) {
	if(!isset($contentTypeDomainCache[$category->content_type])) {
		if($category->content_type > 0) {
			$contentTypeDomainCache[$category->content_type] = Content::isAccessibleOnDomain($category->content_type, $_SESSION["current_domain"]);
		} else {
			$contentTypeDomainCache[$category->content_type] = true;
		}
	}

	return $contentTypeDomainCache[$category->content_type] != false;
});

$all_categories = array_filter($all_categories, function($category) {
	if($category->domain!==null && $category->domain!=$_SESSION["current_domain"]) {
		return false;
	}
	return true;
});

$all_categories = array_values($all_categories); //reindex nicely after filtering