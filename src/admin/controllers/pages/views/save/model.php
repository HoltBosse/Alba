<?php

Use HoltBosse\Alba\Core\{CMS, Content, Page, Template};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Form, Input};
Use Respect\Validation\Validator as v;

// any variables created here will be available to the view

$page = new Page();
$page_options_form = new Form(__DIR__ . '/../edit/page_options.json');
$success=$page->load_from_post();

if (!$success) {
	CMS::Instance()->queue_message('Failed to create page object from form data','danger',$_ENV["uripath"].'/admin/pages');
}

if(Input::getvar("id",v::IntVal())==0) {
	$new_page = true;
	$existing_page = DB::fetch("SELECT * FROM pages WHERE alias=? AND parent=? AND domain=?", [$page->alias, $page->parent, $page->domain]);
	if($existing_page) {
		CMS::Instance()->queue_message('Page already exists (user created)','danger',$_ENV["uripath"].'/admin/pages');
	} elseif(CMS::getCoreControllerPath($page->alias)) {
		//read https://www.php.net/manual/en/function.clearstatcache.php - if the file is found to exist, and then later deleted, will need to clear the cache
		CMS::Instance()->queue_message('Page already exists (core)','danger',$_ENV["uripath"].'/admin/pages');
	}
}

if(Input::getvar("id",v::IntVal())!=0) {
	$existingPage = new Page();
	$existingPage->load_from_id(Input::getvar("id",v::IntVal()));
}

/* CMS::pprint_r($page);
CMS::pprint_r($page->view_configuration);
die;
 */

// save content options / view configuration if available
if ($page->content_type && $page->view) {
	$content_loc = Content::get_content_location($page->content_type);
	$view_loc = Content::get_view_location($page->view);
	$options_json_filepath = Content::getContentControllerPath($content_loc) . "/views/" . $view_loc . "/options_form.json";
	if(!file_exists($options_json_filepath)) {
		$template = new Template((int) $page->template_id);
		$templatepath = Template::getTemplatePath($template->folder);
		$controller_folder = DB::fetch("SELECT * FROM content_types WHERE id=?", (int) $page->content_type)->controller_location;
		$options_json_filepath = $templatepath . "/overrides/" . $controller_folder . "/" . $view_loc . "/options_form.json";
	}
	$options_form = new Form($options_json_filepath);
	$options_form->setFromSubmit();
	$is_valid = $options_form->validate();
	if (!$is_valid) {
		CMS::Instance()->queue_message('Page creation/update failed - invalid view options form','danger',$_ENV["uripath"].'/admin/pages');
	}
	// get name/value pairs json from form to add to view_configuration of page
	$page->view_configuration = json_encode($options_form, JSON_THROW_ON_ERROR);
}

// save page seo/og options
$page->page_options = json_encode($page->page_options_form, JSON_THROW_ON_ERROR); // not view options, page seo/og options :)
$is_valid = $page_options_form->validate();
if (!$is_valid) {
	CMS::Instance()->queue_message('Page creation/update failed - invalid page options form','danger',$_ENV["uripath"].'/admin/pages');
}

$page->domain = $_SESSION["current_domain"];
$success = $page->save();

if ($success) {
	// save widget overrides
	// unique key on page, template and position in table
	$overridePositionsWidgets = Input::getVar('override_positions_widgets', v::arrayType()->each(v::optional(v::stringVal())), []);
	$overridePositions = Input::getVar('override_positions', v::arrayType()->each(v::optional(v::StringVal())), []);

	for ($n=0; $n < sizeof ($overridePositionsWidgets); $n++ ) {
		$data = [$page->id, $overridePositions[$n], $overridePositionsWidgets[$n], $page->id, $overridePositions[$n], $overridePositionsWidgets[$n]];
		$override_success = DB::exec("insert into page_widget_overrides (page_id, position, widgets) values (?,?,?) on duplicate key update page_id=?, position=?, widgets=?", $data);
	}

	$quicksave = Input::getvar('quicksave',v::StringVal(),'');
	if ($quicksave) {
		$msg = "Quicksave successful";
		$redirectTo = $_SERVER['HTTP_REFERER'];
	} else {
		$msg = "Page <a href='" . $_ENV["uripath"] . "/admin/pages/edit/{$page->id}/{$page->content_type}/{$page->view}'>" . Input::stringHtmlSafe($page->title) . "</a> $status" . ($new_page ? 'created' : 'updated');
		$redirectTo = $_ENV["uripath"].'/admin/pages';
	}

	CMS::Instance()->queue_message($msg, 'success', $redirectTo);
}
else {
	CMS::Instance()->queue_message('Page creation/update failed','danger',$_ENV["uripath"].'/admin/pages');
}
