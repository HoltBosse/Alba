<?php
defined('CMSPATH') or die; // prevent unauthorized access

$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

$id = CMS::getvar('id','ARRAYOFINT');
if (!$id) {
	CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
}

if ($action=='toggle') {
	$query = "UPDATE widgets SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array($id[0])); // id always array even with single id being passed
	if ($result) {
		CMS::Instance()->queue_message('Toggled state of widget','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of widget','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='publish') {
	$idlist = implode(',',$id);
	$query = "UPDATE widgets SET state = 1 where id in ({$idlist})"; // relatively safe - ids already filtered to be INTs only
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array()); 
	if ($result) {
		CMS::Instance()->queue_message('Published widgets','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish widgets','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='unpublish') {
	$idlist = implode(',',$id);
	$query = "UPDATE widgets SET state = 0 where id in ({$idlist})"; // relatively safe - ids already filtered to be INTs only
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array()); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished widgets','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish widgets','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='delete') {
	$idlist = implode(',',$id);
	$query = "UPDATE widgets SET state = -1 where id in ({$idlist})"; // relatively safe - ids already filtered to be INTs only
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array()); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted widgets','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete widgets','danger', $_SERVER['HTTP_REFERER']);
	}
}

