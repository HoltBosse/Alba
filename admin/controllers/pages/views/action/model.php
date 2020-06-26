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
	$query = "UPDATE pages SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array($id[0])); // id always array even with single id being passed
	if ($result) {
		CMS::Instance()->queue_message('Toggled state of page','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of page','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='publish') {
	$idlist = implode(',',$id);
	$query = "UPDATE pages SET state = 1 where id in ({$idlist})"; // relatively safe - ids already filtered to be INTs only
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array()); 
	if ($result) {
		CMS::Instance()->queue_message('Published pages','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish pages','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='unpublish') {
	$idlist = implode(',',$id);
	$query = "UPDATE pages SET state = 0 where id in ({$idlist})"; // relatively safe - ids already filtered to be INTs only
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array()); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished pages','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish pages','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='delete') {
	$idlist = implode(',',$id);
	$query = "select count(parent) as c from pages where parent in ({$idlist})";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array());
	if ($result) {
		$c = $stmt->fetch()->c;
		if ($c>0) {
			CMS::Instance()->queue_message('Cannot delete page(s) with children','danger', $_SERVER['HTTP_REFERER']);
		}
		else {
			$query = "UPDATE pages SET alias = CONCAT(alias,'_DELETED'), parent=-1, state = -1 where id in ({$idlist})"; // relatively safe - ids already filtered to be INTs only
			$stmt = CMS::Instance()->pdo->prepare($query);
			$result = $stmt->execute(array()); 
			if ($result) {
				CMS::Instance()->queue_message('Deleted pages','success', $_SERVER['HTTP_REFERER']);
			}
			else {
				CMS::Instance()->queue_message('Failed to delete pages','danger', $_SERVER['HTTP_REFERER']);
			}
		}
	}
	else {
		CMS::Instance()->queue_message('Error checking for child pages','danger', $_SERVER['HTTP_REFERER']);
	}
}

