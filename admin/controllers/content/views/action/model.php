<?php
defined('CMSPATH') or die; // prevent unauthorized access

$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}





if ($action=='toggle') {
	$id = Input::getvar('id','ARRAYOFINT');
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$result = DB::exec("UPDATE content SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if ($result) {
		CMS::Instance()->queue_message('Toggled state of content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of content','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='togglestate') {
	$togglestate = Input::getvar('togglestate','ARRAYOFINT');
	if (!$togglestate) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$result = DB::exec("UPDATE content SET state = ? where id=?", [$togglestate[1], $togglestate[0]]); //first is id, second is state
	if ($result) {
		CMS::Instance()->queue_message('Updated state of content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to update state of content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='publish') {
	$id = Input::getvar('id','ARRAYOFINT');
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE content SET state = 1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Published content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='unpublish') {
	$id = Input::getvar('id','ARRAYOFINT');
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE content SET state = 0 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='delete') {
	$id = Input::getvar('id','ARRAYOFINT');
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE content SET state = -1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='duplicate') {
	$ids = Input::getvar('id','ARRAYOFINT');
	if (!$ids) {
		//CMS::pprint_r ($ids);exit(0);
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	CMS::pprint_r ($id);
	foreach ($ids as $id) {
		$orig = new Content();
		$orig->load($id);
		$orig->duplicate();
	}
}



else {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

