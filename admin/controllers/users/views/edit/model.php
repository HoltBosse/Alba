<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$edit_user = new User();
$all_groups = $edit_user->get_all_groups();
$segments = CMS::Instance()->uri_segments;
// check if editing, if we are, get userid and load into edit_user object
if (sizeof($segments)==3) {
	if (is_numeric($segments[2])) {
		$edit_user->load_from_id($segments[2]);
	}
}