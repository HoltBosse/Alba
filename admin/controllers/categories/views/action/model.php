<?php
defined('CMSPATH') or die; // prevent unauthorized access

$actions = [
	"toggle"=>["categoryupdate", "(CASE state WHEN 1 THEN 0 ELSE 1 END)", "toggled"],
	"publish"=>["categoryupdate", 1, "published"],
	"unpublish"=>["categoryupdate", 0, "unpublished"],
	"delete"=>["categorydelete", -1, "deleted"],
];

$action = CMS::Instance()->uri_segments[2];
$id = Input::getvar('id','ARRAYOFINT');
if (!$action || !$id || !$actions[$action]) {
	CMS::Instance()->queue_message('Unknown action or items','danger', $_SERVER['HTTP_REFERER']);
}

function exec_action($label, $state, $action_text, $ids) {
	foreach($ids as $category) {
		Actions::add_action($label, (object) [
			"affected_category"=>$category,
		]);
	}

	$injectionString = implode(",", array_map(function($input) { return "?"; }, $ids));
	$result = DB::exec("UPDATE categories SET state = $state where id in ($injectionString)", $ids);

	if(!$result) { CMS::Instance()->queue_message('Failed to complete action','danger', $_SERVER['HTTP_REFERER']); }

	$categories = DB::fetchall("SELECT * FROM categories WHERE id in ($injectionString)", $ids);
	$categoriesMsgString = implode(", ", array_map(function($input) { return "<a target='_blank' href='" . Config::uripath() . "/admin/categories/edit/$input->id'>$input->title</a>"; }, $categories));

	CMS::Instance()->queue_message("Category(s) " . ($state!=-1 ? $categoriesMsgString : "") . " $action_text",'success', $_SERVER['HTTP_REFERER']);
}

$actionDetails = $actions[$action];
exec_action($actionDetails[0], $actionDetails[1], $actionDetails[2], $id);
