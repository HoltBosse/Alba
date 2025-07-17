<?php

Use HoltBosse\Alba\Core\{CMS, User, Actions};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;

$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

$id = Input::getvar('id','ARRAYOFINT');
if (!$id && !Input::getvar('togglestate','ARRAYOFINT')) {
	CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
}

if ($action=='toggle') {
	foreach($id as $uid) {
		Actions::add_action("userupdate", (object) [
			"affected_user"=>$uid,
		]);
	}

	$result = DB::exec("UPDATE users SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if ($result) {
		$user = DB::fetch('SELECT * FROM users WHERE id=?', [$id[0]]);
		$msg = "User <a href='" . $_ENV["uripath"] . "/admin/users/edit/{$id[0]}'>" . Input::stringHtmlSafe($user->username) . "</a> state toggled";
		CMS::Instance()->queue_message($msg,'success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of user','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='togglestate') {
	$togglestate = Input::getvar('togglestate','ARRAYOFINT');
	if (!$togglestate) {
		CMS::Instance()->queue_message('Cannot perform action on unknown users','danger', $_SERVER['HTTP_REFERER']);
	}

	Actions::add_action("userupdate", (object) [
		"affected_user"=>$togglestate[0],
	]);

	$result = DB::exec("update users SET state = ? where id=?", [$togglestate[1], $togglestate[0]]); //first is id, second is state
	if ($result) {
		$user = DB::fetch('SELECT * FROM users WHERE id=?', [$togglestate[0]]);
		$msg = "User <a href='" . $_ENV["uripath"] . "/admin/users/edit/{$togglestate[0]}'>" . Input::stringHtmlSafe($user->username) . "</a> state toggled";
		CMS::Instance()->queue_message($msg,'success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of user','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='publish') {
	foreach($id as $uid) {
		Actions::add_action("userupdate", (object) [
			"affected_user"=>$uid,
		]);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE users SET state = 1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Published users','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish users','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='unpublish') {
	foreach($id as $uid) {
		Actions::add_action("userupdate", (object) [
			"affected_user"=>$uid,
		]);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE users SET state = 0 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished users','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish users','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='delete') {
	foreach($id as $uid) {
		Actions::add_action("userdelete", (object) [
			"affected_user"=>$uid,
		]);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE users SET state = -1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted users','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete users','danger', $_SERVER['HTTP_REFERER']);
	}
}

