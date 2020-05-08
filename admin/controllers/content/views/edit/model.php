<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;



if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$content_id = $segments[2];
	
	$content = new content();
	$content->load($content_id);
	$new_content = false;
}
elseif(sizeof($segments)==4 && $segments[2]=='new' && is_numeric($segments[3])) {
	$content = new content($segments[3]);
	//$content->type_id = $segments[3]; // passing optional parameter to class constructor above
	$new_content = true;
}
else {
	CMS::Instance()->queue_message('Unkown content operation','danger',Config::$uripath.'/admin/content/show');
	exit(0);
}



// prep forms
$required_details_form = new Form(ADMINPATH . '/controllers/content/views/edit/required_fields_form.json');
$content_form = new Form (CMSPATH . '/controllers/' . $content->content_location . "/custom_fields.json");

// check if submitted or show defaults/data from db
if ($required_details_form->is_submitted()) {

	//echo "<h1>Submitted Content!</h1>";

	// update forms with submitted values
	$required_details_form->set_from_submit();
	$content_form->set_from_submit();


	// validate
	if ($required_details_form->validate() && $content_form->validate()) {
		// forms are valid, save info
		$content->save($required_details_form, $content_form);
	}
	else {
		CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['REQUEST_URI']);	
	}
	//CMS::Instance()->queue_message('content saved','success',Config::$uripath . '/admin/content/show');
}
else {
	// set defaults if needed
	if (!$new_content) {
		$required_details_form->get_field_by_name('state')->default = $content->state;
		$required_details_form->get_field_by_name('title')->default = $content->title;
		$required_details_form->get_field_by_name('alias')->default = $content->alias;
		$required_details_form->get_field_by_name('note')->default = $content->note;
		$required_details_form->get_field_by_name('start')->default = $content->start;
		$required_details_form->get_field_by_name('end')->default = $content->end;
	}
	// set content form TODO
	foreach ($content_form->fields as $content_field) {
		$value = $content->get_field($content_field->name);
		if ($value) {
			$content_field->default = $value;
		}
	}
}
