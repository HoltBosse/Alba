<?php

Use HoltBosse\Alba\Core\{CMS, Widget, User, Template, Content, Page};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Form, Input};

// check for widget preview
if (CMS::Instance()->uri_segments[2]=="widget_preview" && is_numeric(CMS::Instance()->uri_segments[3])) {
	ob_end_clean();
	ob_end_clean(); // not a mistake, a safety net
	ob_start();
	$widget_id = CMS::Instance()->uri_segments[3];
	$widget = DB::fetch('SELECT * FROM widgets WHERE id=? AND state=1', $widget_id);
	if ($widget->state==1) {
		$type_info = Widget::get_widget_type($widget->type);
		$widget_class_name = "Widget_" . $type_info->location;
		$widget_of_type = new $widget_class_name();
		if(!($widget_of_type instanceof Widget)) {
			throw new Exception("Widget class $widget_class_name does not extend Widget");
		}
		$widget_of_type->load ($widget->id);
		$widget_of_type->internal_render();
	}
	$output = ob_get_clean();
	echo $output;
	die();
}

// any variables created here will be available to the view

$user = new User();
$all_groups = $user->get_all_groups();
//$all_users = $user->get_all_users();

$all_templates = Template::get_all_templates();

$all_content_types = Content::get_all_content_types();
$all_content_types = array_values(array_filter($all_content_types, function($ct) {
	return Content::isAccessibleOnDomain($ct->id, $_SESSION["current_domain"]);
}));

// todo: get default template as set by user instead of template 1
$default_template = Template::get_default_template();
$template = $default_template;

// determine if editing existing page or new page

// uri = /pages/edit/PAGE_ID/TYPE_ID/VIEW_ID
// if page_id = 0 - new page
// if page_id exists, try and override form content 

$page = new Page();
$page_options_form = new Form(__DIR__ . '/page_options.json');

//CMS::pprint_r (CMS::Instance()->uri_segments);
if (array_key_exists(2, CMS::Instance()->uri_segments)) {
	$uri_id = CMS::Instance()->uri_segments[2];
	// TODO: check type etc
	if ($uri_id>0) {
		// existing page - override view and content based on uri parameters if required
		if (!$page->load_from_id($uri_id)) {
			// page load from id also loads correct template for page
			CMS::Instance()->queue_message('Failed to load Page id: ' . $uri_id, 'danger',$_ENV["uripath"].'/admin/pages');
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
		// NEW page
		// set CMS property edit_page_id for template layout.php file to access
		CMS::Instance()->edit_page_id = 0;
		// set page template to default
		$page->template_id = 0;
		$page->template = $default_template;
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
	CMS::Instance()->queue_message('Unknown operation - no parameters available' . $uri_id, 'danger',$_ENV["uripath"].'/admin/pages');
	exit(0);
}


$layout_path = Template::getTemplatePath($page->template->folder) . "/layout.php";
if (!file_exists($layout_path)) {
	CMS::Instance()->queue_message('Failed to locate layout for template: ' . $page->template->title, 'danger',$_ENV["uripath"].'/admin/pages');
}
