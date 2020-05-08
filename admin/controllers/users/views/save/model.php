<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$user = new User();
//$all_groups = $user->get_all_groups();
//$all_users = $user->get_all_users();

$success=$user->load_from_post();

if (!$success) {
	CMS::Instance()->queue_message('Failed to create user object from form data','danger',Config::$uripath.'/admin/users');
}

$success = $user->save();
if ($success) {
	CMS::Instance()->queue_message('User saved','success',Config::$uripath.'/admin/users');
}
else {
	CMS::Instance()->queue_message('User save failed','danger',Config::$uripath.'/admin/users');
}
