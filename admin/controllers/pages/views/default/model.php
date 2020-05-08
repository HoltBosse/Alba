<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$page = new Page();


//$template = new Template();
$all_templates = Template::get_all_templates();
if (!$all_templates) {
	CMS::Instance()->queue_message('No templates installed - please install at least one template','danger',Config::$uripath.'/admin');
	exit(0);
}

// TODO: change to user set default
$default_template = 1;

$all_pages = Page::get_all_pages_by_depth(); // defaults to parent=-1 and depth=-1

function get_template_title($page_template_id, $all_templates) {
	foreach ($all_templates as $template) {
		if ($page_template_id == $template->id) {
			return $template->title;
		}
	}
	return "Error - Unknown Template";
}
