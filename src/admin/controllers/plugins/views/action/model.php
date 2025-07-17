<?php

Use HoltBosse\Alba\Core\{CMS};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Form, Input};


$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

$id = Input::getvar('id','ARRAYOFINT');
if (!$id) {
	CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
}

if ($action=='toggle') {
	$result = DB::exec("UPDATE plugins SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if ($result) {
		$plugin = DB::fetch('SELECT * FROM plugins WHERE id=?', [$id[0]]);
		$msg = "Plugin <a href='" . $_ENV["uripath"] . "/admin/plugins/edit/{$id[0]}'>{$plugin->title}</a> state toggled";
		CMS::Instance()->queue_message($msg,'success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of plugin','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='publish') {
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE plugins SET state = 1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Published plugin','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish plugin','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='unpublish') {
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE plugins SET state = 0 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished plugin','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish plugin','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='delete') {
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE plugins SET state = -1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted plugin','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete plugin','danger', $_SERVER['HTTP_REFERER']);
	}
}

