<?php
defined('CMSPATH') or die; // prevent unauthorized access
ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned
ob_end_clean();

function rj($input) {
    echo json_encode($input);
    die;
}

$action = Input::getvar('action','STRING');

if ($action=="changeorder") {
	// id, new_order, prev_order, content_type
	$id = Input::getvar('id','NUMBER');
	$new_order = Input::getvar('new_order','INT');
	$prev_order = Input::getvar('prev_order','INT');
	$content_type = Input::getvar('content_type','INT');
	if (!$id || !$new_order || !$prev_order || !$content_type) {
		rj([
			"success"=>0,
			"message"=>"No ordering performed - one or more missing parameters"
		]);
		exit(0);
	}
	$content_table = Content::get_table_name_for_content_type($content_type);
	$stmt = DB::exec("UPDATE `$content_table` SET ordering = ? WHERE id = ?",[$new_order, $id]);
	if ($new_order > $prev_order) {
		// Item was moved down, so decrement the ordering indices of the items above it
		DB::exec("UPDATE `$content_table` SET ordering = ordering - 1 WHERE ordering > ? AND ordering <= ?", [$prev_order, $new_order]);
	} elseif ($new_order < $prev_order) {
		// Item was moved up, so increment the ordering indices of the items below it
		DB::exec("UPDATE `$content_table` SET ordering = ordering + 1 WHERE ordering >= ? AND ordering < ?",[$new_order, $prev_order]);
	}
	// Update moved items ordering
	$stmt = DB::exec("UPDATE `$content_table` SET ordering = ? WHERE id = ?",[$new_order, $id]);
	rj([
		"success"=>1,
		"message"=>"Ordering complete",
		"data"=>[
			"id"=>$id,
			"new_order"=>$new_order,
			"prev_order"=>$prev_order,
			"content_type"=>$content_type,
			
		]
	]);
	exit(0);
}
else {
	rj([
		"success"=>0,
		"message"=>"Unknown operation"
	]);
	exit(0);
}