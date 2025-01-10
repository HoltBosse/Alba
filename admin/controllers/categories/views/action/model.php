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
	Actions::add_action("categoryupdate", (object) [
		"affected_category"=>$id[0],
	]);

	$result = DB::exec("UPDATE categories SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if ($result) {
		$title = DB::fetch('SELECT title FROM categories WHERE id=?', [$id[1]])->title;
		$msg = "Category <a href='" . Config::uripath() . "/admin/categories/edit/{$id[0]}'>" . Input::stringHtmlSafe($title) . "</a> state toggled";	
		CMS::Instance()->queue_message($msg,'success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of Category','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='publish') {
	foreach($id as $item) {
		Actions::add_action("categoryupdate", (object) [
			"affected_category"=>$item,
		]);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE categories SET state = 1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Published Categories','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish Categories','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='unpublish') {
	foreach($id as $item) {
		Actions::add_action("categoryupdate", (object) [
			"affected_category"=>$item,
		]);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE categories SET state = 0 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished Categories','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish Categories','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='delete') {
	foreach($id as $item) {
		Actions::add_action("categorydelete", (object) [
			"affected_category"=>$item,
		]);
	}
	$idlist = implode(',',$id);
	$result = DB::exec("UPDATE categories SET state = -1 WHERE id IN ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted Categories','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete Categories','danger', $_SERVER['HTTP_REFERER']);
	}
}

