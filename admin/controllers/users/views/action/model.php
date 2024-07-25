<?php
defined('CMSPATH') or die; // prevent unauthorized access

$actions = [
	"toggle"=>["userupdate", "(CASE state WHEN 1 THEN 0 ELSE 1 END)", "toggled"],
	"publish"=>["userupdate", 1, "published"],
	"unpublish"=>["userupdate", 0, "unpublished"],
	"delete"=>["userdelete", -1, "deleted"],
];

$action = CMS::Instance()->uri_segments[2];
$id = Input::getvar('id','ARRAYOFINT');
if (!$action || !$id || !$actions[$action]) {
	CMS::Instance()->queue_message('Unknown action or items','danger', $_SERVER['HTTP_REFERER']);
}

function exec_action($label, $state, $action_text, $ids) {
	foreach($ids as $uid) {
		Actions::add_action($label, (object) [
			"affected_user"=>$uid,
		]);
	}

	$injectionString = implode(",", array_map(function($input) {return "?";}, $ids));
	$result = DB::exec("UPDATE users SET state = $state WHERE id IN ($injectionString)", $ids);

	if(!$result) { CMS::Instance()->queue_message('Failed to complete action','danger', $_SERVER['HTTP_REFERER']); }

	$users = DB::fetchall("SELECT * FROM users WHERE id in ($injectionString)", $ids);
	$usersMsgString = implode(", ", array_map(function($input) { return "<a target='_blank' href='" . Config::uripath() . "/admin/users/edit/$input->id'>$input->username</a>"; }, $users));

	CMS::Instance()->queue_message("User(s) " . ($label!="userdelete" ? $usersMsgString : "") . " $action_text",'success', $_SERVER['HTTP_REFERER']);
}

$actionDetails = $actions[$action];
exec_action($actionDetails[0], $actionDetails[1], $actionDetails[2], $id);