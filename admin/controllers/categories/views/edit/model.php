<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

//CMS::pprint_r ($segments);

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	// edit existing cat
	
	$cat_id = $segments[2];
	$content_type = DB::fetch('select content_type from categories where id=?', [$cat_id]);
	//CMS::pprint_r ($content_type); exit(0);
	if (!$content_type) {
		CMS::Instance()->queue_message('Unknown category','danger',Config::uripath().'/admin/categories/show');
	}
	$cat = new Category($content_type->content_type);
	$cat->load($cat_id);
	$new_cat = false;
}
elseif(sizeof($segments)==4 && $segments[2]=='new' && is_numeric($segments[3])) {
	$cat = new Category($segments[3]);
	//$content->type_id = $segments[3]; // passing optional parameter to class constructor above
	$new_cat = true;
}
else {
	CMS::Instance()->queue_message('Unknown cat operation','danger',Config::uripath().'/admin/categories/show');
	exit(0);
}

// update CMS instance with this content information
// this allows custom form fields etc to easily access information such as
// content id/type
//CMS::Instance()->editing_content = $content;


// prep forms
$required_details_form = new Form(CMSPATH . '/admin/controllers/categories/views/edit/required_fields_form.json');
// set content_type for tag field based on content type of new/editing content

$content_location = Content::get_content_location($cat->content_type);
$custom_fields_form = file_exists(CMSPATH . "/controllers/" . $content_location . "/cat_fields.json");
if ($custom_fields_form) {
	$custom_fields_form = new Form (CMSPATH . "/controllers/" . $content_location . "/cat_fields.json");
}

// check if submitted or show defaults/data from db
if ($required_details_form->is_submitted()) {

	//echo "<h1>Submitted Content!</h1>";

	// update forms with submitted values
	$required_details_form->set_from_submit();

	if ($custom_fields_form) {
		$custom_fields_form->set_from_submit();
	}

	// validate
	if ($required_details_form->validate()) {
		if (!$custom_fields_form->id || ($custom_fields_form && $custom_fields_form->validate()) ) {
			// forms are valid, save info
			$saved = $cat->save($required_details_form, $custom_fields_form);
			if ($saved) {
				$msg = "Category <a href='" . Config::uripath() . "/admin/categories/edit/{$cat->id}'>" . Input::stringHtmlSafe($cat->title) . "</a> " . ($new_cat ? 'created' : 'updated');	
				CMS::Instance()->queue_message($msg, 'success', Config::uripath() . '/admin/categories');
			}
			else {
				CMS::Instance()->queue_message('Failed to save category','danger',$_SERVER['REQUEST_URI']);
			}
		}
		else {
			CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['REQUEST_URI']);	
		}
	}
	else {
		CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['REQUEST_URI']);	
	}
	//CMS::Instance()->queue_message('content saved','success',Config::uripath() . '/admin/content/show');
}
else {
	// set defaults if needed
	if (!$new_cat) {
		$required_details_form->get_field_by_name('state')->default = $cat->state;
		$required_details_form->get_field_by_name('title')->default = $cat->title;
		$required_details_form->get_field_by_name('parent')->default = $cat->parent;
		$required_details_form->get_field_by_name('parent')->content_type = $cat->content_type;
		$required_details_form->get_field_by_name('parent')->self_id = $cat->id; // set self_id so don't show in dropdown
		$required_details_form->get_field_by_name('content_type')->default = $cat->content_type;

		if ($custom_fields_form) {
			if ($cat->custom_fields) {
				$custom_fields_form->deserialize_json($cat->custom_fields);
			}
		}
	}
	else {
		// always know for new cat what content type is
		$required_details_form->get_field_by_name('parent')->content_type = $cat->content_type;
		$required_details_form->get_field_by_name('content_type')->default = $cat->content_type;
	}
	//CMS::pprint_r ($required_details_form);
	foreach ($content_form->fields as $content_field) {
		$value = $content->get_field($content_field->name);
		if ($value) {
			$content_field->default = $value;
		}
	}
}
