<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$user = new User();
//$all_groups = $user->get_all_groups();
//$all_users = $user->get_all_users();

$user_ok = $user->load_from_post();
if (!$user_ok) {
	CMS::Instance()->queue_message('Failed to create user object from form data','danger',Config::uripath().'/admin/users');
}
$response = Hook::execute_hook_actions('validate_user_fields_form');

$custom_field_error = false; // only set to true if any errors occur during custom field saving

$custom_user_fields_form = new Form(CMSPATH . "/custom_user_fields.json");
if ($custom_user_fields_form) {
	$submitted = $custom_user_fields_form->is_submitted(); 
	$valid = $custom_user_fields_form->validate();
	if (!$submitted || !$valid) {
		CMS::Instance()->queue_message('Invalid additional details form','danger',Config::uripath().'/admin/users');
	}
	else {
		$custom_user_fields_form->set_from_submit();
		foreach ($custom_user_fields_form->fields as $field) {
			// insert field info
			if (isset($field->save)) {
				if ($field->save===false) {
					// field have save property set explicitly to false - SKIP saving
					// this may be a field such as an SQL statement from a non-cms table, or just markup etc
					continue;
				}
			}
			// TODO: handle other arrays 
			/* CMS::pprint_r ($field);  */
			if ($field->filter=="ARRAYOFINT") {
				// convert array of int to string
				if (is_array($field->default)) {
					$field->default = implode(",",$field->default);
				}
			}
			// make sure INT is good
			if ($field->coltype=="INTEGER") {
				$field->default = (int)$field->default;
				if (!is_numeric($field->default)) {
					$field->default = null;
				}
			}
			$result = DB::exec("UPDATE `custom_user_fields` SET `{$field->name}`=? WHERE `user_id`=?", [$field->default, $user->id]);
			if (!$result) {
				$error_text .= "Error saving: " . $field->name . " ";
				CMS::Instance()->log("Error saving: " . $field->name);
				$custom_field_error = true;
			}
		}
	}
}


$success = $user->save();
if ($success) {
	Hook::execute_hook_actions('on_user_save',$user);
	if (!$custom_field_error) {
		CMS::Instance()->queue_message('User saved','success',Config::uripath().'/admin/users');
	}
	else {
		CMS::Instance()->queue_message('User saved, additional fields had 1 or more errors. See Log.','warning',Config::uripath().'/admin/users');
	}
}
else {
	CMS::Instance()->queue_message('User save failed','danger',Config::uripath().'/admin/users');
}
