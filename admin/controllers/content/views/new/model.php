<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$all_content_types = Content::get_all_content_types();

$new_content_type_form = new Form(ADMINPATH . "/controllers/content/views/new/new_content_type_form.json");

function make_alias($string) {
	$string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
	$string = Input::stringURLSafe($string);
	return $string;
}

$new_content_type_form->set_from_submit();
if ($new_content_type_form->is_submitted()) {
	echo "<h1>got data!</h1>";
	if ($new_content_type_form->validate()) {
		
		$title = $new_content_type_form->get_field_by_name('title')->default;
		$description = $new_content_type_form->get_field_by_name('description')->default;
		$defaultviewtitle = $new_content_type_form->get_field_by_name('defaultviewtitle')->default;
		$defaultviewdescription = $new_content_type_form->get_field_by_name('defaultviewdescription')->default;
		$foldername = make_alias($title);
		$defaultviewfoldername = make_alias($defaultviewtitle);
		CMS::pprint_r ($title );
		CMS::pprint_r ($description );
		CMS::pprint_r ($defaultviewtitle);
		CMS::pprint_r ($defaultviewdescription);
		CMS::pprint_r ($foldername);
		CMS::pprint_r ($defaultviewfoldername);
		// TODO: check for folder existing with name $foldername in cmspath / controllers /
		// then create folder - if done
		// then create files, view folde, view files
		// then reload content page and allow auto-installer to do its thing :)
		if (file_exists(CMSPATH . "/controllers/" . $foldername)) {
			CMS::Instance()->queue_message('Folder for new controller type already exists','error',Config::$uripath . '/admin/content/new');
		}
		if (!mkdir(CMSPATH . "/controllers/" . $foldername)) {
			CMS::Instance()->queue_message('Unable to create folder for new controller type','error',Config::$uripath . '/admin/content/new');
		}

		// folder created
		// now create files

		$custom_fields_dot_json = "
		{
			\"title\":\"{$title}\",
			\"id\":\"{$foldername}\",
			\"fields\":[
			]
		}";
		if (!file_put_contents (CMSPATH . '/controllers/' . $foldername . "/custom_fields.json", $custom_fields_dot_json)) {
			CMS::Instance()->queue_message('Unable to create custom_fields.json file for new controller type','error',Config::$uripath . '/admin/content/new');
		}

		$controller_config_dot_json = "
		{
			\"title\":\"{$title}\",
			\"description\":\"{$description}\"
		}
		";
		if (!file_put_contents (CMSPATH . '/controllers/' . $foldername . "/controller_config.json", $controller_config_dot_json)) {
			CMS::Instance()->queue_message('Unable to create custom_fields.json file for new controller type','error',Config::$uripath . '/admin/content/new');
		}

		// copy controller.php (no custom text required)
		if (!copy(ADMINPATH . '/controllers/content/views/new/new_controller_files/' . "controller.php", CMSPATH . "/controllers/" . $foldername . "/controller.php")) {
			CMS::Instance()->queue_message('Unable to copy controller.php file for new controller type default view','error',Config::$uripath . '/admin/content/new');
		}

		// create default view

		// make views folders
		if (!mkdir(CMSPATH . "/controllers/" . $foldername . "/views")) {
			CMS::Instance()->queue_message('Unable to create views folder for new controller type','error',Config::$uripath . '/admin/content/new');
		}
		if (!mkdir(CMSPATH . "/controllers/" . $foldername . "/views/" . $defaultviewfoldername)) {
			CMS::Instance()->queue_message('Unable to create views default view folder for new controller type','error',Config::$uripath . '/admin/content/new');
		}

		// files to be placed inside view/defaultview

		// copy options_form.json, model.php, and view.php - warn that changes should be made to these files

		if (!copy(ADMINPATH . '/controllers/content/views/new/new_controller_files/' . "options_form.json", CMSPATH . "/controllers/" . $foldername . "/views/" . $defaultviewfoldername . "/options_form.json")) {
			CMS::Instance()->queue_message('Unable to copy options_form.json file for new controller type default view','error',Config::$uripath . '/admin/content/new');
		}
		if (!copy(ADMINPATH . '/controllers/content/views/new/new_controller_files/' . "model.php", CMSPATH . "/controllers/" . $foldername . "/views/" . $defaultviewfoldername . "/model.php")) {
			CMS::Instance()->queue_message('Unable to copy model.php file for new controller type default view','error',Config::$uripath . '/admin/content/new');
		}
		if (!copy(ADMINPATH . '/controllers/content/views/new/new_controller_files/' . "view.php", CMSPATH . "/controllers/" . $foldername . "/views/" . $defaultviewfoldername . "/view.php")) {
			CMS::Instance()->queue_message('Unable to copy view.php file for new controller type default view','error',Config::$uripath . '/admin/content/new');
		}

		$view_configuration_dot_json = "
		{
			\"title\":\"{$defaultviewtitle}\",
			\"description\":\"{$defaultviewdescription}\"
		}
		";
		if (!file_put_contents (CMSPATH . '/controllers/' . $foldername . "/views/" . $defaultviewfoldername . "/view_configuration.json", $view_configuration_dot_json)) {
			CMS::Instance()->queue_message('Unable to create view_configuration.json file for new controller type default view','error',Config::$uripath . '/admin/content/new');
		}

		// got here - all good :)
		CMS::Instance()->queue_message('New content type created - importing','success',Config::$uripath . '/admin/content/');
		
	}
	else {
		echo "<h2>It's INVALID!</h2>";
	}
}


