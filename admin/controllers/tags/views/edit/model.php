<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$tag_id = $segments[2];
	$tag = new tag();
	$tag->load($tag_id);
	$new_tag = false;
}
elseif(sizeof($segments)==3 && $segments[2]=='new') {
	$tag = new tag();
	$new_tag = true;
}
else {
	CMS::Instance()->queue_message('Unknown tag operation','danger',Config::uripath().'/admin/tags/show');
	exit(0);
}

// prep forms
$required_details_form = new Form(ADMINPATH . '/controllers/tags/views/edit/required_fields_form.json');
$custom_fields_form = file_exists(CMSPATH . "/tag_fields.json");
if ($custom_fields_form) {
	$custom_fields_form = new Form (CMSPATH . "/tag_fields.json");
}

// check if submitted or show defaults/data from db
if ($required_details_form->is_submitted()) {

	// update forms with submitted values
	$required_details_form->set_from_submit();
	if ($custom_fields_form) {
		$custom_fields_form->set_from_submit();
	}

	// validate
	if ($required_details_form->validate()) {
		if (!$custom_fields_form || ($custom_fields_form && $custom_fields_form->validate()) ) {
			// forms are valid, save info
			$saved = $tag->save($required_details_form, $custom_fields_form);
			if ($saved) {
				CMS::Instance()->queue_message('Tag saved','success', "/admin/tags");
			}
			else {
				CMS::Instance()->queue_message('Failed to save tag','danger',$_SERVER['REQUEST_URI']);
			}
		}
		else {
			CMS::Instance()->queue_message('Invalid field form','danger',$_SERVER['REQUEST_URI']);	
		}
	}
	else {
		CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['REQUEST_URI']);	
	}
	//CMS::Instance()->queue_message('tag saved','success',Config::uripath() . '/admin/tags/show');
}
else {
	// set defaults if needed
	if (!$new_tag) {
		$required_details_form->get_field_by_name('state')->default = $tag->state;
		$required_details_form->get_field_by_name('title')->default = $tag->title;
		$required_details_form->get_field_by_name('alias')->default = $tag->alias;
		$required_details_form->get_field_by_name('note')->default = $tag->note;
		$required_details_form->get_field_by_name('image')->default = $tag->image;
		$required_details_form->get_field_by_name('filter')->default = $tag->filter;
		$required_details_form->get_field_by_name('description')->default = $tag->description;
		$required_details_form->get_field_by_name('public')->default = $tag->public;
		$required_details_form->get_field_by_name('contenttypes')->default = $tag->contenttypes;
		$required_details_form->get_field_by_name('parent')->default = $tag->parent;
		$required_details_form->get_field_by_name('category')->default = $tag->category;

		if ($custom_fields_form) {
			if ($tag->custom_fields) {
				$custom_fields_form->deserialize_json($tag->custom_fields);
			}
		}
	}
	
}
