<?php
defined('CMSPATH') or die; // prevent unauthorized access
ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned

// TODO: endure logged in user is allowed to actually perform these tasks!

$action = Input::getvar('action','STRING');

if ($action=='insert') {
	$source_id = Input::getvar('sourceid','INT');
	$dest_id = Input::getvar('destid','INT');
	$insert_position = Input::getvar('insert_position','STRING');
	
	// source ordering
	$query = "select ordering from content where id=?";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$stmt->execute(array($source_id));
	$source_ordering = $stmt->fetch()->ordering;
	// dest ordering
	$query = "select ordering from content where id=?";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$stmt->execute(array($dest_id));
	$dest_ordering = $stmt->fetch()->ordering;

	// make space in ordering first
	if ($source_ordering < $dest_ordering && $insert_position=='before') {
		$space_query = "update content set ordering = ordering -1 where ordering>0 and ordering > ? and ordering < ?";
		$move_query = "update content set ordering = " . ($dest_ordering-1) . " where id={$source_id}";
	}
	elseif ($source_ordering < $dest_ordering && $insert_position=='after') {
		$space_query = "update content set ordering = ordering -1 where ordering>0 and ordering > ? and ordering <= ?";
		$move_query = "update content set ordering = {$dest_ordering} where id={$source_id}";
	}
	elseif ($source_ordering > $dest_ordering && $insert_position=='before') {
		$space_query = "update content set ordering = ordering +1 where ordering <= ? and ordering >= ?";
		$move_query = "update content set ordering = " . $dest_ordering . " where id={$source_id}";
	}
	elseif ($source_ordering > $dest_ordering && $insert_position=='after') {
		$space_query = "update content set ordering = ordering +1 where ordering <= ? and ordering > ?";
		$move_query = "update content set ordering = " . ($dest_ordering + 1) . " where id={$source_id}";
	}
	// make space
	$stmt = CMS::Instance()->pdo->prepare($space_query);
	$stmt->execute(array($source_ordering, $dest_ordering));
	// move
	$stmt = CMS::Instance()->pdo->prepare($move_query);
	$stmt->execute(array());
	echo '{"success":1,"message":"Ordering complete"}';
	exit(0);
}