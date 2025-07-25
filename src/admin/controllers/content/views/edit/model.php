<?php

Use HoltBosse\Alba\Core\{CMS, Content, Hook};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Form, Input};

$segments = CMS::Instance()->uri_segments;

$version_count = 0;

if (sizeof($segments)==4 && is_numeric($segments[2]) && is_numeric($segments[3])) {
	// need to pass content type now as well
	$content_id = $segments[2];
	$content_type = $segments[3];
	
	$content = new Content();
	$content->load($content_id, $content_type);
	$new_content = false;

	$version_count = DB::fetch('select count(id) as c from content_versions where content_id=?', [$content_id])->c;
	
}
elseif(sizeof($segments)==4 && $segments[2]=='new' && is_numeric($segments[3])) {
	$content_type = $segments[3];
	$content = new Content($content_type);
	//$content->type_id = $segments[3]; // passing optional parameter to class constructor above
	$new_content = true;
}
else {
	CMS::Instance()->queue_message('Unknown content operation','danger',$_ENV["uripath"].'/admin');
	exit(0);
}

// update CMS instance with this content information
// this allows custom form fields etc to easily access information such as
// content id/type
CMS::Instance()->editing_content = $content;


// inject custom content states into form
$custom_fields = json_decode(file_get_contents(Content::getContentControllerPath($content->content_location) . "/custom_fields.json"));
$required_details_obj = json_decode(file_get_contents(__DIR__ . '/required_fields_form.json'));

$required_details_obj = Hook::execute_hook_filters('content_required_details_form_obj', $required_details_obj);

foreach($required_details_obj->fields as $field) {
	if($field->name == "state") {
		foreach($custom_fields->states as $state) {
			$field->select_options[] = (object) [
				"value"=>$state->state,
				"text"=>$state->name,
			];
		}
	}
	if($field->name == "tags") {
		$field->content_type = $content_type;
	}
}

// prep forms
$required_details_form = new Form($required_details_obj);
$content_form = new Form ($custom_fields);
// set content_type for tag field based on content type of new/editing content
$tags_field = $required_details_form->getFieldByName('tags');
$tags_field->content_type = $content->content_type;

// check if submitted or show defaults/data from db
if ($required_details_form->isSubmitted()) {

	//echo "<h1>Submitted Content!</h1>"; exit(0);

	// update forms with submitted values
	$required_details_form->setFromSubmit();
	$content_form->setFromSubmit();

	// validate
	if ($required_details_form->validate() && $content_form->validate()) {
		// forms are valid, save info
		// first save version if versions are turned on
		
		// TODO: make versions work with new fields
		/* $content_versions = Configuration::get_configuration_value ('general_options', 'content_versions');
		if (is_numeric($content_versions) && $content_versions>0 && !$new_content) {
			// save old version
			//$old_version = new content();
			$content_location = Content::get_content_location($content->content_type);
			$old_content = Content::get_all_content("id", $content_location, $content_id); // 2nd param being passed gives enough info to get custom fields
			
			if ($old_content) {
				Content::save_version($old_content[0]);
			}
			else {
				CMS::Instance()->queue_message('Unable to get all original content fields','danger',$_SERVER['REQUEST_URI']);
			}
		} */

		$quicksave = Input::getvar('quicksave',"STRING");
		$saved = $content->save($required_details_form, $content_form );
	
		if ($saved) {
			if ($quicksave) {
				$redirect_to = $_SERVER['HTTP_REFERER'];
				$msg = "Quicksave successful";
			}
			else {
				$redirect_to = $_ENV["uripath"] . "/admin/content/all/" . $content->content_type;
				if (Input::getvar("http_referer_form") && Input::getvar("http_referer_form") != $_SERVER["HTTP_REFERER"]){
					$redirect_to = Input::getvar("http_referer_form");
				}
				$msg = "Content <a href='" . $_ENV["uripath"] . "/admin/content/edit/{$content->id}/{$content_type}'>" . Input::stringHtmlSafe($content->title) . "</a> " . ($new_content ? 'created' : 'updated');
			}
			CMS::Instance()->queue_message($msg, 'success', $redirect_to);
		}
		else {
			CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['HTTP_REFERER']);
		}
		
	}
	else {
		CMS::Instance()->queue_message('Invalid form','danger');	
	}
	//CMS::Instance()->queue_message('content saved','success',$_ENV["uripath"] . '/admin/content/show');
}
else {
	// set category field content_type based on current new/edited content type
	$required_details_form->getFieldByName('category')->content_type = $content->content_type;
	// set defaults if needed
	if (!$new_content) {
		$required_details_form->getFieldByName('state')->default = $content->state;
		$required_details_form->getFieldByName('title')->default = $content->title;
		$required_details_form->getFieldByName('alias')->default = $content->alias;
		$required_details_form->getFieldByName('note')->default = $content->note;
		$required_details_form->getFieldByName('start')->default = $content->start;
		$required_details_form->getFieldByName('end')->default = $content->end;
		$required_details_form->getFieldByName('category')->default = $content->category;
		
		// load tags
		$tag_id_array=[]; // $content->tags is array of tag objects returned from Tag::get_tags_for_content function
		foreach ($content->tags as $t) {
			$tag_id_array[] = $t->id;
		}
		// TagMultiple field expects a json array of integers
		$required_details_form->getFieldByName('tags')->default = json_encode($tag_id_array); 
	}
	// set content form TODO
	if(!$new_content) {
		foreach ($content_form->fields as $content_field) {
			if (property_exists($content_field,'save')) {
				if ($content_field->save===false) {
					continue; // skip unsaveable fields
				}
			}
			$value = $content->get_field($content_field->name);
			//CMS::pprint_r ('got '); CMS::pprint_r ($value);
			$content_field->default = $value;
		}
	}
}
