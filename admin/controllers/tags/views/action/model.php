<?php
defined('CMSPATH') or die; // prevent unauthorized access

$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

$id = Input::getvar('id','ARRAYOFINT');
if (!$id) {
	CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
}

if ($action=='toggle') {
	Actions::add_action("tagupdate", (object) [
		"affected_tag"=>$id[0],
	]);
	
	$result = DB::exec("UPDATE tags SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", array($id[0])); // id always array even with single id being passed
	if ($result) {
		CMS::Instance()->queue_message('Toggled state of tag','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of tag','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='publish') {
	foreach($id as $item) {
		Actions::add_action("tagupdate", (object) [
			"affected_tag"=>$item,
		]);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE tags SET state = 1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Published tags','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish tags','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='unpublish') {
	foreach($id as $item) {
		Actions::add_action("tagupdate", (object) [
			"affected_tag"=>$item,
		]);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE tags SET state = 0 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished tags','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish tags','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='delete') {
	foreach($id as $item) {
		Actions::add_action("tagdelete", (object) [
			"affected_tag"=>$item,
		]);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE tags SET state = -1 WHERE id IN ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted tags','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete tags','danger', $_SERVER['HTTP_REFERER']);
	}
}

