<?php

Use HoltBosse\Alba\Core\{CMS, Template, Page, Hook, File};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Form;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

// any variables created here will be available to the view

$page = new Page();


//$template = new Template();
$all_templates = Template::get_all_templates();
if (!$all_templates) {
	CMS::Instance()->queue_message('No templates installed - please install at least one template','danger',$_ENV["uripath"].'/admin');
	exit(0);
}

// TODO: change to user set default
$default_template = 1;

$all_pages = Page::get_all_pages_by_depth(); // defaults to parent=-1 and depth=-1

// @phpstan-ignore missingType.iterableValue
function get_template_title(int $page_template_id, array $all_templates): string {
	foreach ($all_templates as $template) {
		if ($page_template_id == $template->id) {
			return $template->title;
		}
	}
	$default_template = Template::get_default_template();
	return "Default (" . $default_template->title . ")";
	//return "Error - Unknown Template";
}

$all_pages = array_filter($all_pages, function($page) {
	return $page->domain == $_SESSION["current_domain"];
});

$all_pages = array_values($all_pages); // reindex after array_filter

// Build search form using CMS form system
$searchFormObject = json_decode(File::getContents(__DIR__ . "/search_form.json"));
$searchFormObject->fields[] = (object) [
	"type" => "Html",
	"html" => "<div style='display: flex; gap: 1rem;'>
                <button class='button is-info' type='submit'>Submit</button>
                <button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
            </div>"
];

$searchFormObject = Hook::execute_hook_filters('admin_search_form_object', $searchFormObject);

$searchForm = new Form($searchFormObject);
if ($searchForm->isSubmitted()) {
	$searchForm->setFromSubmit();
}

// Apply search filters to $all_pages
$searchState = Input::getVar('state', v::numericVal(), null);
$searchTitle = Input::getVar('title', v::stringType()->length(1, null), null);
if (!is_null($searchState) || $searchTitle) {
	$all_pages = array_filter($all_pages, function($p) use ($searchState, $searchTitle) {
		if (!is_null($searchState) && (int)$p->state !== (int)$searchState) return false;
		if ($searchTitle && stripos($p->title, $searchTitle) === false) return false;
		return true;
	});
	$all_pages = array_values($all_pages);
}

$domainLookup = DB::fetchAll("SELECT value FROM `domains`", [], ["mode"=>PDO::FETCH_COLUMN]);