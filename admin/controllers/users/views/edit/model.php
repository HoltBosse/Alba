<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$edit_user = new User();
$all_groups = $edit_user->get_all_groups();
$segments = CMS::Instance()->uri_segments;
// check if editing, if we are, get userid and load into edit_user object

if(sizeof($segments) > 3) {
	CMS::raise_404();
}

$core_user_fields_form = new Form(CMSPATH . "/admin/controllers/users/views/edit/core_user_fields.json");
$custom_user_fields_form = file_exists(CMSPATH . "/custom_user_fields.json") ? new Form(CMSPATH . "/custom_user_fields.json") : null;

if (sizeof($segments)==3) {
	if (is_numeric($segments[2])) {
		$edit_user->load_from_id($segments[2]);

		//load core user fields form
		$core_user_fields_form->fields["username"]->default = $edit_user->username;
		$core_user_fields_form->fields["email"]->default = $edit_user->email;

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