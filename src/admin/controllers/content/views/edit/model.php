<?php

Use HoltBosse\Alba\Core\{CMS, Content, Hook};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Form, Input};
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;

if (sizeof($segments)==4 && is_numeric($segments[2]) && is_numeric($segments[3])) {
	// need to pass content type now as well
	$content_id = $segments[2];
	$content_type = $segments[3];

	$contentTypeTableRecord = DB::fetch("SELECT * FROM content_types WHERE id=?", $content_type);
	if(!$contentTypeTableRecord) {
		CMS::raise_404();
	}
	
	$content = new Content();
	$contentLoadStatus = $content->load($content_id, $content_type);
	$tableName = Content::get_table_name_for_content_type($content_type);
	$contentItem = DB::fetch("SELECT * FROM `{$tableName}` WHERE id=?",[$content_id]);

	if($contentLoadStatus===false) {
		CMS::Instance()->queue_message('Failed to load content id: ' . $content_id, 'danger',$_ENV["uripath"].'/admin/content/all/' . $content_type);
	}
	$new_content = false;

	$revision = Input::getVar("revision", v::numericVal(), null);
	if($revision) {
		$revisionId = (int) $revision;

		$revisions = DB::fetchall(
			"SELECT ua.date, uad.json
			FROM user_actions ua
			LEFT JOIN user_actions_details uad ON ua.id=uad.action_id
			WHERE REPLACE(JSON_EXTRACT(ua.json, '$.content_id'), '\"', '')=?
			AND REPLACE(JSON_EXTRACT(ua.json, '$.content_type'), '\"', '')=?
			AND ua.id>=?
			AND ua.type='contentupdate'
			ORDER BY ua.date DESC",
			[$content_id, $content_type, $revisionId]
		);

		foreach($revisions as $revision) {
			if(empty($revision->json) || $revision->json=="{}") {
				continue;
			}

			$rData = json_decode($revision->json);
			foreach($rData as $key=>$action) {
				
				if(isset($contentItem->$key)) {
					$contentItem->$key = $action->before;
				}
			}
		}

		//CMS::pprint_r($contentItem);
		//CMS::pprint_r($revisions[sizeof($revisions)-1]);
	}
	
} elseif(sizeof($segments)==4 && $segments[2]=='new' && is_numeric($segments[3])) {
	$content_type = $segments[3];
	$content = new Content($content_type);

	$contentTypeTableRecord = DB::fetch("SELECT * FROM content_types WHERE id=?", $content_type);
	if(!$contentTypeTableRecord) {
		CMS::raise_404();
	}

	//$content->type_id = $segments[3]; // passing optional parameter to class constructor above
	$new_content = true;
} else {
	CMS::Instance()->queue_message('Unknown content operation','danger',$_ENV["uripath"].'/admin');
	exit(0);
}

if(!Content::isAccessibleOnDomain($content_type, $_SESSION["current_domain"])) {
	CMS::raise_404();
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

		$quicksave = Input::getvar('quicksave',V::StringVal());
		$saved = $content->save($required_details_form, $content_form );
	
		if ($saved) {
			if ($quicksave) {
				$redirect_to = $_SERVER['HTTP_REFERER'];
				$msg = "Quicksave successful";
			}
			else {
				$redirect_to = $_ENV["uripath"] . "/admin/content/all/" . $content->content_type;
				if (Input::getvar("http_referer_form", v::StringVal()) && Input::getvar("http_referer_form", v::StringVal()) != $_SERVER["HTTP_REFERER"]){
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
	$required_details_form->getFieldByName('category')->content_type = $contentItem->content_type;
	// set defaults if needed
	if (!$new_content) {
		$required_details_form->getFieldByName('state')->default = $contentItem->state;
		$required_details_form->getFieldByName('title')->default = $contentItem->title;
		$required_details_form->getFieldByName('alias')->default = $contentItem->alias;
		$required_details_form->getFieldByName('note')->default = $contentItem->note;
		$required_details_form->getFieldByName('start')->default = $contentItem->start;
		$required_details_form->getFieldByName('end')->default = $contentItem->end;
		$required_details_form->getFieldByName('category')->default = $contentItem->category;

		// load tags
		$tag_id_array=[]; // $content->tags is array of tag objects returned from Tag::get_tags_for_content function
		foreach ($content->tags as $t) { //tags are currently not handled by content versions, so we use the content class entry here
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
			$customFieldsKey = $content_field->name;
			$value = $contentItem->$customFieldsKey;
			//CMS::pprint_r ('got '); CMS::pprint_r ($value);
			$content_field->default = $value;
		}
	}
}
