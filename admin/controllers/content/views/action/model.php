<?php
defined('CMSPATH') or die; // prevent unauthorized access

$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

$content_type = Input::getvar('content_type','INT',null);
if (!$content_type) {
	CMS::Instance()->queue_message('Unknown content type','danger', $_SERVER['HTTP_REFERER']);
}

$location = Content::get_content_location($content_type);
$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
$table_name = "controller_" . $custom_fields->id ;
if ($table_name=="controller_") {
	CMS::Instance()->queue_message('Unable to determine content table name','danger', $_SERVER['HTTP_REFERER']);
}



if ($action=='toggle') {
	$id = Input::getvar('id','ARRAYOFINT');
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	$result = DB::exec("UPDATE $table_name SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
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
	$result = DB::exec("UPDATE $table_name SET state = ? where id=?", [$togglestate[1], $togglestate[0]]); //first is id, second is state
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
	$result = DB::exec("UPDATE $table_name SET state = 1 where id in ({$idlist})"); 
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
	$result = DB::exec("UPDATE $table_name SET state = 0 where id in ({$idlist})"); 
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
	$result = DB::exec("UPDATE $table_name SET state = -1 where id in ({$idlist})"); 
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
	
	foreach ($ids as $id) {
		$orig = new Content();
		$orig->load($id, Input::getvar('content_type','INT'));
		$orig->duplicate();
	}
}



else {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

