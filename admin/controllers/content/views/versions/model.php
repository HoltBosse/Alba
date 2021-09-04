<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$version_count = 0;

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$content_id = $segments[2];
	
	$content = new content();
	$content->load($content_id);
	$new_content = false;

	$cur_content_fields = DB::fetchAll('select * from content_fields where content_id=?',array($content_id));
	$version_count = DB::fetch('select count(id) as c from content_versions where content_id=?',array($content_id))->c;
	$versions = DB::fetchAll('select v.*, u.username from content_versions v, users u where content_id=? and u.id=v.created_by order by created desc',array($content_id));
}
else {
	CMS::Instance()->queue_message('Content not found','danger',Config::$uripath.'/admin/content/show');
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


