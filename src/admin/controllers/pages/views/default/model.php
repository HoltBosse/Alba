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

$domainLookup = DB::fetchAll("SELECT value FROM `domains`", [], ["mode"=>PDO::FETCH_COLUMN]);

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

$searchWhere = "";
$searchParams = [];
if(!is_null($searchState)) {
	$searchWhere .= " AND state = ?";
	$searchParams[] = $searchState;
}
if($searchTitle) {
	$searchWhere .= " AND title LIKE ?";
	$searchParams[] = "%$searchTitle%";
}

$all_pages = DB::fetchall(
	"WITH RECURSIVE page_hierarchy AS (
		SELECT *, 0 as depth, domain as sort_domain, CAST(id AS CHAR(500)) as sort_path
		FROM pages
		WHERE parent = -1
			AND state > -1
		
		UNION ALL
		
		SELECT p.*, ph.depth + 1, p.domain, CONCAT(ph.sort_path, '-', LPAD(p.id, 10, '0'))
		FROM pages p
		INNER JOIN page_hierarchy ph ON p.parent = ph.id
		WHERE p.state > -1
	)
	SELECT *
	FROM page_hierarchy
	WHERE domain = ?
	$searchWhere
	ORDER BY CASE WHEN alias = 'home' THEN 0 ELSE 1 END, sort_path, content_type, content_view, sort_domain",
	array_merge([$_SESSION["current_domain"]], $searchParams)
);