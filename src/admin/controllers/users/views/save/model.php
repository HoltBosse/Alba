<?php

Use HoltBosse\Alba\Core\{CMS, User, Hook};
Use HoltBosse\Form\{Form, Input};
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

// any variables created here will be available to the view

$user = new User();
//$all_groups = $user->get_all_groups();
//$all_users = $user->get_all_users();

$user_ok = $user->load_from_post();
if (!$user_ok) {
	CMS::Instance()->queue_message('Failed to create user object from form data','danger',$_ENV["uripath"].'/admin/users');
}
$response = Hook::execute_hook_actions('validate_user_fields_form');
$new_user = !$user->id; //check for new user

$success = $user->save();
$custom_field_error = false; // only set to true if any errors occur during custom field saving

$custom_user_fields_form = isset($_ENV["custom_user_fields_file_path"]) ? new Form($_ENV["custom_user_fields_file_path"]) : null;
if ($custom_user_fields_form) {
	$submitted = $custom_user_fields_form->isSubmitted(); 
	$valid = $custom_user_fields_form->validate();
	if (!$submitted || !$valid) {
		CMS::Instance()->queue_message('Invalid additional details form','danger',$_ENV["uripath"].'/admin/users');
	}
	else {
		$custom_user_fields_form->setFromSubmit();
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

$redirect_url = $_ENV["uripath"] . '/admin/users';
if (Input::getvar("http_referer_form", v::StringVal()) && Input::getvar("http_referer_form", v::StringVal()) != $_SERVER["HTTP_REFERER"]){
	$redirect_url = Input::getvar("http_referer_form", v::StringVal());
}

if ($success) {
	Hook::execute_hook_actions('on_user_save',$user);
	if (!$custom_field_error) {
		$msg = "User <a href='" . $_ENV["uripath"] . "/admin/users/edit/{$user->id}'>" . Input::stringHtmlSafe($user->username) . "</a> " . ($new_user ? 'created' : 'updated');
		CMS::Instance()->queue_message($msg, 'success', $redirect_url);
	}
	else {
		CMS::Instance()->queue_message('User saved, additional fields had 1 or more errors. See Log.','warning', $redirect_url);
	}
}
else {
	CMS::Instance()->queue_message('User save failed','danger', $redirect_url);
}
