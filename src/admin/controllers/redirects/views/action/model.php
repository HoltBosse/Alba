<?php

Use HoltBosse\Alba\Core\{CMS, Configuration};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

$table_name = "redirects";
$action = CMS::Instance()->uri_segments[2];

if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

if ($action=='toggle') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$result = DB::exec("update `$table_name` SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if ($result) {
		$redirect = DB::fetch('SELECT * FROM redirects WHERE id=?', [$id[0]]);
		$msg = "Redirect <a href='" . $_ENV["uripath"] . "/admin/redirects/edit/{$id[0]}'>item</a> state toggled";
		CMS::Instance()->queue_message($msg,'success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of redirect','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='togglestate') {
	$togglestate = Input::getvar('togglestate',v::arrayType()->each(v::intVal()));
	if (!$togglestate) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$result = DB::exec("update `$table_name` SET state = ? where id=?", [$togglestate[1], $togglestate[0]]); //first is id, second is state
	if ($result) {
		CMS::Instance()->queue_message('Updated state of redirect','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to update state of redirect','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='publish') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("update `$table_name` SET state = 1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Published redirect','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish redirect','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='unpublish') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("update `$table_name` SET state = 0 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished redirect','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish redirect','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='delete') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("update `$table_name` SET state = -1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted redirect','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete redirect','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='duplicate') {
	CMS::Instance()->queue_message('Cannot duplicate redirects','danger', $_SERVER['HTTP_REFERER']);
}

else {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

