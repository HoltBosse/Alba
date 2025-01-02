<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$page = new Page();
$page_options_form = new Form(CMSPATH . '/admin/controllers/pages/page_options.json');
$success=$page->load_from_post();

if (!$success) {
	CMS::Instance()->queue_message('Failed to create page object from form data','danger',Config::uripath().'/admin/pages');
}

if(Input::getvar("id")==0) {
	$new_page = true;
	$existing_page = DB::fetch("SELECT * FROM pages WHERE alias=? AND parent=?", [$page->alias, $page->parent]);
	if($existing_page) {
		CMS::Instance()->queue_message('Page already exists (user created)','danger',Config::uripath().'/admin/pages');
	} elseif(file_exists(CMSPATH . "/core/controllers/{$page->alias}/controller.php")) {
		//read https://www.php.net/manual/en/function.clearstatcache.php - if the file is found to exist, and then later deleted, will need to clear the cache
		CMS::Instance()->queue_message('Page already exists (core)','danger',Config::uripath().'/admin/pages');
	}
}

/* CMS::pprint_r($page);
CMS::pprint_r($page->view_configuration);
die;
 */

// save content options / view configuration if available
if ($page->content_type && $page->view) {
	$content_loc = Content::get_content_location($page->content_type);
	$view_loc = Content::get_view_location($page->view);
	$options_form = new Form(CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options_form.json");
	$options_form->set_from_submit();
	$is_valid = $options_form->validate();
	if (!$is_valid) {
		CMS::Instance()->queue_message('Page creation/update failed - invalid view options form','danger',Config::uripath().'/admin/pages');
	}
	// get name/value pairs json from form to add to view_configuration of page
	$page->view_configuration = $options_form->serialize_json();
}

// save page seo/og options
$page->page_options = $page->page_options_form->serialize_json(); // not view options, page seo/og options :)
$is_valid = $page_options_form->validate();
if (!$is_valid) {
	CMS::Instance()->queue_message('Page creation/update failed - invalid page options form','danger',Config::uripath().'/admin/pages');
}

$success = $page->save();

if ($success) {
	// save widget overrides
	// unique key on page, template and position in table	
	for ($n=0; $n < sizeof ($_POST['override_positions_widgets']); $n++ ) {
		$data = [$page->id, $_POST['override_positions'][$n], $_POST['override_positions_widgets'][$n], $page->id, $_POST['override_positions'][$n], $_POST['override_positions_widgets'][$n]];
		$override_success = DB::exec("insert into page_widget_overrides (page_id, position, widgets) values (?,?,?) on duplicate key update page_id=?, position=?, widgets=?", $data);
	}

	$msg = "Page <a href='" . Config::uripath() . "/admin/pages/edit/{$page->id}/{$page->content_type}/{$page->view}'>{$page->title}</a> $status" . ($new_page ? 'created' : 'updated');
	CMS::Instance()->queue_message($msg, 'success', Config::uripath().'/admin/pages');
}
else {
	CMS::Instance()->queue_message('Page creation/update failed','danger',Config::uripath().'/admin/pages');
}
