<?php

Use HoltBosse\Alba\Core\{CMS, User};
Use HoltBosse\Form\Form;
Use HoltBosse\DB\DB;

// any variables created here will be available to the view

$edit_user = new User();
$all_groups = $edit_user->get_all_groups();
$segments = CMS::Instance()->uri_segments;
// check if editing, if we are, get userid and load into edit_user object

if(sizeof($segments) > 3) {
	CMS::raise_404();
}

$core_user_fields_form = new Form(__DIR__ . "/core_user_fields.json");
$custom_user_fields_form = isset($_ENV["custom_user_fields_file_path"]) ? new Form($_ENV["custom_user_fields_file_path"]) : null;

if (sizeof($segments)==3) {
	if (is_numeric($segments[2])) {
		$userLoadStatus = $edit_user->load_from_id($segments[2]);

		if($userLoadStatus==false) {
			CMS::Instance()->queue_message('Failed to load User id: ' . $segments[2], 'danger',$_ENV["uripath"].'/admin/users');
		}

		//load core user fields form
		$core_user_fields_form->fields["username"]->default = $edit_user->username;
		$core_user_fields_form->fields["email"]->default = $edit_user->email;
		$core_user_fields_form->fields["userstate"]->default = $edit_user->state;

		// load user fields
		if ($custom_user_fields_form) {
			$saved_data = DB::fetch('SELECT * FROM `custom_user_fields` WHERE `user_id`=?',[$segments[2]]);
			foreach ($custom_user_fields_form->fields as $content_field) {
				if (property_exists($content_field,'save')) {
					if ($content_field->save===false) {
						continue; // skip unsaveable fields
					}
				}
				$value = $saved_data->{$content_field->name} ?? null;
				if ($value||is_numeric($value)) {
					$content_field->default = $value;
				}
			}
		}
	}
}

//remove the description from the password field for new users
if(!$edit_user->email) {
	$core_user_fields_form->fields["password"]->description = "";
} else {
	$core_user_fields_form->fields["password"]->required = false;
}

$states = NULL;
if(isset($_ENV["custom_user_fields_file_path"])) {
	$formObject = json_decode(file_get_contents($_ENV["custom_user_fields_file_path"]));
	if($formObject->states) {
		foreach($formObject->states as $state) {
			$core_user_fields_form->fields["userstate"]->select_options[] = (object) ["text"=>$state->name,"value"=>$state->state];
		}
	}
}