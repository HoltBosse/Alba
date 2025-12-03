<?php

Use HoltBosse\Alba\Core\{CMS, Category, Content};
Use HoltBosse\Form\{Form, Input};
Use HoltBosse\DB\DB;

$segments = CMS::Instance()->uri_segments;

//CMS::pprint_r ($segments);

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	// edit existing cat
	
	$cat_id = $segments[2];
	$content_type = DB::fetch('select content_type from categories where id=?', [$cat_id]);
	//CMS::pprint_r ($content_type); exit(0);
	if (!$content_type) {
		CMS::Instance()->queue_message('Unknown category','danger',$_ENV["uripath"].'/admin/categories/show');
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
	CMS::Instance()->queue_message('Unknown cat operation','danger',$_ENV["uripath"].'/admin/categories/all');
	exit(0);
}

if(!Content::isAccessibleOnDomain(isset($content_type->content_type) ? $content_type->content_type : $segments[3], $_SESSION["current_domain"])) {
	CMS::raise_404();
}

if($cat->domain !== null && $cat->domain !== $_SESSION["current_domain"]) {
	CMS::raise_404();
}

// update CMS instance with this content information
// this allows custom form fields etc to easily access information such as
// content id/type
//CMS::Instance()->editing_content = $content;


// prep forms
$required_details_form = new Form(__DIR__ . '/required_fields_form.json');
// set content_type for tag field based on content type of new/editing content

$content_location = Content::get_content_location($cat->content_type);
if (isset($_ENV["category_custom_fields_form_path"])) {
	$custom_fields_form = new Form ($_ENV["category_custom_fields_form_path"]);
}

// check if submitted or show defaults/data from db
if ($required_details_form->isSubmitted()) {

	//echo "<h1>Submitted Content!</h1>";

	// update forms with submitted values
	$required_details_form->setFromSubmit();

	if ($custom_fields_form) {
		$custom_fields_form->setFromSubmit();
	}

	// validate
	if ($required_details_form->validate()) {
		if (!$custom_fields_form->id || ($custom_fields_form && $custom_fields_form->validate()) ) {
			// forms are valid, save info
			$saved = $cat->save($required_details_form, $custom_fields_form);
			if ($saved) {
				$msg = "Category <a href='" . $_ENV["uripath"] . "/admin/categories/edit/{$cat->id}'>" . Input::stringHtmlSafe($cat->title) . "</a> " . ($new_cat ? 'created' : 'updated');	
				CMS::Instance()->queue_message($msg, 'success', $_ENV["uripath"] . '/admin/categories');
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
	//CMS::Instance()->queue_message('content saved','success',$_ENV["uripath"] . '/admin/content/show');
}
else {
	// set defaults if needed
	if (!$new_cat) {
		$required_details_form->getFieldByName('state')->default = $cat->state;
		$required_details_form->getFieldByName('title')->default = $cat->title;
		$required_details_form->getFieldByName('parent')->default = $cat->parent;
		$required_details_form->getFieldByName('parent')->content_type = $cat->content_type;
		$required_details_form->getFieldByName('parent')->self_id = $cat->id; // set self_id so don't show in dropdown
		$required_details_form->getFieldByName('content_type')->default = $cat->content_type;

		if ($custom_fields_form) {
			if ($cat->custom_fields) {
				$custom_fields_form->deserializeJson($cat->custom_fields);
			}
		}
	}
	else {
		// always know for new cat what content type is
		$required_details_form->getFieldByName('parent')->content_type = $cat->content_type;
		$required_details_form->getFieldByName('content_type')->default = $cat->content_type;
	}
}
