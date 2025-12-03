<?php

Use HoltBosse\Alba\Core\{CMS, Tag};
Use HoltBosse\Form\{Input, Form};

$segments = CMS::Instance()->uri_segments;

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$tag_id = $segments[2];
	$tag = new Tag();
	$tagLoadStatus = $tag->load($tag_id);

	if($tagLoadStatus==false) {
		CMS::Instance()->queue_message('Failed to load tag id: ' . $tag_id, 'danger',$_ENV["uripath"].'/admin/tags');
	}

	$new_tag = false;
}
elseif(sizeof($segments)==3 && $segments[2]=='new') {
	$tag = new Tag();
	$new_tag = true;
}
else {
	CMS::Instance()->queue_message('Unknown tag operation','danger',$_ENV["uripath"].'/admin/tags/show');
	exit(0);
}

if($tag->domain !== null && $tag->domain !== $_SESSION["current_domain"]) {
	CMS::raise_404();
}

// prep forms
if (isset($_ENV["tag_custom_fields_file_path"])) {
	$custom_fields_form = new Form ($_ENV["tag_custom_fields_file_path"]);
}

$required_details_obj = json_decode(file_get_contents(__DIR__ . '/required_fields_form.json'));

if($custom_fields_form) {
	$customFieldStates = json_decode(file_get_contents($_ENV["tag_custom_fields_file_path"]))->states ?? [];

	foreach($required_details_obj->fields as $field) {
		if($field->name == "state") {
			foreach($customFieldStates as $state) {
				$field->select_options[] = (object) [
					"value"=>$state->state,
					"text"=>$state->name,
				];
			}
		}
	}
}

$required_details_form = new Form($required_details_obj);

// check if submitted or show defaults/data from db
if ($required_details_form->isSubmitted()) {

	// update forms with submitted values
	$required_details_form->setFromSubmit();
	if ($custom_fields_form) {
		$custom_fields_form->setFromSubmit();
	}

	// validate
	if ($required_details_form->validate()) {
		if (!$custom_fields_form->id || ($custom_fields_form && $custom_fields_form->validate()) ) {
			// forms are valid, save info
			$saved = $tag->save($required_details_form, $custom_fields_form);
			// CMS::pprint_r($tag); die;
			if ($saved) {
				$msg = "Tag <a href='" . $_ENV["uripath"] . "/admin/tags/edit/{$tag->id}'>" . Input::stringHtmlSafe($tag->title) . "</a> " . ($new_tag ? 'created' : 'updated');
				CMS::Instance()->queue_message($msg, 'success', $_ENV["uripath"] . '/admin/tags');
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
	//CMS::Instance()->queue_message('tag saved','success',$_ENV["uripath"] . '/admin/tags/show');
}
else {
	// set defaults if needed
	if (!$new_tag) {
		$required_details_form->getFieldByName('state')->default = $tag->state;
		$required_details_form->getFieldByName('title')->default = $tag->title;
		$required_details_form->getFieldByName('alias')->default = $tag->alias;
		$required_details_form->getFieldByName('note')->default = $tag->note;
		$required_details_form->getFieldByName('image')->default = $tag->image;
		$required_details_form->getFieldByName('filter')->default = $tag->filter;
		$required_details_form->getFieldByName('description')->default = $tag->description;
		$required_details_form->getFieldByName('public')->default = $tag->public;
		$required_details_form->getFieldByName('contenttypes')->default = $tag->contenttypes;
		$required_details_form->getFieldByName('parent')->default = $tag->parent;
		$required_details_form->getFieldByName('category')->default = $tag->category;

		if ($custom_fields_form) {
			if ($tag->custom_fields) {
				$custom_fields_form->deserializeJson($tag->custom_fields);
			}
		}
	}
	
}
