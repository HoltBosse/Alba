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
$new_user = !$user->id; //check for new user

$success = $user->save();
$custom_field_error = false; // only set to true if any errors occur during custom field saving

$custom_user_fields_form = file_exists(CMSPATH . "/custom_user_fields.json") ? new Form(CMSPATH . "/custom_user_fields.json") : null;
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
			}
			$result = DB::exec(
				"INSERT INTO `custom_user_fields` (user_id, `{$field->name}`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `{$field->name}`=?",
				[$user->id, $field->default, $field->default]
			);
			if (!$result) {
				$error_text .= "Error saving: " . $field->name . " ";
				CMS::Instance()->log("Error saving: " . $field->name);
				$custom_field_error = true;
			}
		}
	}
}

$redirect_url = Config::uripath() . '/admin/users';
if (Input::getvar("http_referer_form") && Input::getvar("http_referer_form") != $_SERVER["HTTP_REFERER"]){
	$redirect_url = Input::getvar("http_referer_form");
}

if ($success) {
	Hook::execute_hook_actions('on_user_save',$user);
	if (!$custom_field_error) {
		$msg = "User <a href='" . Config::uripath() . "/admin/users/edit/{$user->id}'>" . Input::stringHtmlSafe($user->username) . "</a> " . ($new_user ? 'created' : 'updated');
		CMS::Instance()->queue_message($msg, 'success', $redirect_url);
	}
	else {
		CMS::Instance()->queue_message('User saved, additional fields had 1 or more errors. See Log.','warning', $redirect_url);
	}
}
else {
	CMS::Instance()->queue_message('User save failed','danger', $redirect_url);
}
