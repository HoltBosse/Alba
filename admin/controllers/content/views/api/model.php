<?php
defined('CMSPATH') or die; // prevent unauthorized access
ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned
ob_end_clean();

// TODO: endure logged in user is allowed to actually perform these tasks!

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
else {
	echo '{"success":0,"message":"Unknown operation"}';
	exit(0);
}