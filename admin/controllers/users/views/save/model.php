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
if (!$response) {
	CMS::Instance()->queue_message('Invalid additional details form','danger',Config::uripath().'/admin/users');
}
$success = $user->save();
if ($success) {
	$response = Hook::execute_hook_actions('save_user_fields_form',$user);
	Hook::execute_hook_actions('on_user_save',$user);
	if ($response) {
		CMS::Instance()->queue_message('User saved','success',Config::uripath().'/admin/users');
	}
	else {
		CMS::Instance()->queue_message('User saved, additional fields did not','warning',Config::uripath().'/admin/users');
	}
}
else {
	CMS::Instance()->queue_message('User save failed','danger',Config::uripath().'/admin/users');
}
