<?php

Use HoltBosse\Alba\Core\{CMS, File, Tag, Hook, HookQueryResult, Form};
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;
use stdClass;

$searchFormObject = json_decode(File::getContents(__DIR__ . "/search_form.json"));
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
	$all_tags = $queryResult->results;
} else {
    $all_tags = Tag::get_all_tags_by_depth();

    $search = Input::getvar('search',v::StringVal(),null);
    $all_tags = array_values(array_filter($all_tags, function($tag) use ($search) {
        if($search === null) {
            return true;
        }
        return (str_contains($tag->title,$search) || str_contains($tag->note,$search));
    })); //filter tags by search term if provided
}

$all_tags = array_values(array_filter($all_tags, function($tag) {
    return ($tag->domain === null || $tag->domain === $_SESSION["current_domain"]);
})); //filter tags by current domain or null for all domains

$content_list_fields = [];

if (isset($_ENV["tag_custom_fields_file_path"])) {
	$customFieldsFormObject = json_decode(File::getContents($_ENV["tag_custom_fields_file_path"]));
    $named_custom_fields = array_column($customFieldsFormObject->fields, null, 'name');

    if (property_exists($customFieldsFormObject,'list')) {
        foreach ($customFieldsFormObject->list as $custom_field_name) {
            $custom_fields_list_item = new stdClass();
            $custom_fields_list_item->name = $custom_field_name;
            foreach ($customFieldsFormObject->fields as $field) {
                if ($field->name==$custom_field_name) {
                    $custom_fields_list_item->label = $field->label;
                    $custom_fields_list_item->type = $field->type;
                }
            }
            $content_list_fields[] = $custom_fields_list_item;
        }   
    }
}