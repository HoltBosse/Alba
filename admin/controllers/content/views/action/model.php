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
	// check for pending
	$cur = DB::fetch("select id,state,start,end from $table_name where id=?",[$id[0]]);
	// get new potential published state (either 1 for published or -2 for pending)
	$pub_state = 1;
	if ($cur->state==0 && $cur->end) {
		// need to check if state should be 1 or -2
		if (time() < strtotime($cur->end)) {
			$pub_state = -2; // can be set to pending
		}
		else {
			CMS::Instance()->queue_message('Cannot set content to pending or published, end date has passed.','danger', $_SERVER['HTTP_REFERER']);
		}
	}
	/* CMS::pprint_r ($cur); die();
	if ($cur->state==-2) {
		CMS::pprint_r ($cur->start);
		CMS::pprint_r (strtotime($cur->start));
		CMS::pprint_r (time());
		die();
	} */
	if ($cur->state==-2 && strtotime($cur->start) >= time()) {
		$pub_state = 0;
	}
	// unpub by default now - pending gets unpubbed, so does pubbed
	// pub_state based on previous check of end date and current state
	$result = DB::exec("UPDATE $table_name SET state = (CASE state WHEN 0 THEN $pub_state ELSE 0 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if (!$result) {
		CMS::Instance()->queue_message('Failed to toggle state of content','danger', $_SERVER['HTTP_REFERER']);
	}
	
	CMS::Instance()->queue_message('Toggled state of content','success', $_SERVER['HTTP_REFERER']);
	
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

