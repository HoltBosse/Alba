<?php
defined('CMSPATH') or die; // prevent unauthorized access
ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned
ob_end_clean();

if (!CMS::Instance()->user->is_member_of('admin')) {
	echo '{"success":0,"message":"Access denied"}';
	exit(0);
}

$action = Input::getvar('action','STRING');

if ($action=='insert') {
	$source_id = Input::getvar('sourceid','INT');
	$dest_id = Input::getvar('destid','INT');
	$insert_position = Input::getvar('insert_position','STRING');
	$content_type = Input::getvar('content_type','INT');
	$content_table = Content::get_table_name_for_content_type($content_type);
	
	// source ordering
	$source_ordering = DB::fetch("select ordering from `$content_table` where id=?", [$source_id])->ordering;
	// dest ordering
	$dest_ordering = DB::fetch("select ordering from `$content_table` where id=?", [$dest_id])->ordering;

	// make space in ordering first
	if ($source_ordering < $dest_ordering && $insert_position=='before') {
		$space_query = "update `$content_table` set ordering = ordering -1 where ordering>0 and ordering > ? and ordering < ?";
		$move_query = "update `$content_table` set ordering = " . ($dest_ordering-1) . " where id={$source_id}";
	}
	elseif ($source_ordering < $dest_ordering && $insert_position=='after') {
		$space_query = "update `$content_table` set ordering = ordering -1 where ordering>0 and ordering > ? and ordering <= ?";
		$move_query = "update `$content_table` set ordering = {$dest_ordering} where id={$source_id}";
	}
	elseif ($source_ordering > $dest_ordering && $insert_position=='before') {
		$space_query = "update `$content_table` set ordering = ordering +1 where ordering <= ? and ordering >= ?";
		$move_query = "update `$content_table` set ordering = " . $dest_ordering . " where id={$source_id}";
	}
	elseif ($source_ordering > $dest_ordering && $insert_position=='after') {
		$space_query = "update `$content_table` set ordering = ordering +1 where ordering <= ? and ordering > ?";
		$move_query = "update `$content_table` set ordering = " . ($dest_ordering + 1) . " where id={$source_id}";
	}
	// make space
	DB::exec($space_query, [$source_ordering, $dest_ordering]);
	// move
	DB::exec($move_query);
	echo '{"success":1,"message":"Ordering complete"}';
	exit(0);
}
elseif ($action=="setorderall") {
	// slow but guarantees perfect ordering
	$content_type = Input::getvar('content_type','INT');
	$id_arr_string = Input::getvar('ids','RAW');
	$id_arr = explode(",",$id_arr_string);
	$content_table = Content::get_table_name_for_content_type($content_type);
	if (is_array($id_arr) && $content_table) {
		$index=0; 
		$start = microtime(true);
		foreach ($id_arr as $id) {
			DB::exec("UPDATE $content_table SET `ordering`=? WHERE id=?",[$index, $id]);
			$index++;
		}
		$end = microtime(true);
		$execution_time = $end - $start;
		$nice_execution_time = round($execution_time * 1000);
		echo '{"success":1,"message":"Ordering complete - '.sizeof($id_arr).' - Time: '.$nice_execution_time.'ms"}';
		exit(0);
	}
	else {
		echo '{"success":0,"message":"No ordering performed"}';
	}
}
elseif ($action=="changeorder") {
	// id, new_order, prev_order, content_type
	// more efficient than setorderall
	$id = Input::getvar('id','NUMBER');
	$new_order = Input::getvar('new_order','INT');
	$prev_order = Input::getvar('prev_order','INT');
	$content_type = Input::getvar('content_type','INT');
	if (!$id || !$new_order || !$prev_order || !$content_type) {
		echo '{"success":0,"message":"No ordering performed - one or more missing parameters"}';
		exit(0);
	}
	$content_table = Content::get_table_name_for_content_type($content_type);
	// First, update the ordering index of the item being moved
	$stmt = DB::exec("UPDATE $content_table SET ordering = ? WHERE id = ?",[$new_order, $id]);
	// Then, update the ordering indices of the other items
	if ($new_order > $prev_order) {
		// Item was moved down, so decrement the ordering indices of the items above it
		DB::exec("UPDATE $content_table SET ordering = ordering - 1 WHERE ordering > ? AND ordering <= ?", [$prev_order, $new_order]);
	} elseif ($new_order < $prev_order) {
		// Item was moved up, so increment the ordering indices of the items below it
		DB::exec("UPDATE $content_table SET ordering = ordering + 1 WHERE ordering >= ? AND ordering < ?",[$new_order, $prev_order]);
	}
	echo '{"success":1,"message":"Ordering complete"}';
	exit(0);
}
else {
	echo '{"success":0,"message":"Unknown operation"}';
	exit(0);
}