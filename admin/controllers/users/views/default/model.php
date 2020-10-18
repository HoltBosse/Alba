<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view


$group_id = NULL;
$group_name = "All";
$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==2) {
	if (is_numeric($segments[1])) {
        $group_id = $segments[1];
        $group_name = User::get_group_name ($group_id);
    }
}

$user = new User();

if ($group_id) {
    $all_users = $user->get_all_users_in_group($group_id);
}
else {
    $all_users = $user->get_all_users();
}