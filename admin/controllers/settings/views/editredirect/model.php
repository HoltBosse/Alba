<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$version_count = 0;

$new_content = false;

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$redirect_id = $segments[2];
	$redirect = DB::fetch('select * from redirects where id=?',$redirect_id);
	
}
elseif(sizeof($segments)==3 && $segments[2]=='new') {
	$new_content = true;
}
else {
	CMS::Instance()->queue_message('Unkown content operation','danger',Config::uripath().'/admin/settings/redirects');
	exit(0);
}

$required_details_obj = json_decode(file_get_contents(ADMINPATH . '/controllers/settings/views/editredirect/required_fields_form.json'));
foreach($required_details_obj->fields as $field) {
	if($field->name == "state") {
		foreach($custom_fields->states as $state) {
			$field->select_options[] = (object) [
				"value"=>$state->state,
				"text"=>$state->name,
			];
		}
	}
}

// prep forms
$required_details_form = new Form($required_details_obj);

// check if submitted or show defaults/data from db
if ($required_details_form->is_submitted()) {

	// update forms with submitted values
	$required_details_form->set_from_submit();

	// validate
	if ($required_details_form->validate()) {
		// forms are valid, save info
		
		$state = $required_details_form->get_field_by_name('state')->default;
		$note = $required_details_form->get_field_by_name('note')->default;
		$old_url = $required_details_form->get_field_by_name('old_url')->default;
		$new_url = $required_details_form->get_field_by_name('new_url')->default;
		$updated_by = CMS::Instance()->user->id;
		$header = $required_details_form->get_field_by_name('header')->default;

		if ($new_content) {
			$params = [$state,$note,$old_url,$new_url,$updated_by,$header];
			$saved = DB::exec("INSERT INTO `redirects` (`state`,note,old_url,new_url,updated_by,header) VALUES (?,?,?,?,?,?)", $params);
		}
		else {
			$params = [$state,$note,$old_url,$new_url,$updated_by,$header,$redirect_id];
			$saved = DB::exec("UPDATE `redirects` SET `state`=?, note=?, old_url=?, new_url=?, updated_by=?, header=? WHERE id=?", $params);
		}
	
		if ($saved) {
			if ($quicksave) {
				$redirect_to = $_SERVER['HTTP_REFERER'];
				$msg = "Quicksave successful";
			}
			else {
				$redirect_to = Config::uripath() . "/admin/settings/redirects/";
				$msg = "Redirect saved";
			}
			CMS::Instance()->queue_message($msg, 'success', $redirect_to);
		}
		else {
			CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['HTTP_REFERER']);
		}
		
	}
	else {
		CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['REQUEST_URI']);	
	}
	//CMS::Instance()->queue_message('content saved','success',Config::uripath() . '/admin/content/show');
}
else {
	// set category field content_type based on current new/edited content type
	// set defaults if needed
	if (!$new_content) {
		$required_details_form->get_field_by_name('state')->default = $redirect->state;
		$required_details_form->get_field_by_name('note')->default = $redirect->note;
		$required_details_form->get_field_by_name('old_url')->default = $redirect->old_url;
		$required_details_form->get_field_by_name('new_url')->default = $redirect->new_url;
	}
}
