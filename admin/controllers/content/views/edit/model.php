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

// update CMS instance with this content information
// this allows custom form fields etc to easily access information such as
// content id/type
CMS::Instance()->editing_content = $content;


// prep forms
$required_details_form = new Form(ADMINPATH . '/controllers/content/views/edit/required_fields_form.json');
$content_form = new Form (CMSPATH . '/controllers/' . $content->content_location . "/custom_fields.json");
// set content_type for tag field based on content type of new/editing content
$tags_field = $required_details_form->get_field_by_name('tags');
$tags_field->content_type = $content->content_type;

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
		$required_details_form->get_field_by_name('category')->default = $content->category;
		// load tags
		$tag_id_array=[]; // $content->tags is array of tag objects returned from Tag::get_tags_for_content function
		foreach ($content->tags as $t) {
			$tag_id_array[] = $t->id;
		}
		// TagMultiple field expects a json array of integers
		$required_details_form->get_field_by_name('tags')->default = json_encode($tag_id_array); 
	}
	// set content form TODO
	foreach ($content_form->fields as $content_field) {
		$value = $content->get_field($content_field->name);
		if ($value) {
			$content_field->default = $value;
		}
	}
}
