<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$page = new Page();

$success=$page->load_from_post();

if (!$success) {
	CMS::Instance()->queue_message('Failed to create page object from form data','danger',Config::$uripath.'/admin/pages');
}

/* CMS::pprint_r ($page);
CMS::pprint_r ($page->view_configuration);
exit(0); */

// save content options / view configuration if available
if ($page->content_type && $page->view) {
	$content_loc = Content::get_content_location($page->content_type);
	$view_loc = Content::get_view_location($page->view);
	$options_form = new Form(CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options_form.json");
	$options_form->set_from_submit();
	$is_valid = $options_form->validate();
	if (!$is_valid) {
		CMS::Instance()->queue_message('Page creation/update failed - invalid view options form','danger',Config::$uripath.'/admin/pages');
	}
	// get name/value pairs json from form to add to view_configuration of page
	$page->view_configuration = $options_form->serialize_json();
}

$success = $page->save();

if ($success) {
	// save widget overrides
	// unique key on page, template and position in table	
	for ($n=0; $n < sizeof ($_POST['override_positions_widgets']); $n++ ) {
		$query = "insert into page_widget_overrides (page_id, position, widgets) ";
		$query.= "values (?,?,?) on duplicate key update page_id=?, position=?, widgets=?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$data = array( $page->id, $_POST['override_positions'][$n], $_POST['override_positions_widgets'][$n], $page->id, $_POST['override_positions'][$n], $_POST['override_positions_widgets'][$n] );
		$override_success = $stmt->execute($data);
	}
	
	

	CMS::Instance()->queue_message('Page created/updated','success',Config::$uripath.'/admin/pages');
}
else {
	CMS::Instance()->queue_message('Page creation/update failed','danger',Config::$uripath.'/admin/pages');
}
