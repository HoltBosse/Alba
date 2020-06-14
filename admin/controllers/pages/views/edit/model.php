<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$user = new User();
$all_groups = $user->get_all_groups();
//$all_users = $user->get_all_users();

$all_templates = Template::get_all_templates();

$all_content_types = Content::get_all_content_types();

// todo: get default template as set by user instead of template 1
$default_template = 1;
$template = new Template(1);

// determine if editing existing page or new page

// uri = /pages/edit/PAGE_ID/TYPE_ID/VIEW_ID
// if page_id = 0 - new page
// if page_id exists, try and override form content 

$page = new Page();
$page_options_form = new Form(ADMINPATH . '/controllers/pages/page_options.json');

//CMS::pprint_r (CMS::Instance()->uri_segments);
if (array_key_exists(2, CMS::Instance()->uri_segments)) {
	$uri_id = CMS::Instance()->uri_segments[2];
	// TODO: check type etc
	if ($uri_id>0) {
		// existing page - override view and content based on uri parameters if required
		if (!$page->load_from_id($uri_id)) {
			CMS::Instance()->queue_message('Failed to load Page id: ' . $uri_id, 'danger',Config::$uripath.'/admin/pages');
			exit(0);
		}
		// set CMS property edit_page_id for template layout.php file to access
		CMS::Instance()->edit_page_id = $page->id;
	
		/* // page loaded from id, check for content_type in url and override current page
		if (array_key_exists(3, CMS::Instance()->uri_segments)) {
			$page->content_type = CMS::Instance()->uri_segments[3];
			// check for view url override too
			if (array_key_exists(4, CMS::Instance()->uri_segments)) {
				// have view - override DB values for view AND content type based on passed view id
				$page->view = CMS::Instance()->uri_segments[4];
			}
		} */
	}
	else {
		// set CMS property edit_page_id for template layout.php file to access
		CMS::Instance()->edit_page_id = 0;
	}

	// override content_type and view based on uri params
	if (array_key_exists(3, CMS::Instance()->uri_segments)) {
		$page->content_type = CMS::Instance()->uri_segments[3];
		if (array_key_exists(4, CMS::Instance()->uri_segments)) {
			$page->view = CMS::Instance()->uri_segments[4];
		}
	}
	
}
else {
	CMS::Instance()->queue_message('Unknown operation - no parameters available' . $uri_id, 'danger',Config::$uripath.'/admin/pages');
	exit(0);
}



$layout_path = CMSPATH . '/templates/' . $template->folder . "/layout.php";
if (!file_exists($layout_path)) {
	CMS::Instance()->queue_message('Failed to locate layout for template: ' . $template->title, 'danger',Config::$uripath.'/admin/pages');
}
