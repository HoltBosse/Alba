<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$tag_id = $segments[2];
	$tag = new Tag();
	$tag->load($tag_id);
	$new_tag = false;
}
elseif(sizeof($segments)==3 && $segments[2]=='new') {
	$tag = new Tag();
	$new_tag = true;
}
else {
	CMS::Instance()->queue_message('Unknown tag operation','danger',Config::uripath().'/admin/tags/show');
	exit(0);
}

// prep forms
$required_details_form = new Form(CMSPATH . '/admin/controllers/tags/views/edit/required_fields_form.json');


// check if submitted or show defaults/data from db
if ($required_details_form->isSubmitted()) {

	// update forms with submitted values
	$required_details_form->setFromSubmit();


	// validate
	if ($required_details_form->validate()) {
		// forms are valid, save info
		$saved = $tag->save($required_details_form);
		if ($saved) {
			CMS::Instance()->queue_message('Tag saved','success', Config::uripath() . '/admin/content/all/' . $this->content_type);
		}
		else {
			CMS::Instance()->queue_message('Failed to save tag','danger',$_SERVER['REQUEST_URI']);
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
		$required_details_form->getFieldByName('state')->default = $tag->state;
		$required_details_form->getFieldByName('title')->default = $tag->title;
		$required_details_form->getFieldByName('alias')->default = $tag->alias;
		$required_details_form->getFieldByName('note')->default = $tag->note;
		$required_details_form->getFieldByName('filter')->default = $tag->filter;
		$required_details_form->getFieldByName('description')->default = $tag->description;
		$required_details_form->getFieldByName('public')->default = $tag->public;
		$required_details_form->getFieldByName('contenttypes')->default = $tag->contenttypes;
	}
	
}
