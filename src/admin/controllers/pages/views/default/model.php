<?php

Use HoltBosse\Alba\Core\{CMS, Template, Page};
Use HoltBosse\DB\DB;

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

$domainLookup = DB::fetchAll("SELECT value FROM `domains`", [], ["mode"=>PDO::FETCH_COLUMN]);